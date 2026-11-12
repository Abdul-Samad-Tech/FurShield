@props([
    'name',
    'src' => null,
    'size' => 'md',
])

@php
    $initials = collect(preg_split('/\s+/u', trim($name)))
        ->filter()
        ->take(2)
        ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    $sizes = [
        'sm' => 'size-8 text-xs',
        'md' => 'size-10 text-sm',
        'lg' => 'size-14 text-base',
    ];
@endphp

<span
    {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center overflow-hidden rounded-fs-xl bg-success-soft font-medium text-text '.($sizes[$size] ?? $sizes['md'])]) }}
    role="img"
    aria-label="{{ $name }}"
>
    @if ($src)
        <img src="{{ $src }}" alt="" class="size-full object-cover" onerror="this.hidden = true; this.nextElementSibling.hidden = false">
        <span hidden>{{ $initials }}</span>
    @else
        <span>{{ $initials }}</span>
    @endif
</span>
