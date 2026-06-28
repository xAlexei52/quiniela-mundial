<?php

namespace App\Services\Results;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

/**
 * Driver para football-data.org (https://www.football-data.org/documentation/quickstart).
 *
 * Endpoint usado: GET /v4/competitions/{id}/matches
 * Header de autenticación: X-Auth-Token: <api_key>
 */
class FootballDataProvider implements ResultsProvider
{
    public function __construct(
        private string $apiKey,
        private string $baseUrl,
        private string $competitionId,
    ) {
    }

    public function matches(): Collection
    {
        $response = Http::withHeaders(['X-Auth-Token' => $this->apiKey])
            ->acceptJson()
            ->timeout(20)
            ->get("{$this->baseUrl}/competitions/{$this->competitionId}/matches");

        $response->throw();

        return collect($response->json('matches', []))->map(function (array $m) {
            return [
                'external_id' => (string) ($m['id'] ?? ''),
                'home_ext'    => isset($m['homeTeam']['id']) ? (string) $m['homeTeam']['id'] : null,
                'away_ext'    => isset($m['awayTeam']['id']) ? (string) $m['awayTeam']['id'] : null,
                'home_name'   => $m['homeTeam']['name'] ?? null,
                'away_name'   => $m['awayTeam']['name'] ?? null,
                'home_score'  => $m['score']['fullTime']['home'] ?? null,
                'away_score'  => $m['score']['fullTime']['away'] ?? null,
                'home_pens'   => $m['score']['penalties']['home'] ?? null,
                'away_pens'   => $m['score']['penalties']['away'] ?? null,
                'status'      => $this->mapStatus($m['status'] ?? ''),
                'kickoff_at'  => $m['utcDate'] ?? null,
                'stage'       => $this->mapStage($m['stage'] ?? ''),
                'group'       => $this->mapGroup($m['group'] ?? null),
            ];
        });
    }

    private function mapStatus(string $status): string
    {
        return match ($status) {
            'FINISHED', 'AWARDED'              => 'finished',
            'IN_PLAY', 'PAUSED', 'SUSPENDED'   => 'live',
            default                            => 'scheduled',
        };
    }

    private function mapStage(string $stage): ?string
    {
        return match ($stage) {
            'GROUP_STAGE'    => 'group',
            'LAST_32'        => 'r32',
            'LAST_16'        => 'r16',
            'QUARTER_FINALS' => 'qf',
            'SEMI_FINALS'    => 'sf',
            'THIRD_PLACE'    => 'third_place',
            'FINAL'          => 'final',
            default          => null,
        };
    }

    private function mapGroup(?string $group): ?string
    {
        if (! $group) {
            return null;
        }

        // "GROUP_A" -> "A"
        return str_replace('GROUP_', '', $group) ?: null;
    }
}
