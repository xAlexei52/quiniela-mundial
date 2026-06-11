@php
    /** @var \App\Models\Team|null $team */
    $w = $w ?? 40;
    $url = $team?->flagUrl($w);
@endphp
@if ($url)
    <img src="{{ $url }}" class="flag-img" alt="{{ $team->name }}" loading="lazy">
@else
    <span class="flag">{{ $team?->flag }}</span>
@endif
