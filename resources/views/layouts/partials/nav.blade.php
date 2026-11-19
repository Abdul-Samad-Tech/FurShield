@php
    $publicLinks = [
        ['label' => 'Shop', 'route' => 'products.index', 'active' => 'products.*', 'icon' => 'shopping-bag', 'group' => 'Explore'],
        ['label' => 'Care Tips', 'route' => 'care.index', 'active' => 'care.*', 'icon' => 'book-open', 'group' => 'Explore'],
        ['label' => 'Adopt', 'route' => 'adopt.index', 'active' => 'adopt.*', 'icon' => 'heart', 'group' => 'Explore'],
        ['label' => 'Vets', 'route' => 'vets.index', 'active' => 'vets.*', 'icon' => 'stethoscope', 'group' => 'Care'],
        ['label' => 'Shelters', 'route' => 'shelters.index', 'active' => 'shelters.*', 'icon' => 'house', 'group' => 'Care'],
        ['label' => 'About', 'route' => 'about', 'active' => 'about', 'icon' => 'info', 'group' => 'FurShield'],
        ['label' => 'Contact', 'route' => 'contact', 'active' => 'contact', 'icon' => 'message-circle', 'group' => 'FurShield'],
    ];
    $menuGroups = ['Explore', 'Care', 'FurShield'];
@endphp
<div
    x-data="{ open: false, shop: false, account: false }"
    x-init="$watch('open', (value) => { if (value) { $nextTick(() => $refs.drawerClose?.focus()) } else { $refs.menuButton?.focus() } })"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
    @keydown.escape.window="open = false; shop = false; account = false"
