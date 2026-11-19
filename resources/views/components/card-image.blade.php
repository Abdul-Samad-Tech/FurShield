@props([
    'src' => null,
    'type' => 'pet',
    'seed' => 0,
    'alt' => 'FurShield',
    'variant' => 'card',
    'eager' => false,
])

@php
    $image = \App\Support\DummyImages::describe($src, $type, (int) $seed);
    $altText = ($alt !== null && $alt !== '' && $alt !== 'FurShield') ? $alt : $image['alt'];

    $variants = [
        'card' => 'h-48 w-full',
        'thumb' => 'h-16 w-16',
        'wide' => 'h-44 w-full',
        'hero' => 'h-56 w-full',
        'square' => 'h-72 w-full sm:h-80',
    ];

    $sizeClass = $variants[$variant] ?? $variants['card'];
@endphp

<div {{ $attributes->class(['relative overflow-hidden bg-surface-sunken', $variant === 'thumb' ? 'rounded-2xl shrink-0' : '']) }}>
    <div class="absolute inset-0 motion-safe:animate-pulse bg-surface-sunken" data-image-skeleton aria-hidden="true"></div>
    <img
        src="{{ $image['url'] }}"
        alt="{{ $altText }}"
        @if ($image['width']) width="{{ $image['width'] }}" @endif
        @if ($image['height']) height="{{ $image['height'] }}" @endif
        @if ($eager) fetchpriority="high" @else loading="lazy" fetchpriority="low" @endif
        decoding="async"
        class="{{ $sizeClass }} relative bg-surface-sunken object-contain object-center"
        onload="this.previousElementSibling && this.previousElementSibling.remove()"
    >
</div>
