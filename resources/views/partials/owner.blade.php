@php /** @var \App\Models\Participant|null $participant */ @endphp
@if ($participant)
    <span class="owner-tag" style="color: {{ $participant->color() }}">
        <span class="owner-dot"></span>{{ $participant->name }}
    </span>
@endif