>
<header class="fixed inset-x-3 top-3 z-40 mx-auto max-w-7xl text-text sm:inset-x-4 lg:inset-x-6">
    <div class="relative rounded-fs-lg border border-border bg-surface-raised shadow-fs-lg">
        <div id="fs-nav-loader" class="pointer-events-none absolute inset-x-0 top-0 z-20 h-0.5 overflow-hidden rounded-t-fs-lg" aria-hidden="true">
            <span class="fs-nav-loader-bar block h-full bg-tide"></span>
        </div>
        <div class="flex h-16 items-center justify-between gap-2 px-2 sm:px-3">
        <button type="button" x-ref="menuButton" @click="open = !open" class="grid size-11 shrink-0 cursor-pointer place-items-center rounded-full text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus lg:hidden" :aria-expanded="open ? 'true' : 'false'" aria-controls="mobile-nav" :aria-label="open ? 'Close menu' : 'Open menu'">
            <svg x-show="!open" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            <svg x-show="open" x-cloak class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
        </button>

        <a href="{{ route('home') }}" class="absolute left-1/2 flex h-16 -translate-x-1/2 items-center gap-2 lg:static lg:translate-x-0">
            <img src="{{ asset('images/brand-shield.png') }}" alt="" width="40" height="44" class="size-10 shrink-0 object-contain">
            <span class="fs-brand-name font-display text-lg font-bold tracking-tight">FurShield</span>
        </a>

        <nav class="hidden min-w-0 flex-1 items-center justify-center gap-0.5 lg:flex" aria-label="Primary">
            @foreach ($publicLinks as $link)
                @if ($link['route'] === 'products.index')
                    <div class="relative" @mouseenter="shop = true" @mouseleave="shop = false">
                        <button type="button" class="inline-flex h-10 cursor-pointer items-center gap-1 rounded-fs-sm px-2.5 text-sm font-medium text-text motion-safe:transition-colors motion-safe:duration-fs-fast hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus xl:px-3" @click="shop = !shop" :aria-expanded="shop ? 'true' : 'false'" aria-controls="shop-menu">
                            Shop
                            <x-ui.icon name="chevron-down" size="sm" class="size-3 opacity-70" />
                        </button>
                        <div id="shop-menu" x-show="shop" x-cloak class="absolute left-1/2 top-[calc(100%+0.65rem)] z-50 w-52 -translate-x-1/2 rounded-fs-md border border-border bg-surface-raised p-1.5 text-sm text-text shadow-fs-md">
                            <a href="{{ route('products.index') }}" class="block cursor-pointer rounded-fs-sm px-3 py-2 hover:bg-surface-sunken" @click="shop = false">All products</a>
                            @foreach (\App\Enums\ProductCategory::cases() as $category)
                                <a href="{{ route('products.index', ['category' => $category->value]) }}" class="block cursor-pointer rounded-fs-sm px-3 py-2 hover:bg-surface-sunken" @click="shop = false">{{ $category->label() }}</a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ route($link['route']) }}" @class([
                        'inline-flex h-10 items-center rounded-fs-sm px-2.5 text-sm font-medium motion-safe:transition-colors motion-safe:duration-fs-fast focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus xl:px-3',
                        'bg-success-soft text-text' => request()->routeIs($link['active']),
                        'text-text hover:bg-surface-sunken' => ! request()->routeIs($link['active']),
                    ]) @if (request()->routeIs($link['active'])) aria-current="page" @endif>{{ $link['label'] }}</a>
                @endif
            @endforeach
        </nav>

        <div class="flex h-16 items-center">
            <div class="hidden items-center gap-1 lg:flex">
                @guest
                    <a href="{{ route('login') }}" class="inline-flex h-10 items-center rounded-fs-sm px-3 text-sm font-medium text-text-muted motion-safe:transition-colors motion-safe:duration-fs-fast hover:bg-surface-sunken hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">Log in</a>
                    <a href="{{ route('register') }}" class="inline-flex h-10 items-center rounded-full bg-tide px-4 text-sm font-semibold text-text-inverse motion-safe:transition-colors motion-safe:duration-fs-fast hover:bg-tide-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">Join</a>
                @endguest
            </div>
            @include('layouts.partials.theme-toggle', ['compact' => true])
            <button type="button" class="grid size-11 cursor-pointer place-items-center rounded-full text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="$store.palette.show()" :aria-expanded="$store.palette.open ? 'true' : 'false'" aria-controls="command-palette" aria-label="Search">
                <x-ui.icon name="search" />
            </button>
            @auth
                <div class="relative" @click.outside="account = false">
                    <button type="button" class="grid size-11 cursor-pointer place-items-center rounded-full text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="account = !account; shop = false" :aria-expanded="account ? 'true' : 'false'" aria-haspopup="menu" aria-controls="account-menu" aria-label="Account menu">
                        <x-ui.icon name="user" />
                    </button>
                    <div id="account-menu" x-show="account" x-cloak role="menu" class="absolute right-0 top-[calc(100%+0.65rem)] z-50 w-52 rounded-fs-md border border-border bg-surface-raised p-1.5 text-sm text-text shadow-fs-md">
                        <a role="menuitem" href="{{ route('dashboard') }}" class="block cursor-pointer rounded-fs-sm px-3 py-2 hover:bg-surface-sunken" @click="account = false" @if (request()->routeIs('dashboard', 'owner.dashboard', 'vet.dashboard', 'shelter.dashboard', 'admin.dashboard')) aria-current="page" @endif>Dashboard</a>
                        <a role="menuitem" href="{{ route('profile.edit') }}" class="block cursor-pointer rounded-fs-sm px-3 py-2 hover:bg-surface-sunken" @click="account = false" @if (request()->routeIs('profile.edit')) aria-current="page" @endif>Account</a>
                        @if (auth()->user()->isVet())
                            <a role="menuitem" href="{{ route('vet.profile') }}" class="block cursor-pointer rounded-fs-sm px-3 py-2 hover:bg-surface-sunken" @click="account = false" @if (request()->routeIs('vet.profile')) aria-current="page" @endif>Clinic profile</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" role="menuitem" class="block w-full cursor-pointer rounded-fs-sm px-3 py-2 text-left hover:bg-surface-sunken">Log out</button>
                        </form>
                    </div>
                </div>
                @if (auth()->user()->isOwner())
                    <button type="button" class="relative grid size-11 cursor-pointer place-items-center rounded-full text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="$store.cart.show()" :aria-expanded="$store.cart.open ? 'true' : 'false'" aria-controls="mini-cart" aria-label="Open cart">
                        <x-ui.icon name="shopping-bag" />
                        <span data-cart-badge @class([
                            'absolute right-0 top-1.5 grid min-w-4 place-items-center rounded-full bg-[#0071e3] px-1 text-[9px] leading-4 text-white',
                            'hidden' => ($shellBadges['cart'] ?? 0) < 1,
                        ])>{{ $shellBadges['cart'] ?? 0 }}</span>
                    </button>
                @endif
                @include('layouts.partials.alerts', ['variant' => 'public'])
            @endauth
        </div>
        </div>
    </div>
