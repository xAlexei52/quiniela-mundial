@extends('layouts.app')
@section('title', 'Participantes')

@section('content')
<div class="page-head">
    <h1>Participantes</h1>
    <div class="sub">Equipos de cada quien y sus puntos
        @if ($unassigned > 0) · <span class="gold">{{ $unassigned }} equipos sin asignar</span> @endif
    </div>
</div>

<div class="grid cols-3">
    @foreach ($leaderboard as $i => $row)
        <div class="card">
            <div class="spread" style="margin-bottom:.6rem">
                <div class="row" style="gap:.5rem">
                    <span class="rank-medal">{{ ['🥇','🥈','🥉'][$i] ?? '#'.($i+1) }}</span>
                    <b style="color: {{ $row['participant']->color() }}">{{ $row['participant']->name }}</b>
                </div>
                <span class="pill-pts" style="font-size:1.2rem">{{ $row['score'] }} pts</span>
            </div>

            <div class="row" style="gap:.4rem; margin-bottom:.7rem">
                <span class="tag alive"><span class="dot"></span>{{ $row['alive'] }} vivos</span>
                @if ($row['eliminated'] > 0)<span class="tag out">{{ $row['eliminated'] }} fuera</span>@endif
            </div>

            @forelse ($row['teams'] as $t)
                <div class="spread" style="padding:.35rem 0; border-bottom:1px solid var(--border)">
                    <span class="team-chip {{ $t['alive'] ? '' : 'out' }}">
                        @include('partials.flag', ['team' => $t['team']]) {{ $t['team']->name }}
                    </span>
                    <span class="row" style="gap:.5rem">
                        <span class="muted" style="font-size:.72rem">{{ $t['status_label'] }}</span>
                        <span class="pill-pts">{{ $t['score'] }}</span>
                    </span>
                </div>
            @empty
                <p class="muted">Sin equipos asignados todavía.</p>
            @endforelse
        </div>
    @endforeach
</div>
@endsection
