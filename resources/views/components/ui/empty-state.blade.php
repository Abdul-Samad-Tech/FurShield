@props([
    'title',
    'icon' => 'inbox',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center px-fs-md py-fs-xl text-center']) }}>
    <span class="grid size-12 place-items-center rounded-fs-xl bg-surface-sunken text-text">
        <x-ui.icon :name="$icon" size="lg" />
    </span>
    <p class="mt-fs-sm font-display text-2xl font-semibold text-text">{{ $title }}</p>
    @if ($description)
        <p class="mt-fs-2xs max-w-md text-sm text-text-muted">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-fs-md">{{ $action }}</div>
    @endisset
</div>
