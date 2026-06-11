@php
    $owners = $owners ?? collect();
    $home = $fx->homeTeam; $away = $fx->awayTeam;
    $finished = $fx->isFinished();
    $live = $fx->isLive();
    $stageLabel = \App\Models\Fixture::STAGES[$fx->stage] ?? '';
@endphp
<div class="match {{ $live ? 'live' : '' }}">
    <div class="side">
        <span class="team">@include('partials.flag', ['team' => $home]) {{ $home?->name ?? 'Por definir' }}</span>
        @if ($home)@include('partials.owner', ['participant' => $owners[$home->id] ?? null])@endif
    </div>

    <div class="score">
        @if ($finished)
            {{ $fx->home_score }}–{{ $fx->away_score }}
            @if ($fx->home_pens !== null && $fx->away_pens !== null)
                <div class="meta">pen {{ $fx->home_pens }}–{{ $fx->away_pens }}</div>
            @endif
        @elseif ($live)
            {{ $fx->home_score ?? 0 }}–{{ $fx->away_score ?? 0 }}
            <div class="live-tag"><span class="live-pulse"></span>EN VIVO</div>
        @else
            <span class="vs">vs</span>
            <div class="meta">{{ $fx->kickoffLocal()?->format('d/m H:i') }}</div>
        @endif
    </div>

    <div class="side right">
        <span class="team">{{ $away?->name ?? 'Por definir' }} @include('partials.flag', ['team' => $away])</span>
        @if ($away)@include('partials.owner', ['participant' => $owners[$away->id] ?? null])@endif
    </div>
</div>
@if (!empty($stageLabel) && ($showStage ?? false))
    <div class="meta" style="margin:.1rem 0 .4rem">{{ $stageLabel }}</div>
@endif
