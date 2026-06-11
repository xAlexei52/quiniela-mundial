@extends('layouts.app')
@section('title', 'Resumen')

@section('content')
@php $cur = config('quiniela.prize.currency'); @endphp

<div class="page-head spread">
    <div>
        <h1>Marcador general</h1>
        <div class="sub">Mundial 2026 · 12 participantes · 4 equipos c/u</div>
    </div>
    <div class="badge gold">Bote ${{ number_format($pool) }} {{ $cur }}</div>
</div>

{{-- Premios: top 3 --}}
<div class="grid cols-3" style="margin-bottom:1.2rem">
    @foreach ($prizes as $prize)
        <div class="prize p{{ $prize['place'] }}">
            <div class="place">{{ ['1°','2°','3°'][$prize['place']-1] }} lugar · {{ $prize['pct'] }}%</div>
            <div class="amount">${{ number_format($prize['amount']) }}</div>
            <div class="who">
                @if ($prize['winner'])
                    {{ ['🥇','🥈','🥉'][$prize['place']-1] }} {{ $prize['winner']->name }}
                    <span class="muted">· {{ $prize['score'] }} pts</span>
                @else
                    <span class="muted">Por definir</span>
                @endif
            </div>
        </div>
    @endforeach
</div>

<div class="grid cols-2" style="margin-bottom:1.2rem">
    {{-- Marcador / leaderboard --}}
    <div class="card">
        <h3>Marcador</h3>
        <table>
            <thead>
                <tr>
                    <th class="num">#</th>
                    <th>Participante</th>
                    <th class="num">Vivos</th>
                    <th class="num">Pts</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leaderboard as $i => $row)
                    <tr class="{{ $i < 3 ? 'qualified' : '' }}">
                        <td class="num">
                            <span class="rank-medal">{{ ['🥇','🥈','🥉'][$i] ?? ($i+1) }}</span>
                        </td>
                        <td>{{ $row['participant']->name }}</td>
                        <td class="num">
                            <span class="tag {{ $row['alive'] ? 'alive' : 'empty' }}">{{ $row['alive'] }}/{{ $row['total_teams'] }}</span>
                        </td>
                        <td class="num"><span class="pill-pts">{{ $row['score'] }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">Aún no hay participantes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Gráfica de puntos --}}
    <div class="card">
        <h3>Gráfica de puntos</h3>
        <div class="chart">
            @forelse ($leaderboard as $i => $row)
                <div class="chart-row {{ $i === 0 ? 'top' : '' }}">
                    <span class="name">{{ $row['participant']->name }}</span>
                    <span class="track"><span class="fill" style="width: {{ max(3, round($row['score'] / $maxScore * 100)) }}%"></span></span>
                    <span class="val">{{ $row['score'] }}</span>
                </div>
            @empty
                <p class="muted">Sin datos todavía.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="grid cols-2">
    {{-- Resultados recientes --}}
    <div class="card">
        <h3>Resultados recientes</h3>
        @forelse ($recent as $fx)
            @include('partials.match', ['fx' => $fx])
        @empty
            <p class="muted">Todavía no hay partidos jugados.</p>
        @endforelse
    </div>

    {{-- Próximos partidos --}}
    <div class="card">
        <h3>Próximos partidos</h3>
        @forelse ($upcoming as $fx)
            @include('partials.match', ['fx' => $fx])
        @empty
            <p class="muted">No hay próximos partidos programados.</p>
        @endforelse
    </div>
</div>
@endsection
