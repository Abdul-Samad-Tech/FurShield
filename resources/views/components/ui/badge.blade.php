@props(['variant' => 'neutral'])

@php
    $variants = [
        'neutral' => 'bg-surface-sunken text-text',
        'tide' => 'bg-success-soft text-text',
        'bloom' => 'bg-warning-soft text-text',
        'soft' => 'bg-success-soft text-text',
        'warm' => 'bg-warning-soft text-text',
        'solid' => 'bg-success text-text-inverse',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-fs-xs px-fs-2xs py-fs-3xs text-xs font-medium '.($variants[$variant] ?? $variants['neutral'])]) }}>
    {{ $slot }}
</span>
