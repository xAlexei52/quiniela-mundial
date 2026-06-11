<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Services\ScoringService;

class HomeController extends Controller
{
    public function __construct(private ScoringService $scoring)
    {
    }

    public function index()
    {
        $leaderboard = $this->scoring->leaderboard();

        // Premios: bote repartido al top 3.
        $pool   = (int) config('quiniela.prize.pool');
        $splits = config('quiniela.prize.splits', [0.5, 0.3, 0.2]);
        $prizes = collect($splits)->map(fn ($pct, $i) => [
            'place'   => $i + 1,
            'amount'  => (int) round($pool * $pct),
            'pct'     => (int) round($pct * 100),
            'winner'  => $leaderboard[$i]['participant'] ?? null,
            'score'   => $leaderboard[$i]['score'] ?? null,
        ]);

        // Resultados recientes y próximos partidos.
        $recent = Fixture::with(['homeTeam', 'awayTeam'])
            ->where('status', 'finished')
            ->orderByDesc('kickoff_at')->orderByDesc('id')
            ->limit(6)->get();

        $upcoming = Fixture::with(['homeTeam', 'awayTeam'])
            ->where('status', '!=', 'finished')
            ->whereNotNull('kickoff_at')
            ->orderBy('kickoff_at')
            ->limit(6)->get();

        $maxScore = max(1, (int) $leaderboard->max('score'));

        return view('home', compact('leaderboard', 'prizes', 'pool', 'recent', 'upcoming', 'maxScore'));
    }
}
