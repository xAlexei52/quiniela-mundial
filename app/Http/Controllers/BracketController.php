<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Fixture;
use App\Services\BracketService;

class BracketController extends Controller
{
    public function __construct(private BracketService $bracket)
    {
    }

    public function index()
    {
        $owners = Assignment::with('participant')->get()
            ->mapWithKeys(fn (Assignment $a) => [$a->team_id => $a->participant]);

        // Incluye 3er puesto y final junto al flujo normal.
        $stages = ['r32', 'r16', 'qf', 'sf', 'third_place', 'final'];

        $rounds = collect($stages)
            ->mapWithKeys(fn ($stage) => [
                $stage => Fixture::where('stage', $stage)
                    ->with(['homeTeam', 'awayTeam'])
                    ->orderBy('id')
                    ->get(),
            ])
            ->filter(fn ($fixtures) => $fixtures->isNotEmpty());

        return view('bracket', [
            'rounds'    => $rounds,
            'owners'    => $owners,
            'qualified' => $rounds->isEmpty() ? $this->bracket->qualifiedTeams() : collect(),
        ]);
    }
}
