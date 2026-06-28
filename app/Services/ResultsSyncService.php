<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\Group;
use App\Models\Team;
use App\Services\Results\FootballDataProvider;
use App\Services\Results\ResultsProvider;
use Illuminate\Support\Carbon;

class ResultsSyncService
{
    public function __construct(
        private ScoringService $scoring,
        private BracketService $bracket,
    ) {
    }

    public function isConfigured(): bool
    {
        return ! empty(config('quiniela.results.api_key'));
    }

    /**
     * Sincroniza marcadores desde la API y recalcula la quiniela.
     *
     * @return array{updated:int, skipped:int} resumen
     * @throws \RuntimeException si no hay API key configurada.
     */
    public function sync(): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException(
                'No hay API de resultados configurada. Define RESULTS_API_KEY en el .env '.
                'o carga los marcadores manualmente desde el panel de admin.'
            );
        }

        $provider = $this->makeProvider();
        $matches = $provider->matches();

        $updated = 0;
        $skipped = 0;

        foreach ($matches as $m) {
            $fixture = $this->matchFixture($m);

            if (! $fixture) {
                $skipped++;
                continue;
            }

            $changed = false;

            // Rellena los equipos del cruce cuando la API ya los definió. Sirve
            // para que las casillas "por definir" de las eliminatorias se vayan
            // llenando solas conforme avanza el torneo, sin esperar al marcador.
            if ($this->fillTeams($fixture, $m)) {
                $changed = true;
            }

            // La API gratis a veces marca "terminado" con marcador nulo. Para no
            // pisar lo cargado a mano, solo actualizamos el marcador cuando es real.
            $hasScore = $m['home_score'] !== null && $m['away_score'] !== null;
            if ($hasScore) {
                $fixture->fill([
                    'home_score'  => $m['home_score'],
                    'away_score'  => $m['away_score'],
                    'status'      => $m['status'],
                    'kickoff_at'  => $m['kickoff_at'] ? Carbon::parse($m['kickoff_at']) : $fixture->kickoff_at,
                    'external_id' => $fixture->external_id ?: $m['external_id'],
                ]);
                $changed = true;
            }

            if ($changed) {
                $fixture->save();
                $updated++;
            } else {
                $skipped++;
            }
        }

        // Recalcular cuadro y eliminaciones tras la sincronización.
        $this->bracket->generate();
        $this->scoring->recompute();

        return ['updated' => $updated, 'skipped' => $skipped];
    }

    /**
     * Importa el cuadro oficial completo (equipos, grupos y partidos) desde la API.
     * Crea/actualiza por external_id. Devuelve conteos.
     *
     * @return array{teams:int, fixtures:int}
     * @throws \RuntimeException si no hay API key configurada.
     */
    public function importFixture(): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('No hay API configurada para importar el cuadro.');
        }

        $matches = $this->makeProvider()->matches();
        $teamCount = 0;
        $fixtureCount = 0;

        foreach ($matches as $m) {
            $group = null;
            if ($m['stage'] === 'group' && $m['group']) {
                $group = Group::firstOrCreate(['name' => $m['group']]);
            }

            $home = $this->upsertTeam($m['home_ext'], $m['home_name'], $group?->id, $teamCount);
            $away = $this->upsertTeam($m['away_ext'], $m['away_name'], $group?->id, $teamCount);

            if (empty($m['external_id'])) {
                continue;
            }

            Fixture::updateOrCreate(
                ['external_id' => $m['external_id']],
                [
                    'stage'        => $m['stage'] ?? 'group',
                    'group_id'     => $group?->id,
                    'home_team_id' => $home?->id,
                    'away_team_id' => $away?->id,
                    'home_score'   => $m['home_score'],
                    'away_score'   => $m['away_score'],
                    'status'       => $m['status'],
                    'kickoff_at'   => $m['kickoff_at'] ? Carbon::parse($m['kickoff_at']) : null,
                ]
            );
            $fixtureCount++;
        }

        $this->bracket->generate();
        $this->scoring->recompute();

        return ['teams' => $teamCount, 'fixtures' => $fixtureCount];
    }

    /**
     * Completa los equipos de un fixture con los que reporta la API, pero solo
     * los lados que aún están "por definir" (null). Así nunca pisa equipos ya
     * asignados (cargados a mano o importados) y deja que el cuadro de
     * eliminatorias se vaya rellenando conforme la API decide los cruces.
     */
    private function fillTeams(Fixture $fixture, array $m): bool
    {
        $dummy = 0;
        $changed = false;

        if ($fixture->home_team_id === null && $m['home_ext']
            && $home = $this->upsertTeam($m['home_ext'], $m['home_name'], null, $dummy)) {
            $fixture->home_team_id = $home->id;
            $changed = true;
        }

        if ($fixture->away_team_id === null && $m['away_ext']
            && $away = $this->upsertTeam($m['away_ext'], $m['away_name'], null, $dummy)) {
            $fixture->away_team_id = $away->id;
            $changed = true;
        }

        return $changed;
    }

    private function upsertTeam(?string $externalId, ?string $name, ?int $groupId, int &$counter): ?Team
    {
        if (! $externalId || ! $name) {
            return null;
        }

        [$esName, $flag] = \App\Support\Countries::resolve($name);

        $team = Team::firstOrNew(['external_id' => $externalId]);
        $isNew = ! $team->exists;
        $team->name = $esName;
        if ($flag) {
            $team->flag = $flag;
        }
        if ($groupId) {
            $team->group_id = $groupId;
        }
        $team->save();

        if ($isNew) {
            $counter++;
        }

        return $team;
    }

    /**
     * Localiza el fixture local correspondiente al partido de la API:
     * primero por external_id, luego por los external_id de ambos equipos.
     */
    private function matchFixture(array $m): ?Fixture
    {
        if (! empty($m['external_id'])) {
            $byId = Fixture::where('external_id', $m['external_id'])->first();
            if ($byId) {
                return $byId;
            }
        }

        if ($m['home_ext'] && $m['away_ext']) {
            $home = Team::where('external_id', $m['home_ext'])->first();
            $away = Team::where('external_id', $m['away_ext'])->first();

            if ($home && $away) {
                return Fixture::where(function ($q) use ($home, $away) {
                    $q->where('home_team_id', $home->id)->where('away_team_id', $away->id);
                })->orWhere(function ($q) use ($home, $away) {
                    $q->where('home_team_id', $away->id)->where('away_team_id', $home->id);
                })->first();
            }
        }

        return null;
    }

    private function makeProvider(): ResultsProvider
    {
        $cfg = config('quiniela.results');

        return match ($cfg['driver']) {
            'football-data' => new FootballDataProvider(
                $cfg['api_key'],
                $cfg['base_url'],
                $cfg['competition_id'],
            ),
            default => throw new \RuntimeException("Driver de resultados no soportado: {$cfg['driver']}"),
        };
    }
}
