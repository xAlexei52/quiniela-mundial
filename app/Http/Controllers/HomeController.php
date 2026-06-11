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

        // Premios: montos fijos al top 3.
        $pool    = (int) config('quiniela.prize.pool');
        $amounts = config('quiniela.prize.amounts', [4000, 1500, 500]);
        $prizes = collect($amounts)->map(fn ($amt, $i) => [
            'place'   => $i + 1,
            'amount'  => (int) $amt,
            'pct'     => $pool > 0 ? (int) round($amt / $pool * 100) : 0,
            'winner'  => $leaderboard[$i]['participant'] ?? null,
            'score'   => $leaderboard[$i]['score'] ?? null,
        ]);

        // Partidos en vivo, resultados recientes y próximos.
        $live = Fixture::with(['homeTeam', 'awayTeam'])
            ->where('status', 'live')
            ->orderBy('kickoff_at')->get();

        $recent = Fixture::with(['homeTeam', 'awayTeam'])
            ->where('status', 'finished')
            ->whereNotNull('home_score')->whereNotNull('away_score')
            ->orderByDesc('kickoff_at')->orderByDesc('id')
            ->limit(6)->get();

        $upcoming = Fixture::with(['homeTeam', 'awayTeam'])
            ->where('status', 'scheduled')
            ->whereNotNull('kickoff_at')
            ->orderBy('kickoff_at')
            ->limit(6)->get();

        $maxScore = max(1, (int) $leaderboard->max('score'));

        return view('home', compact('leaderboard', 'prizes', 'pool', 'live', 'recent', 'upcoming', 'maxScore'));
    }
}
