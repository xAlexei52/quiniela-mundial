<?php

namespace App\Http\Controllers;

use App\Services\ScoringService;

class ParticipantController extends Controller
{
    public function __construct(private ScoringService $scoring)
    {
    }

    public function index()
    {
        $leaderboard = $this->scoring->leaderboard();
        $unassigned  = \App\Models\Team::doesntHave('assignment')->count();

        return view('participantes', compact('leaderboard', 'unassigned'));
    }
}
