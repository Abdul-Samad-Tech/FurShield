@props([])

{{-- A card is a single surface. Do not put a card inside a card. --}}
<div {{ $attributes->merge(['class' => 'rounded-fs-lg border border-border bg-surface-raised p-fs-md text-text shadow-fs-md', 'data-ui-card' => 'true']) }}>
    @isset($header)
        <div class="mb-fs-sm">{{ $header }}</div>
    @endisset

    {{ $slot }}

    @isset($footer)
        <div class="mt-fs-md border-t border-border pt-fs-sm">{{ $footer }}</div>
    @endisset
</div>
