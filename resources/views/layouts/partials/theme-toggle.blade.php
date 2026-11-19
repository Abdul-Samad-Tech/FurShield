@php
    $onHero = $onHero ?? false;
    $compact = $compact ?? false;
@endphp
<button
    type="button"
    data-theme-toggle
    @class([
        'grid place-items-center focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-current',
        'size-11 text-inherit' => $compact,
        'size-11 rounded-xl focus-visible:ring-2 focus-visible:ring-focus' => ! $compact,
        'text-text hover:bg-surface-sunken' => ! $compact && ! $onHero,
    ])
    @if ($onHero && ! $compact) :class="(scrolled || open) ? 'text-ink' : 'text-white'" @endif
    aria-pressed="true"
    aria-label="Switch to light theme"
>
    <span class="hidden dark:inline-flex"><x-ui.icon name="sun" size="sm" /></span>
    <span class="inline-flex dark:hidden"><x-ui.icon name="moon" size="sm" /></span>
</button>
