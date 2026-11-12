@props([
    'message' => null,
    'variant' => 'success',
    'offset' => 'top-24',
])

@php
    $initial = $message ? [['message' => $message, 'variant' => $variant]] : [];
@endphp

<div
    x-data='fsToasts(@json($initial))'
    class="pointer-events-none fixed right-4 z-[90] flex w-[calc(100vw-2rem)] max-w-sm flex-col gap-fs-2xs {{ $offset }}"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            role="status"
            class="pointer-events-auto flex items-start gap-fs-sm rounded-fs-md border px-fs-sm py-fs-xs text-sm shadow-fs-md"
            :class="{
                'border-success/30 bg-success-soft text-text': toast.variant === 'success',
                'border-warning/30 bg-warning-soft text-text': toast.variant === 'warning',
                'border-danger/30 bg-danger-soft text-text': toast.variant === 'danger',
            }"
            x-transition:enter="motion-safe:transition motion-safe:duration-fs-base motion-safe:ease-fs-enter"
            x-transition:enter-start="translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="motion-safe:transition motion-safe:duration-fs-fast motion-safe:ease-fs-exit"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <p class="min-w-0 flex-1 pt-fs-3xs" x-text="toast.message"></p>
            <button
                type="button"
                x-show="toast.action"
                x-cloak
                class="shrink-0 rounded-fs-sm px-fs-2xs py-fs-3xs text-sm font-medium text-text underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus"
                @click="act(toast)"
                x-text="toast.action"
            ></button>
            <button
                type="button"
                class="grid size-9 shrink-0 place-items-center rounded-fs-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus"
                @click="dismiss(toast.id)"
                aria-label="Dismiss notification"
            >
                <x-ui.icon name="x" size="sm" />
            </button>
        </div>
    </template>
</div>
