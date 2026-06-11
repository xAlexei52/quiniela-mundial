<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Fixture;
use App\Models\Participant;
use App\Models\Team;
use App\Services\BracketService;
use App\Services\ResultsSyncService;
use App\Services\ScoringService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public const TEAMS_PER_PARTICIPANT = 4;

    public function __construct(
        private ResultsSyncService $sync,
        private BracketService $bracket,
        private ScoringService $scoring,
    ) {
    }

    /** Formulario de PIN (o redirige al panel si ya está autenticado). */
    public function login(Request $request)
    {
        if ($request->session()->get('admin_ok')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate(['pin' => 'required|string']);

        if (! hash_equals((string) config('quiniela.admin_pin'), (string) $request->input('pin'))) {
            throw ValidationException::withMessages(['pin' => 'PIN incorrecto.']);
        }

        $request->session()->put('admin_ok', true);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin_ok');

        return redirect()->route('home');
    }

    public function dashboard()
    {
        $participants = Participant::with('teams.group')->orderBy('position')->get();
        $available = Team::with('group')->doesntHave('assignment')
            ->orderBy('name')->get();

        return view('admin.dashboard', [
            'participants'  => $participants,
            'available'     => $available,
            'perParticipant'=> self::TEAMS_PER_PARTICIPANT,
            'apiConfigured' => $this->sync->isConfigured(),
            'teamCount'     => Team::count(),
            'assignedCount' => Assignment::count(),
        ]);
    }

    public function storeParticipant(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:60']);

        Participant::create([
            'name'     => $data['name'],
            'position' => (int) Participant::max('position') + 1,
        ]);

        return back()->with('status', 'Participante agregado.');
    }

    public function updateParticipant(Request $request, Participant $participant)
    {
        $data = $request->validate(['name' => 'required|string|max:60']);
        $participant->update(['name' => $data['name']]);

        return back()->with('status', 'Nombre actualizado.');
    }

    public function destroyParticipant(Participant $participant)
    {
        $participant->delete(); // assignments en cascada

        return back()->with('status', 'Participante eliminado.');
    }

    public function assignTeam(Request $request, Participant $participant)
    {
        $data = $request->validate(['team_id' => 'required|exists:teams,id']);

        if ($participant->teams()->count() >= self::TEAMS_PER_PARTICIPANT) {
            return back()->with('error', "Cada participante puede tener máximo ".self::TEAMS_PER_PARTICIPANT." equipos.");
        }
        if (Assignment::where('team_id', $data['team_id'])->exists()) {
            return back()->with('error', 'Ese equipo ya está asignado a alguien.');
        }

        Assignment::create([
            'participant_id' => $participant->id,
            'team_id'        => $data['team_id'],
        ]);

        return back()->with('status', 'Equipo asignado.');
    }

    public function unassignTeam(Team $team)
    {
        Assignment::where('team_id', $team->id)->delete();

        return back()->with('status', 'Equipo liberado.');
    }

    /** Reparte al azar los equipos sin dueño entre los participantes con cupo. */
    public function randomFill()
    {
        $teams = Team::doesntHave('assignment')->get()->shuffle();
        $participants = Participant::withCount('teams')->orderBy('position')->get();

        foreach ($teams as $team) {
            $target = $participants
                ->filter(fn ($p) => $p->teams_count < self::TEAMS_PER_PARTICIPANT)
                ->sortBy('teams_count')->first();

            if (! $target) {
                break;
            }

            Assignment::create(['participant_id' => $target->id, 'team_id' => $team->id]);
            $target->teams_count++;
        }

        return back()->with('status', 'Equipos repartidos al azar entre los cupos disponibles.');
    }

    public function clearAssignments()
    {
        Assignment::query()->delete();

        return back()->with('status', 'Asignaciones borradas.');
    }

    /** Pantalla de carga/edición manual de marcadores. */
    public function results()
    {
        $stages = Fixture::with(['homeTeam', 'awayTeam', 'group'])
            ->orderBy('kickoff_at')->orderBy('id')->get()
            ->groupBy('stage')
            ->sortBy(fn ($g, $stage) => array_search($stage, array_keys(Fixture::STAGES)));

        return view('admin.resultados', ['stages' => $stages]);
    }

    public function updateResult(Request $request, Fixture $fixture)
    {
        $data = $request->validate([
            'home_score' => 'nullable|integer|min:0|max:99',
            'away_score' => 'nullable|integer|min:0|max:99',
            'home_pens'  => 'nullable|integer|min:0|max:99',
            'away_pens'  => 'nullable|integer|min:0|max:99',
            'status'     => 'required|in:scheduled,live,finished',
        ]);

        $fixture->update($data);

        // Recalcular cuadro, eliminados y puntaje.
        $this->bracket->generate();
        $this->scoring->recompute();

        return back()->with('status', 'Marcador guardado y puntos recalculados.');
    }

    public function sync()
    {
        try {
            $result = $this->sync->sync();

            return back()->with('status', "Sincronizado con la API: {$result['updated']} partidos actualizados.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
