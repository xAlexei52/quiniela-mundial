@php
    /** @var \App\Models\Fixture $fx */
    $owners = $owners ?? collect();
    $finished = $fx->isFinished();
    $winner = $fx->winnerTeamId();
    $rows = [
        ['team' => $fx->homeTeam, 'score' => $fx->home_score, 'pen' => $fx->home_pens, 'id' => $fx->home_team_id],
        ['team' => $fx->awayTeam, 'score' => $fx->away_score, 'pen' => $fx->away_pens, 'id' => $fx->away_team_id],
    ];
@endphp
<div class="bkt-inner">
    @foreach ($rows as $r)
        @php $cls = $finished ? ($winner === $r['id'] ? 'win' : 'lose') : ''; @endphp
        <div class="bkt-row {{ $cls }}" @if($r['team'] && ($o = ($owners[$r['team']->id] ?? null))) title="{{ $o }}" @endif>
            @include('partials.flag', ['team' => $r['team']])
            <span class="nm">{{ $r['team']?->name ?? 'Por definir' }}</span>
            @if ($r['pen'] !== null)<span class="sc pen">({{ $r['pen'] }})</span>@endif
            <span class="sc">{{ $finished ? $r['score'] : '' }}</span>
        </div>
    @endforeach
</div>
