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
                        <td><span class="owner-dot" style="color: {{ $row['participant']->color() }}; vertical-align:middle; margin-right:.4rem"></span>{{ $row['participant']->name }}</td>
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

    {{-- Gráfica de puntos (línea) --}}
    <div class="card">
        <h3>Gráfica de puntos</h3>
        @php
            $pts = $leaderboard->values();
            $n = $pts->count();
            $W = 620; $H = 234; $pl = 16; $pr = 16; $pt = 26; $pb = 50;
            $plotW = $W - $pl - $pr; $plotH = $H - $pt - $pb;
            $max = max(1, (int) $maxScore);
            $coords = [];
            foreach ($pts as $i => $row) {
                $x = $n > 1 ? $pl + $plotW * $i / ($n - 1) : $pl + $plotW / 2;
                $y = $pt + $plotH * (1 - $row['score'] / $max);
                $coords[] = ['x' => round($x, 1), 'y' => round($y, 1), 'row' => $row];
            }
            $line = collect($coords)->map(fn ($c) => $c['x'].','.$c['y'])->implode(' ');
            $base = $pt + $plotH;
            $area = $coords ? ($coords[0]['x'].','.$base.' '.$line.' '.end($coords)['x'].','.$base) : '';
        @endphp

        @if ($n)
            <svg class="line-chart" viewBox="0 0 {{ $W }} {{ $H }}" preserveAspectRatio="xMidYMid meet" role="img">
                <defs>
                    <linearGradient id="lc-grad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#11b7c6" stop-opacity="0.35"/>
                        <stop offset="100%" stop-color="#11b7c6" stop-opacity="0"/>
                    </linearGradient>
                </defs>

                {{-- líneas de referencia --}}
                @foreach ([0, 0.5, 1] as $g)
                    @php $gy = $pt + $plotH * $g; @endphp
                    <line class="grid-line" x1="{{ $pl }}" y1="{{ $gy }}" x2="{{ $W - $pr }}" y2="{{ $gy }}"/>
                @endforeach

                <polygon class="area" points="{{ $area }}"/>
                <polyline class="line" points="{{ $line }}"/>

                @foreach ($coords as $i => $c)
                    <circle class="dot {{ $i === 0 ? 'lead' : '' }}" cx="{{ $c['x'] }}" cy="{{ $c['y'] }}" r="4.5"/>
                    <text class="val" x="{{ $c['x'] }}" y="{{ $c['y'] - 10 }}">{{ $c['row']['score'] }}</text>
                    <text class="xlabel" x="{{ $c['x'] }}" y="{{ $H - 26 }}"
                          text-anchor="end" transform="rotate(-40 {{ $c['x'] }} {{ $H - 26 }})">
                        {{ \Illuminate\Support\Str::limit(\Illuminate\Support\Str::before($c['row']['participant']->name, ' '), 9, '') }}
                    </text>
                @endforeach
            </svg>
        @else
            <p class="muted">Sin datos todavía.</p>
        @endif
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
