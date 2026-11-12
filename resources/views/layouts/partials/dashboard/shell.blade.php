@php
    $tabs = collect($dashboardNav)->where('tab', true)->values();
    $initials = collect(preg_split('/\s+/u', trim(auth()->user()->name)))
        ->filter()
        ->take(2)
        ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<div class="lg:grid lg:min-h-screen lg:grid-cols-[16rem_minmax(0,1fr)]" x-data="fsShell" @keydown.escape.window="onEscape()">
    <aside class="hidden border-r border-border bg-surface-raised lg:block">
        <div class="sticky top-0 flex h-screen flex-col gap-fs-sm overflow-y-auto p-fs-sm">
            <a href="{{ route('home') }}" class="flex items-center gap-fs-sm rounded-fs-sm px-fs-sm py-fs-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">
                <span class="grid size-10 place-items-center rounded-fs-sm bg-success font-display text-sm font-bold text-text-inverse">FS</span>
                <span>
                    <span class="block font-display text-lg font-semibold text-text">FurShield</span>
                    <span class="block text-xs text-text-muted">{{ auth()->user()->role->label() }}</span>
                </span>
            </a>
            <nav aria-label="Dashboard" class="grid gap-fs-3xs">
                @foreach ($dashboardNav as $item)
                    @include('layouts.partials.dashboard.link', ['item' => $item, 'variant' => 'sidebar'])
                @endforeach
            </nav>
        </div>
    </aside>

    <div class="min-w-0 pb-24 lg:pb-0">
        <header class="sticky top-0 z-30 flex items-center gap-fs-xs border-b border-border bg-surface/90 px-fs-sm py-fs-xs backdrop-blur">
            <div class="min-w-0 flex-1">
                <p class="text-xs text-text-muted">{{ auth()->user()->role->label() }}</p>
                <p class="truncate font-display text-lg font-semibold text-text">@yield('title', 'Dashboard')</p>
            </div>
            <button type="button" class="grid size-11 place-items-center rounded-fs-sm text-text hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="$store.palette.show()" :aria-expanded="$store.palette.open ? 'true' : 'false'" aria-controls="command-palette" aria-label="Search">
                <x-ui.icon name="search" />
            </button>
            @include('layouts.partials.alerts', ['variant' => 'dashboard'])
            <div class="relative" @click.outside="menu = false">
                <button type="button" class="grid size-11 place-items-center rounded-fs-xl bg-success-soft text-sm font-semibold text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="menu = !menu" :aria-expanded="menu ? 'true' : 'false'" aria-haspopup="menu" aria-label="Account menu for {{ auth()->user()->name }}">
                    {{ $initials }}
                </button>
                <div x-show="menu" x-cloak role="menu" class="absolute right-0 z-40 mt-fs-2xs w-56 rounded-fs-md border border-border bg-surface-raised p-fs-2xs text-sm shadow-fs-md">
                    <p class="px-fs-sm py-fs-2xs text-xs text-text-muted">{{ auth()->user()->name }}</p>
                    <a role="menuitem" href="{{ route('profile.edit') }}" class="block rounded-fs-sm px-fs-sm py-fs-2xs text-text hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @if (request()->routeIs('profile.edit')) aria-current="page" @endif>Account</a>
                    @if (auth()->user()->isVet())
                        <a role="menuitem" href="{{ route('vet.profile') }}" class="block rounded-fs-sm px-fs-sm py-fs-2xs text-text hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @if (request()->routeIs('vet.profile')) aria-current="page" @endif>Clinic profile</a>
                    @endif
                    <a role="menuitem" href="{{ route('home') }}" class="block rounded-fs-sm px-fs-sm py-fs-2xs text-text hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">View site</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" role="menuitem" class="block w-full rounded-fs-sm px-fs-sm py-fs-2xs text-left text-text hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">Log out</button>
                    </form>
                </div>
            </div>
        </header>

        <main id="main" tabindex="-1" class="px-fs-sm py-fs-md sm:px-fs-md lg:px-fs-lg">
            <x-ui.error-summary />
            @yield('content')
        </main>
    </div>

    <nav aria-label="Primary" class="fixed inset-x-0 bottom-0 z-40 border-t border-border bg-surface-raised lg:hidden" style="padding-bottom: env(safe-area-inset-bottom)">
        <ul class="grid" style="grid-template-columns: repeat({{ $tabs->count() + 1 }}, minmax(0, 1fr))">
            @foreach ($tabs as $item)
                <li>@include('layouts.partials.dashboard.link', ['item' => $item, 'variant' => 'tabs'])</li>
            @endforeach
            <li>
                <button type="button" x-ref="moreTab" class="flex min-h-11 w-full flex-col items-center justify-center gap-fs-3xs px-fs-3xs text-[0.7rem] font-medium text-text-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="openDrawer()" :aria-expanded="drawer ? 'true' : 'false'" aria-controls="dashboard-drawer">
                    <x-ui.icon name="menu" size="sm" />
                    More
                </button>
            </li>
        </ul>
    </nav>

    <div x-show="drawer" x-cloak class="fixed inset-0 z-[60] lg:hidden" x-transition:enter="motion-safe:transition motion-safe:duration-fs-base motion-safe:ease-fs-enter" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="motion-safe:transition motion-safe:duration-fs-fast motion-safe:ease-fs-exit" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-text/40" @click="closeDrawer()"></div>
        <div id="dashboard-drawer" x-ref="drawerPanel" role="dialog" aria-modal="true" aria-label="Menu" tabindex="-1" class="absolute inset-y-0 left-0 flex w-[min(100%,20rem)] flex-col overflow-y-auto bg-surface-raised p-fs-sm shadow-fs-lg" @keydown.tab="trap($event, $refs.drawerPanel)">
            <div class="mb-fs-sm flex items-center justify-between">
                <p class="font-display text-lg font-semibold text-text">FurShield</p>
                <button type="button" class="grid size-11 place-items-center rounded-fs-sm text-text hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="closeDrawer()" aria-label="Close menu">
                    <x-ui.icon name="x" />
                </button>
            </div>
            <nav aria-label="Dashboard" class="grid gap-fs-3xs">
                @foreach ($dashboardNav as $item)
                    @include('layouts.partials.dashboard.link', ['item' => $item, 'variant' => 'drawer'])
                @endforeach
            </nav>
        </div>
    </div>
</div>
