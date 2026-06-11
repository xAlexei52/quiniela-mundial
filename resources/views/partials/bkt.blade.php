@php
    /** @var \App\Models\Fixture $fx */
    $owners = $owners ?? collect();
    $finished = $fx->isFinished();
    $live = $fx->isLive();
    $winner = $fx->winnerTeamId();
    $rows = [
        ['team' => $fx->homeTeam, 'score' => $fx->home_score, 'pen' => $fx->home_pens, 'id' => $fx->home_team_id],
        ['team' => $fx->awayTeam, 'score' => $fx->away_score, 'pen' => $fx->away_pens, 'id' => $fx->away_team_id],
    ];
@endphp
<div class="bkt-inner {{ $live ? 'live' : '' }}">
    @if ($live)<div class="bkt-live"><span class="live-pulse"></span>EN VIVO</div>@endif
    @foreach ($rows as $r)
        @php $cls = $finished ? ($winner === $r['id'] ? 'win' : 'lose') : ''; @endphp
        @php $o = $r['team'] ? ($owners[$r['team']->id] ?? null) : null; @endphp
        <div class="bkt-row {{ $cls }}" @if($o) title="{{ $o->name }}" @endif>
            @include('partials.flag', ['team' => $r['team']])
            @if($o)<span class="owner-dot" style="color: {{ $o->color() }}"></span>@endif
            <span class="nm">{{ $r['team']?->name ?? 'Por definir' }}</span>
            @if ($r['pen'] !== null)<span class="sc pen">({{ $r['pen'] }})</span>@endif
            <span class="sc">{{ ($finished || $live) ? ($r['score'] ?? 0) : '' }}</span>
        </div>
    @endforeach
</div>
