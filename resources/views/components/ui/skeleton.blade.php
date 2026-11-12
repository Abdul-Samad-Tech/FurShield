@props(['variant' => 'text'])

<div {{ $attributes->merge(['class' => 'motion-safe:animate-pulse', 'role' => 'status', 'aria-label' => 'Loading']) }}>
    @if ($variant === 'avatar')
        <div class="size-10 rounded-fs-xl bg-surface-sunken"></div>
    @elseif ($variant === 'card')
        <div class="rounded-fs-lg border border-border bg-surface-raised p-fs-md shadow-fs-xs">
            <div class="h-4 w-1/3 rounded-fs-xs bg-surface-sunken"></div>
            <div class="mt-fs-sm h-3 w-full rounded-fs-xs bg-surface-sunken"></div>
            <div class="mt-fs-2xs h-3 w-4/5 rounded-fs-xs bg-surface-sunken"></div>
        </div>
    @else
        <div class="grid gap-fs-2xs">
            <div class="h-3 w-full rounded-fs-xs bg-surface-sunken"></div>
            <div class="h-3 w-5/6 rounded-fs-xs bg-surface-sunken"></div>
            <div class="h-3 w-2/3 rounded-fs-xs bg-surface-sunken"></div>
        </div>
    @endif
    <span class="sr-only">Loading</span>
</div>
