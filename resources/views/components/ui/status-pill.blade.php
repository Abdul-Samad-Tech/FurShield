@props(['variant' => 'neutral'])

@php
    $variants = [
        'success' => ['wrap' => 'bg-success-soft text-text', 'dot' => 'bg-success'],
        'warning' => ['wrap' => 'bg-warning-soft text-text', 'dot' => 'bg-warning'],
        'danger' => ['wrap' => 'bg-danger-soft text-text', 'dot' => 'bg-danger'],
        'neutral' => ['wrap' => 'bg-surface-sunken text-text-muted', 'dot' => 'bg-text-muted'],
    ];
    $tone = $variants[$variant] ?? $variants['neutral'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-fs-2xs rounded-fs-xl px-fs-xs py-fs-3xs text-xs font-medium '.$tone['wrap']]) }}>
    <span class="size-1.5 rounded-full {{ $tone['dot'] }}" aria-hidden="true"></span>
    {{ $slot }}
</span>
