<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\Group;
use App\Models\Participant;
use App\Models\Team;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Calcula el puntaje de la quiniela.
 *
 * Puntos por partido (todo el torneo): victoria 3, empate 1, derrota 0.
 * Más un BONUS por la ronda más lejana que alcanza cada equipo (no acumulativo
 * entre hitos: cuenta el más alto):
 *   Dieciseisavos +5, Octavos +8, Cuartos +12, Semifinal +15,
 *   3er lugar +18, Subcampeón +20, Campeón +25.
 */
class ScoringService
{
    private const WIN = 3;
    private const DRAW = 1;

    /** Ranking de rondas de eliminación para saber "hasta dónde llegó". */
    private const STAGE_RANK = [
        'r32' => 1, 'r16' => 2, 'qf' => 3, 'sf' => 4, 'third_place' => 4, 'final' => 5,
    ];

    public function __construct(private StandingsService $standings)
    {
    }

    /**
     * Recalcula qué equipos están eliminados según los resultados actuales.
     * Idempotente.
     */
    public function recompute(): void
    {
        $eliminated = collect();

        // Perdedores de eliminación directa.
        foreach (Fixture::where('stage', '!=', 'group')->get() as $fx) {
            if ($fx->isFinished() && ($loser = $fx->loserTeamId())) {
                $eliminated->push($loser);
            }
        }

        // Fase de grupos: 4º fuera; 3º fuera salvo top-8 terceros.
        $allComplete = $this->allGroupsComplete();
        $best8Thirds = $allComplete
            ? $this->standings->thirdPlacedRanking()->take(8)->pluck('team.id')
            : collect();

        foreach (Group::all() as $group) {
            if (! $this->groupComplete($group)) {
                continue;
            }
            $rows = $this->standings->forGroup($group);

            if ($fourth = $rows->firstWhere('position', 4)) {
                $eliminated->push($fourth['team']->id);
            }
            if ($allComplete && ($third = $rows->firstWhere('position', 3))) {
                if (! $best8Thirds->contains($third['team']->id)) {
                    $eliminated->push($third['team']->id);
                }
            }
        }

        $ids = $eliminated->unique();

        Team::whereIn('id', $ids)->whereNull('eliminated_at')
            ->update(['eliminated_at' => Carbon::now()]);
        Team::whereNotIn('id', $ids->all() ?: [0])->whereNotNull('eliminated_at')
            ->update(['eliminated_at' => null]);
    }

    /**
     * Estadísticas por equipo: puntos de partidos, bonus de ronda, total, estado.
     *
     * @return Collection<int, array> indexada por team_id
     */
    public function teamStats(): Collection
    {
        $teams = Team::all()->keyBy('id');
        $fixtures = Fixture::whereNotNull('home_team_id')->whereNotNull('away_team_id')->get();

        // Puntos por partido (todas las rondas).
        $matchPoints = [];
        foreach ($fixtures as $fx) {
            if (! $fx->isFinished()) {
                continue;
            }
            [$hp, $ap] = $this->matchPoints($fx);
            $matchPoints[$fx->home_team_id] = ($matchPoints[$fx->home_team_id] ?? 0) + $hp;
            $matchPoints[$fx->away_team_id] = ($matchPoints[$fx->away_team_id] ?? 0) + $ap;
        }

        // Ronda más lejana alcanzada + colocaciones (campeón, subcampeón, 3º).
        $maxRank = [];
        $championId = $runnerUpId = $thirdId = null;
        foreach ($fixtures as $fx) {
            $rank = self::STAGE_RANK[$fx->stage] ?? 0;
            foreach ([$fx->home_team_id, $fx->away_team_id] as $tid) {
                if ($tid && $rank > ($maxRank[$tid] ?? 0)) {
                    $maxRank[$tid] = $rank;
                }
            }
            if ($fx->stage === 'final' && $fx->isFinished()) {
                $championId = $fx->winnerTeamId();
                $runnerUpId = $fx->loserTeamId();
            }
            if ($fx->stage === 'third_place' && $fx->isFinished()) {
                $thirdId = $fx->winnerTeamId();
            }
        }

        return $teams->map(function (Team $team) use ($matchPoints, $maxRank, $championId, $runnerUpId, $thirdId) {
            $mp = $matchPoints[$team->id] ?? 0;
            $bonus = $this->reachBonus($team->id, $maxRank[$team->id] ?? 0, $championId, $runnerUpId, $thirdId);

            return [
                'team'         => $team,
                'alive'        => ! $team->isEliminated(),
                'match_points' => $mp,
                'reach_bonus'  => $bonus,
                'score'        => $mp + $bonus,
                'status_label' => $this->statusLabel($team, $championId, $runnerUpId, $thirdId, $maxRank[$team->id] ?? 0),
            ];
        });
    }

