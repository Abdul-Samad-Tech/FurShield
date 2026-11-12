@props([
    'name' => 'dialog',
    'title',
])

<div x-data="fsModal()" class="inline-flex" @keydown.escape.window="if (shown) close()">
    @isset($trigger)
        <div class="inline-flex" @click="open()">{{ $trigger }}</div>
    @endisset

    <div
        x-show="shown"
        x-cloak
        class="fixed inset-0 z-[60] flex items-end justify-center p-fs-sm sm:items-center"
        x-transition:enter="motion-safe:transition motion-safe:duration-fs-base motion-safe:ease-fs-enter"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="motion-safe:transition motion-safe:duration-fs-fast motion-safe:ease-fs-exit"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-text/40" @click="close()"></div>
        <div
            x-ref="dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="fs-modal-{{ $name }}-title"
            tabindex="-1"
            @keydown.tab="trap($event)"
            class="relative max-h-[calc(100vh-2rem)] w-full max-w-lg overflow-y-auto rounded-fs-lg border border-border bg-surface-raised p-fs-md text-text shadow-fs-lg focus:outline-none"
        >
            <div class="flex items-start justify-between gap-fs-sm">
                <h2 id="fs-modal-{{ $name }}-title" class="font-display text-2xl font-semibold">{{ $title }}</h2>
                <button
                    type="button"
                    class="grid size-11 shrink-0 place-items-center rounded-fs-sm text-text-muted motion-safe:transition motion-safe:duration-fs-fast motion-safe:ease-fs-standard hover:bg-surface-sunken hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus"
                    @click="close()"
                    aria-label="Close dialog"
                >
                    <x-ui.icon name="x" />
                </button>
            </div>
            <div class="mt-fs-sm">{{ $slot }}</div>
        </div>
    </div>
</div>
