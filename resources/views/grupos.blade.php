@extends('layouts.app')
@section('title', 'Grupos')

@section('content')
<div class="page-head">
    <h1>Fase de grupos</h1>
    <div class="sub">12 grupos · clasifican los 2 primeros + los 8 mejores terceros</div>
</div>

<div class="grid cols-2">
    @foreach ($groups as $g)
        <div class="card">
            <h3>Grupo {{ $g['group']->name }}</h3>

            <table>
                <thead>
                    <tr>
                        <th class="num">#</th>
                        <th>Equipo</th>
                        <th class="num">PJ</th>
                        <th class="num">G</th>
                        <th class="num">E</th>
                        <th class="num">P</th>
                        <th class="num">DG</th>
                        <th class="num">Pts</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($g['rows'] as $row)
                        <tr class="{{ $row['position'] <= 2 ? 'qualified' : '' }}">
                            <td class="num">{{ $row['position'] }}</td>
                            <td>
                                @include('partials.flag', ['team' => $row['team']])
                                {{ $row['team']->name }}
                                @if ($owners[$row['team']->id] ?? null)
                                    <span style="margin-left:.35rem">@include('partials.owner', ['participant' => $owners[$row['team']->id]])</span>
                                @endif
                            </td>
                            <td class="num">{{ $row['played'] }}</td>
                            <td class="num">{{ $row['won'] }}</td>
                            <td class="num">{{ $row['drawn'] }}</td>
                            <td class="num">{{ $row['lost'] }}</td>
                            <td class="num">{{ $row['gd'] > 0 ? '+'.$row['gd'] : $row['gd'] }}</td>
                            <td class="num"><b>{{ $row['points'] }}</b></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="divider"></div>
            <h3>Partidos</h3>
            @foreach ($g['matches'] as $fx)
                @include('partials.match', ['fx' => $fx, 'owners' => $owners])
            @endforeach
        </div>
    @endforeach
</div>
@endsection
