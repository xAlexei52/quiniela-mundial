@php
    $owners = $owners ?? collect();
    $home = $fx->homeTeam; $away = $fx->awayTeam;
    $finished = $fx->isFinished();
    $stageLabel = \App\Models\Fixture::STAGES[$fx->stage] ?? '';
@endphp
<div class="match">
    <div class="side">
        <span class="team">@include('partials.flag', ['team' => $home]) {{ $home?->name ?? 'Por definir' }}</span>
        @if ($home && ($o = ($owners[$home->id] ?? null)))<span class="owner">{{ $o }}</span>@endif
    </div>

    <div class="score">
        @if ($finished)
            {{ $fx->home_score }}–{{ $fx->away_score }}
            @if ($fx->home_pens !== null && $fx->away_pens !== null)
                <div class="meta">pen {{ $fx->home_pens }}–{{ $fx->away_pens }}</div>
            @endif
        @else
            <span class="vs">vs</span>
            <div class="meta">{{ $fx->kickoff_at?->format('d/m H:i') }}</div>
        @endif
    </div>

    <div class="side right">
        <span class="team">{{ $away?->name ?? 'Por definir' }} @include('partials.flag', ['team' => $away])</span>
        @if ($away && ($o = ($owners[$away->id] ?? null)))<span class="owner">{{ $o }}</span>@endif
    </div>
</div>
@if (!empty($stageLabel) && ($showStage ?? false))
    <div class="meta" style="margin:.1rem 0 .4rem">{{ $stageLabel }}</div>
@endif
