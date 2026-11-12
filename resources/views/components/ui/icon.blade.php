@props([
    'name',
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'size-4',
        'md' => 'size-5',
        'lg' => 'size-6',
    ];
    $labelled = $attributes->has('aria-label');
@endphp

<i
    data-lucide="{{ $name }}"
    @unless ($labelled) aria-hidden="true" @endunless
    {{ $attributes->merge(['class' => 'inline-block shrink-0 '.($sizes[$size] ?? $sizes['md'])]) }}
></i>