</header>

    <div id="mobile-nav" class="pointer-events-none fixed inset-0 z-50 lg:hidden">
        <button type="button" class="pointer-events-auto absolute inset-0 cursor-pointer bg-[#021114]/50 backdrop-blur-[2px]" x-show="open" x-cloak @click="open = false" aria-label="Close menu" x-transition:enter="motion-safe:transition motion-safe:duration-fs-base motion-safe:ease-fs-enter" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="motion-safe:transition motion-safe:duration-fs-fast motion-safe:ease-fs-exit" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></button>
        <aside
            class="pointer-events-none absolute inset-y-0 left-0 flex w-full max-w-[21.75rem]"
            x-show="open"
            x-cloak
            x-transition:enter="motion-safe:transition motion-safe:duration-fs-base motion-safe:ease-fs-enter"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="motion-safe:transition motion-safe:duration-fs-fast motion-safe:ease-fs-exit"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
        >
            <div class="pointer-events-auto m-3 flex h-[calc(100dvh-1.5rem)] max-h-[calc(100dvh-1.5rem)] min-h-0 min-w-0 flex-1 flex-col overflow-hidden rounded-fs-lg border border-border bg-surface-raised text-text shadow-fs-lg" role="dialog" aria-modal="true" aria-label="Menu">
                <div class="h-1 shrink-0 bg-tide" aria-hidden="true"></div>
                <div class="min-h-0 flex-1 overflow-y-auto overscroll-y-contain" style="touch-action: pan-y">
                <div class="sticky top-0 z-10 flex items-center gap-3 bg-surface-raised px-4 pb-4 pt-4">
                    <img src="{{ asset('images/brand-shield.png') }}" alt="" width="40" height="44" class="size-10 shrink-0 object-contain">
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-display text-lg font-semibold tracking-tight">FurShield</p>
                        <p class="truncate text-sm text-text-muted">Healthy pets. Happier lives.</p>
                    </div>
                    <button type="button" x-ref="drawerClose" class="grid size-11 shrink-0 cursor-pointer place-items-center rounded-full text-text-muted motion-safe:transition-colors motion-safe:duration-fs-fast hover:bg-surface-sunken hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="open = false" aria-label="Close menu">
                        <x-ui.icon name="x" />
                    </button>
                </div>
                <nav class="flex flex-col gap-1 px-3 pb-3" aria-label="Mobile">
                    @foreach ($menuGroups as $group)
                        <p class="px-2 pb-1 pt-3 text-xs font-semibold uppercase tracking-[0.14em] text-text-muted">{{ $group }}</p>
                        @foreach ($publicLinks as $link)
                            @continue($link['group'] !== $group)
                            @php $current = request()->routeIs($link['active']); @endphp
                            <a
                                href="{{ route($link['route']) }}"
                                @class([
                                    'group flex min-h-12 cursor-pointer items-center gap-3 rounded-fs-md px-2 text-base font-medium motion-safe:transition-colors motion-safe:duration-fs-fast focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus',
                                    'bg-success-soft text-text' => $current,
                                    'text-text hover:bg-surface-sunken' => ! $current,
                                ])
                                @click="open = false"
                                @if ($current) aria-current="page" @endif
                            >
                                <span @class([
                                    'grid size-9 shrink-0 place-items-center rounded-fs-sm text-success',
                                    'bg-surface-raised' => $current,
                                    'bg-surface-sunken' => ! $current,
                                ])>
                                    <x-ui.icon :name="$link['icon']" size="sm" />
                                </span>
                                <span class="min-w-0 flex-1 truncate">{{ $link['label'] }}</span>
                                <x-ui.icon name="chevron-right" size="sm" @class([
                                    'shrink-0 text-text-muted motion-safe:transition-opacity motion-safe:duration-fs-fast',
                                    'opacity-100' => $current,
                                    'opacity-0 group-hover:opacity-100 group-focus-visible:opacity-100' => ! $current,
                                ]) />
                            </a>
                        @endforeach
                    @endforeach
                </nav>
                @guest
                    <div class="shrink-0 border-t border-border p-4">
                        <a href="{{ route('register') }}" class="btn-primary w-full cursor-pointer" @click="open = false">Join FurShield</a>
                        <a href="{{ route('login') }}" class="mt-2 flex min-h-11 cursor-pointer items-center justify-center rounded-fs-md text-sm font-medium text-text-muted motion-safe:transition-colors motion-safe:duration-fs-fast hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="open = false">Log in</a>
                    </div>
                @endguest
                </div>
            </div>
        </aside>
    </div>
</div>
