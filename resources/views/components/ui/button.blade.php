@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'loading' => false,
    'disabled' => false,
])

@php
    $variants = [
        'primary' => 'bg-success text-text-inverse shadow-fs-sm hover:bg-tide-deep',
        'secondary' => 'border border-border-strong bg-surface-raised text-text hover:bg-surface-sunken',
        'ghost' => 'bg-transparent text-success hover:bg-success-soft hover:text-text',
        'danger' => 'bg-danger text-text-inverse hover:bg-danger/90',
    ];
    $sizes = [
        'sm' => 'min-h-9 rounded-fs-sm px-fs-sm py-fs-2xs text-sm',
        'md' => 'min-h-11 rounded-fs-md px-fs-md py-fs-xs text-sm',
        'lg' => 'min-h-12 rounded-fs-md px-fs-lg py-fs-sm text-base',
    ];
    $classes = implode(' ', [
        'inline-flex items-center justify-center gap-fs-2xs font-medium',
        'motion-safe:transition motion-safe:duration-fs-fast motion-safe:ease-fs-standard',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus focus-visible:ring-offset-2 focus-visible:ring-offset-surface',
        'disabled:cursor-not-allowed disabled:opacity-60',
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
    ]);
    $isDisabled = $disabled || $loading;
    $tag = ($href && ! $isDisabled) ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if ($tag === 'a')
        href="{{ $href }}"
    @else
        type="{{ $type }}"
        @disabled($isDisabled)
    @endif
    aria-busy="{{ $loading ? 'true' : 'false' }}"
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if ($loading)
        <span class="ui-spinner" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</{{ $tag }}>
