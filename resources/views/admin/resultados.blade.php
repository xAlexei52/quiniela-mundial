@extends('layouts.app')
@section('title', 'Resultados')

@section('content')
<div class="page-head spread">
    <div>
        <h1>Cargar resultados</h1>
        <div class="sub">Edita el marcador de cada partido. Al guardar se recalcula todo.</div>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn sm">← Volver al panel</a>
</div>

@foreach ($stages as $stage => $fixtures)
    <div class="card" style="margin-bottom:1rem">
        <h3>{{ \App\Models\Fixture::STAGES[$stage] ?? $stage }}</h3>

        @foreach ($fixtures as $fx)
            @php $ko = $stage !== 'group'; @endphp
            <form method="POST" action="{{ route('admin.resultados.update', $fx) }}" class="result-row">
                @csrf @method('PUT')

                <span class="re-team right">
                    {{ $fx->homeTeam?->name ?? 'Por definir' }} @include('partials.flag', ['team' => $fx->homeTeam])
                </span>

                <input type="number" name="home_score" value="{{ $fx->home_score }}" class="re-score" min="0" placeholder="-">
                <span class="muted">–</span>
                <input type="number" name="away_score" value="{{ $fx->away_score }}" class="re-score" min="0" placeholder="-">

                <span class="re-team">
                    @include('partials.flag', ['team' => $fx->awayTeam]) {{ $fx->awayTeam?->name ?? 'Por definir' }}
                </span>

                @if ($ko)
                    <span class="muted" style="font-size:.72rem">pen</span>
                    <input type="number" name="home_pens" value="{{ $fx->home_pens }}" class="re-score sm" min="0" placeholder="-">
                    <input type="number" name="away_pens" value="{{ $fx->away_pens }}" class="re-score sm" min="0" placeholder="-">
                @endif

                <select name="status">
                    @foreach (['scheduled' => 'Programado', 'live' => 'En vivo', 'finished' => 'Terminado'] as $val => $lbl)
                        <option value="{{ $val }}" @selected($fx->status === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>

                <button class="btn primary sm">Guardar</button>
            </form>
        @endforeach
    </div>
@endforeach
@endsection
