<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\Group;
use App\Models\Team;
use Illuminate\Support\Collection;

class BracketService
{
    /** Secuencia de rondas de eliminación directa. */
    public const KNOCKOUT_FLOW = ['r32', 'r16', 'qf', 'sf', 'final'];

    public function __construct(private StandingsService $standings)
    {
    }

    /**
     * Genera todas las rondas posibles: crea los dieciseisavos cuando la fase
     * de grupos está completa, y va creando la siguiente ronda cuando la
     * anterior termina. Es un cuadro SIMPLIFICADO (sembrado por puntaje),
     * editable por el admin. Idempotente.
     */
    public function generate(): void
    {
        $this->generateRoundOf32();

        foreach (self::KNOCKOUT_FLOW as $i => $stage) {
            $next = self::KNOCKOUT_FLOW[$i + 1] ?? null;
            if ($next) {
                $this->advance($stage, $next);
            }
        }
    }

    private function generateRoundOf32(): void
    {
        if (Fixture::where('stage', 'r32')->exists()) {
            return; // ya generada
        }
        if (! $this->allGroupsComplete()) {
            return; // aún no termina la fase de grupos
        }

        $qualified = $this->qualifiedTeams(); // 32 equipos sembrados
        $this->pairAndCreate($qualified, 'r32', 'Dieciseisavos');
    }

    /**
     * Crea la ronda siguiente cuando la actual está completa.
     */
    private function advance(string $stage, string $nextStage): void
    {
        if (Fixture::where('stage', $nextStage)->exists()) {
            return;
        }

        $fixtures = Fixture::where('stage', $stage)->orderBy('id')->get();
        if ($fixtures->isEmpty() || $fixtures->contains(fn (Fixture $f) => ! $f->isFinished())) {
            return;
        }

        $winners = $fixtures->map(fn (Fixture $f) => Team::find($f->winnerTeamId()))->filter()->values();
        if ($winners->count() < 2) {
            return;
        }

        $label = Fixture::STAGES[$nextStage] ?? $nextStage;
        $this->pairAndCreate($winners, $nextStage, $label);
    }

    /**
     * Equipos clasificados ordenados como semilla: 12 primeros (A-L),
     * 12 segundos (A-L), 8 mejores terceros (por ranking).
     *
     * @return Collection<int, Team>
     */
    public function qualifiedTeams(): Collection
    {
        $firsts = collect();
        $seconds = collect();

        foreach (Group::orderBy('name')->get() as $group) {
            $rows = $this->standings->forGroup($group);
            if ($t = $rows->firstWhere('position', 1)) {
                $firsts->push($t['team']);
            }
            if ($t = $rows->firstWhere('position', 2)) {
                $seconds->push($t['team']);
            }
        }

        $thirds = $this->standings->thirdPlacedRanking()
            ->take(8)
            ->map(fn ($row) => $row['team']);

        return $firsts->concat($seconds)->concat($thirds)->values();
    }

    /**
     * Empareja una lista sembrada (1vN, 2vN-1, ...) y crea los fixtures.
     */
    private function pairAndCreate(Collection $teams, string $stage, string $labelBase): void
    {
        $n = $teams->count();
        $matches = intdiv($n, 2);

        for ($i = 0; $i < $matches; $i++) {
            $home = $teams[$i];
            $away = $teams[$n - 1 - $i];

            Fixture::create([
                'stage'        => $stage,
                'home_team_id' => $home->id,
                'away_team_id' => $away->id,
                'status'       => 'scheduled',
                'label'        => $labelBase.' '.($i + 1),
            ]);
        }
    }

    private function allGroupsComplete(): bool
    {
        $groups = Group::all();
        if ($groups->isEmpty()) {
            return false;
        }

        foreach ($groups as $group) {
            $pending = Fixture::where('group_id', $group->id)
                ->where('stage', 'group')
                ->where('status', '!=', 'finished')
                ->exists();
            $hasMatches = Fixture::where('group_id', $group->id)->where('stage', 'group')->exists();

            if ($pending || ! $hasMatches) {
                return false;
            }
        }

        return true;
    }
}
