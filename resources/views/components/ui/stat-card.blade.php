@props([
    'label',
    'value',
    'icon' => null,
    'delta' => null,
    'trend' => null,
    'count' => false,
])

@php
    $deltaTone = match ($trend) {
        'up' => 'text-success',
        'down' => 'text-danger',
        default => 'text-text-muted',
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-fs-lg border border-border bg-surface-raised p-fs-md text-text shadow-fs-sm', 'data-ui-card' => 'true']) }}>
    <div class="flex items-start justify-between gap-fs-sm">
        <p class="text-sm text-text-muted">{{ $label }}</p>
        @if ($icon)
            <span class="grid size-10 shrink-0 place-items-center rounded-fs-sm bg-success-soft text-text">
                <x-ui.icon :name="$icon" />
            </span>
        @endif
    </div>
    <p class="mt-fs-sm font-display text-3xl font-semibold tabular-nums tracking-tight" @if ($count) data-count="{{ $value }}" @endif>{{ $value }}</p>
    @if ($delta)
        <p class="mt-fs-2xs inline-flex items-center gap-fs-3xs text-sm font-medium tabular-nums {{ $deltaTone }}">
            @if ($trend === 'up')
                <x-ui.icon name="trending-up" size="sm" />
            @elseif ($trend === 'down')
                <x-ui.icon name="trending-down" size="sm" />
            @endif
            {{ $delta }}
        </p>
    @endif
    @isset($sparkline)
        <div class="mt-fs-sm text-success">{{ $sparkline }}</div>
    @endisset
</div>
