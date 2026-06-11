<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\Group;
use App\Models\Team;
use Illuminate\Support\Collection;

class StandingsService
{
    /**
     * Tabla de posiciones calculada de un grupo, ordenada.
     * Cada fila: team, played, won, drawn, lost, gf, ga, gd, points, position.
     *
     * Desempates (simplificado): puntos > diferencia de goles > goles a favor.
     */
    public function forGroup(Group $group): Collection
    {
        $teams = $group->teams()->get();
        $rows = $teams->mapWithKeys(fn (Team $t) => [$t->id => $this->emptyRow($t)]);

        $fixtures = Fixture::where('group_id', $group->id)
            ->where('stage', 'group')
            ->get();

        foreach ($fixtures as $fx) {
            if (! $fx->isFinished()) {
                continue;
            }

            $home = $rows[$fx->home_team_id] ?? null;
            $away = $rows[$fx->away_team_id] ?? null;
            if (! $home || ! $away) {
                continue;
            }

            $home['played']++;
            $away['played']++;
            $home['gf'] += $fx->home_score;
            $home['ga'] += $fx->away_score;
            $away['gf'] += $fx->away_score;
            $away['ga'] += $fx->home_score;

            if ($fx->home_score > $fx->away_score) {
                $home['won']++;
                $home['points'] += 3;
                $away['lost']++;
            } elseif ($fx->home_score < $fx->away_score) {
                $away['won']++;
                $away['points'] += 3;
                $home['lost']++;
            } else {
                $home['drawn']++;
                $away['drawn']++;
                $home['points']++;
                $away['points']++;
            }

            $rows[$fx->home_team_id] = $home;
            $rows[$fx->away_team_id] = $away;
        }

        return $rows->values()
            ->map(function (array $row) {
                $row['gd'] = $row['gf'] - $row['ga'];
                return $row;
            })
            ->sortBy([
                ['points', 'desc'],
                ['gd', 'desc'],
                ['gf', 'desc'],
            ])
            ->values()
            ->map(function (array $row, int $i) {
                $row['position'] = $i + 1;
                return $row;
            });
    }

    /**
     * Todas las tablas: [Group => Collection<row>].
     */
    public function all(): Collection
    {
        return Group::orderBy('name')->get()->mapWithKeys(
            fn (Group $g) => [$g->name => ['group' => $g, 'rows' => $this->forGroup($g)]]
        );
    }

    /**
     * Ranking de los terceros de cada grupo (para los 8 mejores que avanzan).
     */
    public function thirdPlacedRanking(): Collection
    {
        $thirds = $this->all()
            ->map(fn ($g) => $g['rows']->firstWhere('position', 3))
            ->filter()
            ->values();

        return $thirds->sortBy([
            ['points', 'desc'],
            ['gd', 'desc'],
            ['gf', 'desc'],
        ])->values();
    }

    private function emptyRow(Team $team): array
    {
        return [
            'team'     => $team,
            'played'   => 0,
            'won'      => 0,
            'drawn'    => 0,
            'lost'     => 0,
            'gf'       => 0,
            'ga'       => 0,
            'gd'       => 0,
            'points'   => 0,
            'position' => 0,
        ];
    }
}
