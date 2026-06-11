@extends('layouts.app')
@section('title', 'Admin')

@section('content')
<div class="page-head spread">
    <div>
        <h1>Panel de administración</h1>
        <div class="sub">Registra participantes y asígnales sus {{ $perParticipant }} equipos</div>
    </div>
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button class="btn sm">Salir</button>
    </form>
</div>

{{-- Estado / acciones globales --}}
<div class="grid cols-4" style="margin-bottom:1.2rem">
    <div class="card"><div class="stat"><span class="big green">{{ $participants->count() }}</span><span class="lbl">Participantes</span></div></div>
    <div class="card"><div class="stat"><span class="big">{{ $assignedCount }}/{{ $teamCount }}</span><span class="lbl">Equipos asignados</span></div></div>
    <div class="card"><div class="stat"><span class="big {{ $apiConfigured ? 'green' : 'muted' }}">{{ $apiConfigured ? 'ON' : 'OFF' }}</span><span class="lbl">API resultados</span></div></div>
    <div class="card">
        <h3>Acciones</h3>
        <div class="row">
            <form method="POST" action="{{ route('admin.sync') }}">
                @csrf
                <button class="btn primary sm" {{ $apiConfigured ? '' : 'disabled' }}>@include('partials.icon', ['name' => 'refresh', 'size' => 15]) Sincronizar</button>
            </form>
            <form method="POST" action="{{ route('admin.teams.random') }}" onsubmit="return confirm('¿Repartir al azar los equipos sin dueño?')">
                @csrf
                <button class="btn sm">@include('partials.icon', ['name' => 'dice', 'size' => 15]) Repartir al azar</button>
            </form>
            <form method="POST" action="{{ route('admin.teams.clear') }}" onsubmit="return confirm('¿Borrar TODAS las asignaciones?')">
                @csrf
                <button class="btn danger sm">Limpiar</button>
            </form>
        </div>
    </div>
</div>

{{-- Alta de participante --}}
<div class="card" style="margin-bottom:1.2rem">
    <h3>Agregar participante</h3>
    <form method="POST" action="{{ route('admin.participants.store') }}" class="row">
        @csrf
        <input type="text" name="name" placeholder="Nombre del participante" required style="flex:1; min-width:200px">
        <button class="btn primary">Agregar</button>
    </form>
</div>

{{-- Participantes --}}
<div class="grid cols-3">
    @forelse ($participants as $p)
        <div class="card">
            <div class="spread" style="margin-bottom:.6rem">
                <form method="POST" action="{{ route('admin.participants.update', $p) }}" class="row" style="flex:1; gap:.4rem">
                    @csrf @method('PUT')
                    <input type="text" name="name" value="{{ $p->name }}" style="flex:1; min-width:0">
                    <button class="btn sm" title="Guardar">@include('partials.icon', ['name' => 'check', 'size' => 15])</button>
                </form>
                <form method="POST" action="{{ route('admin.participants.destroy', $p) }}" onsubmit="return confirm('¿Eliminar a {{ $p->name }}?')">
                    @csrf @method('DELETE')
                    <button class="btn danger sm" title="Eliminar">@include('partials.icon', ['name' => 'trash', 'size' => 15])</button>
                </form>
            </div>

            <div class="muted" style="font-size:.78rem; margin-bottom:.4rem">{{ $p->teams->count() }}/{{ $perParticipant }} equipos</div>

            @foreach ($p->teams as $team)
                <div class="spread" style="padding:.3rem 0; border-bottom:1px solid var(--border)">
                    <span class="team-chip">@include('partials.flag', ['team' => $team]) {{ $team->name }}</span>
                    <form method="POST" action="{{ route('admin.teams.unassign', $team) }}">
                        @csrf @method('DELETE')
                        <button class="btn danger sm">quitar</button>
                    </form>
                </div>
            @endforeach

            @if ($p->teams->count() < $perParticipant)
                <form method="POST" action="{{ route('admin.teams.assign', $p) }}" class="row" style="margin-top:.6rem; gap:.4rem">
                    @csrf
                    <select name="team_id" required style="flex:1; min-width:0">
                        <option value="">+ Asignar equipo…</option>
                        @foreach ($available as $team)
                            <option value="{{ $team->id }}">{{ $team->name }} ({{ $team->group?->name ? 'Gpo '.$team->group->name : 's/g' }})</option>
                        @endforeach
                    </select>
                    <button class="btn primary sm">Añadir</button>
                </form>
            @endif
        </div>
    @empty
        <p class="muted">No hay participantes. Agrega el primero arriba.</p>
    @endforelse
</div>
@endsection