    /**
     * Tabla de la quiniela por participante (marcador), ordenada por puntaje.
     */
    public function leaderboard(): Collection
    {
        $stats = $this->teamStats();

        return Participant::with('teams')->orderBy('position')->get()->map(function (Participant $p) use ($stats) {
            $teamStats = $p->teams->map(fn (Team $t) => $stats[$t->id] ?? null)->filter()->values();

            return [
                'participant' => $p,
                'teams'       => $teamStats->sortByDesc('score')->values(),
                'alive'       => $teamStats->where('alive', true)->count(),
                'eliminated'  => $teamStats->where('alive', false)->count(),
                'total_teams' => $teamStats->count(),
                'score'       => $teamStats->sum('score'),
            ];
        })->sortByDesc('score')->values();
    }

    /** Puntos [home, away] de un partido finalizado. */
    private function matchPoints(Fixture $fx): array
    {
        if ($fx->home_score > $fx->away_score) {
            return [self::WIN, 0];
        }
        if ($fx->away_score > $fx->home_score) {
            return [0, self::WIN];
        }
        // Empate en marcador: en eliminatorias decide penales (victoria/derrota).
        $winner = $fx->winnerTeamId();
        if ($winner === null) {
            return [self::DRAW, self::DRAW];
        }

        return $winner === $fx->home_team_id ? [self::WIN, 0] : [0, self::WIN];
    }

    private function reachBonus(int $teamId, int $rank, ?int $champ, ?int $runner, ?int $third): int
    {
        if ($teamId === $champ) {
            return 25;
        }
        if ($rank >= 5) { // jugó la final (finalista) sin ser campeón → subcampeón
            return 20;
        }
        if ($teamId === $third) {
            return 18;
        }
        return match (true) {
            $rank >= 4 => 15, // semifinalista (sf o partido por el 3er puesto)
            $rank === 3 => 12, // cuartos
            $rank === 2 => 8,  // octavos
            $rank === 1 => 5,  // dieciseisavos
            default     => 0,
        };
    }

    private function statusLabel(Team $team, ?int $champ, ?int $runner, ?int $third, int $rank): string
    {
        if ($team->id === $champ) {
            return '🏆 Campeón';
        }
        if ($team->id === $runner) {
            return '🥈 Subcampeón';
        }
        if ($team->id === $third) {
            return '🥉 Tercer lugar';
        }
        if ($team->isEliminated()) {
            $stage = array_search($rank, self::STAGE_RANK, true) ?: 'group';
            return 'Eliminado en '.($rank === 0 ? 'Fase de grupos' : (Fixture::STAGES[$stage] ?? 'Fase de grupos'));
        }

        return $rank === 0 ? 'En fase de grupos' : 'Vivo · '.($this->roundLabelForRank($rank));
    }

    private function roundLabelForRank(int $rank): string
    {
        $stage = array_search($rank, self::STAGE_RANK, true) ?: 'r32';

        return Fixture::STAGES[$stage] ?? 'Eliminatorias';
    }

    private function groupComplete(Group $group): bool
    {
        return ! Fixture::where('group_id', $group->id)->where('stage', 'group')
                ->where('status', '!=', 'finished')->exists()
            && Fixture::where('group_id', $group->id)->where('stage', 'group')->exists();
    }

    private function allGroupsComplete(): bool
    {
        $groups = Group::all();

        return $groups->isNotEmpty() && $groups->every(fn (Group $g) => $this->groupComplete($g));
    }
}
