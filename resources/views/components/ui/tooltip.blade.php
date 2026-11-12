@props(['text'])

<span class="relative inline-flex" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @focusin="open = true" @focusout="open = false">
    {{ $slot }}
    <span
        role="tooltip"
        x-show="open"
        x-cloak
        class="pointer-events-none absolute bottom-full left-1/2 z-30 mb-fs-2xs w-max max-w-xs -translate-x-1/2 rounded-fs-xs bg-text px-fs-2xs py-fs-3xs text-xs text-text-inverse shadow-fs-sm"
    >
        {{ $text }}
    </span>
</span>
