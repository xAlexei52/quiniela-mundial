@extends('layouts.app')
@section('title', 'Eliminatorias')

@section('content')
<div class="page-head">
    <h1>Eliminatorias</h1>
    <div class="sub">Dieciseisavos → Octavos → Cuartos → Semifinal → Final</div>
</div>

@if ($rounds->isEmpty())
    <div class="notice" style="margin-bottom:1rem">
        El cuadro se genera automáticamente cuando termine la fase de grupos.
        @if ($qualified->isNotEmpty()) Mientras tanto, estos son los equipos que van clasificando: @endif
    </div>

    @if ($qualified->isNotEmpty())
        <div class="card">
            <h3>Clasificados (provisional)</h3>
            <div class="row">
                @foreach ($qualified as $team)
                    <span class="team-chip">@include('partials.flag', ['team' => $team]) {{ $team->name }}</span>
                @endforeach
            </div>
        </div>
    @endif
@else
    <div class="grid cols-3">
        @foreach ($rounds as $stage => $fixtures)
            <div class="card">
                <h3>{{ \App\Models\Fixture::STAGES[$stage] ?? $stage }}</h3>
                @foreach ($fixtures as $fx)
                    @include('partials.match', ['fx' => $fx, 'owners' => $owners])
                @endforeach
            </div>
        @endforeach
    </div>
@endif
@endsection
