<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Fixture;
use App\Models\Group;
use App\Services\StandingsService;

class GroupController extends Controller
{
    public function __construct(private StandingsService $standings)
    {
    }

    public function index()
    {
        // Mapa team_id => participante dueño (para nombre + color).
        $owners = Assignment::with('participant')->get()
            ->mapWithKeys(fn (Assignment $a) => [$a->team_id => $a->participant]);

        $groups = Group::orderBy('name')->get()->map(function (Group $group) {
            return [
                'group'   => $group,
                'rows'    => $this->standings->forGroup($group),
                'matches' => Fixture::with(['homeTeam', 'awayTeam'])
                    ->where('group_id', $group->id)
                    ->where('stage', 'group')
                    ->orderBy('kickoff_at')->orderBy('id')
                    ->get(),
            ];
        });

        return view('grupos', compact('groups', 'owners'));
    }
}
