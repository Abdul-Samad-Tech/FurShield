@php
    $public = ($variant ?? 'dashboard') === 'public';
@endphp
<div
    class="relative"
    x-data="fsAlerts"
    data-unread="{{ $shellBadges['alerts'] }}"
    data-latest-id="{{ $alertLatestId }}"
    data-count-url="{{ route('notifications.unread-count') }}"
    data-list-url="{{ route('notifications.latest') }}"
    data-read-url="{{ url('/notifications') }}"
    @click.outside="open = false"
>
    <a
        href="{{ route('notifications.index') }}"
        class="relative grid size-11 place-items-center rounded-fs-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus {{ $public ? '' : 'text-text hover:bg-surface-sunken' }}"
        @if ($public) :class="$parent.scrolled ? 'text-ink' : 'text-white'" @endif
        @click.prevent="toggle()"
        :aria-expanded="open ? 'true' : 'false'"
        aria-haspopup="dialog"
        aria-controls="alert-panel"
        :aria-label="count > 0 ? 'Alerts, ' + count + ' unread' : 'Alerts'"
    >
        <x-ui.icon name="bell" />
        @if ($shellBadges['alerts'] > 0)
            <span x-show="count > 0" class="absolute right-1 top-1 grid min-w-4 place-items-center rounded-fs-xl bg-danger px-fs-3xs text-[0.65rem] leading-4 text-text-inverse" aria-hidden="true" x-text="count > 9 ? '9+' : count">{{ $shellBadges['alerts'] }}</span>
        @else
            <span x-show="count > 0" x-cloak class="absolute right-1 top-1 grid min-w-4 place-items-center rounded-fs-xl bg-danger px-fs-3xs text-[0.65rem] leading-4 text-text-inverse" aria-hidden="true" x-text="count > 9 ? '9+' : count"></span>
        @endif
    </a>
    <div
        id="alert-panel"
        x-show="open"
        x-cloak
        role="dialog"
        aria-label="Notifications"
        class="absolute right-0 z-50 mt-fs-2xs w-80 max-w-[calc(100vw-2rem)] rounded-fs-md border border-border bg-surface-raised p-fs-sm text-text shadow-fs-md"
    >
        <div class="flex items-center justify-between gap-fs-sm">
            <p class="font-display text-lg font-semibold">Notifications</p>
            <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-text underline-offset-2 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">See all</a>
        </div>
        <p x-show="loading" class="mt-fs-sm text-sm text-text" role="status">Loading notifications…</p>
        <p x-show="error" x-cloak class="mt-fs-sm text-sm text-text" role="alert" x-text="error"></p>
        <p x-show="!loading && !error && loaded && items.length === 0" x-cloak class="mt-fs-sm text-sm text-text">No notifications yet.</p>
        <ul class="mt-fs-sm grid max-h-80 gap-fs-2xs overflow-y-auto">
            <template x-for="item in items" :key="item.id">
                <li class="rounded-fs-sm border border-border px-fs-sm py-fs-xs" :class="item.read ? 'opacity-70' : 'bg-surface'">
                    <a :href="item.url || '{{ route('notifications.index') }}'" class="block text-sm font-medium text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" x-text="item.title"></a>
                    <p class="mt-fs-3xs text-sm text-text" x-text="item.message"></p>
                    <div class="mt-fs-2xs flex items-center justify-between gap-fs-sm">
                        <p class="text-xs text-text-muted" x-text="item.when"></p>
                        <button type="button" x-show="!item.read" class="min-h-9 rounded-fs-sm px-fs-xs text-sm font-medium text-text hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="markRead(item)">Mark as read</button>
                    </div>
                </li>
            </template>
        </ul>
    </div>
</div>
