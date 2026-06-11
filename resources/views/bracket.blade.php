@extends('layouts.app')
@section('title', 'Eliminatorias')

@section('content')
@php
    $order = ['r32', 'r16', 'qf', 'sf', 'final'];
    $treeStages = collect($order)->filter(fn ($s) => isset($rounds[$s]) && $rounds[$s]->isNotEmpty());
    $thirdPlace = $rounds['third_place'] ?? collect();
@endphp

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
    <div class="bracket-wrap">
        <div class="bracket">
            @foreach ($treeStages as $stage)
                <div class="round-col">
                    <div class="round-title">{{ \App\Models\Fixture::STAGES[$stage] ?? $stage }}</div>
                    <div class="round-matches">
                        @foreach ($rounds[$stage] as $fx)
                            <div class="bkt">
                                @include('partials.bkt', ['fx' => $fx, 'owners' => $owners])
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if ($thirdPlace->isNotEmpty())
        <div class="card" style="max-width:360px; margin-top:1.2rem">
            <h3>🥉 Tercer puesto</h3>
            @foreach ($thirdPlace as $fx)
                @include('partials.bkt', ['fx' => $fx, 'owners' => $owners])
            @endforeach
        </div>
    @endif
@endif
@endsection
