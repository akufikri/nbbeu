@props(['color' => 'teal'])

@php
    $stroke = match ($color) {
        'navy' => '#16305C',
        default => '#18AFBF',
    };
@endphp

<svg viewBox="0 0 1200 60" class="w-full h-8 md:h-12" preserveAspectRatio="none" aria-hidden="true">
    <path d="M0,0 Q600,60 1200,0" fill="none" stroke="{{ $stroke }}" stroke-opacity="0.35" stroke-width="1.5" />
</svg>
