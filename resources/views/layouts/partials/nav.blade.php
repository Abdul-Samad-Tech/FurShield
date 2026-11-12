@php
    $isHome = request()->routeIs('home');
    $publicLinks = [
        ['label' => 'Shop', 'route' => 'products.index', 'active' => 'products.*'],
        ['label' => 'Care Tips', 'route' => 'care.index', 'active' => 'care.*'],
        ['label' => 'Adopt', 'route' => 'adopt.index', 'active' => 'adopt.*'],
        ['label' => 'Vets', 'route' => 'vets.index', 'active' => 'vets.*'],
        ['label' => 'Shelters', 'route' => 'shelters.index', 'active' => 'shelters.*'],
        ['label' => 'About', 'route' => 'about', 'active' => 'about'],
        ['label' => 'Contact', 'route' => 'contact', 'active' => 'contact'],
    ];
@endphp
<header
    x-data="{ open: false, scrolled: {{ $isHome ? 'false' : 'true' }} }"
    x-init="{{ $isHome ? "window.addEventListener('scroll', () => scrolled = window.scrollY > 24)" : '' }}"
    class="fixed inset-x-0 top-0 z-40 transition duration-300"
    :class="scrolled ? 'bg-mist/90 shadow-soft backdrop-blur-xl' : 'bg-gradient-to-b from-[#07181C]/80 to-transparent'"
>
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="group flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-tide text-lg font-bold text-white shadow-glow transition group-hover:scale-105">FS</span>
            <span class="font-display text-xl font-bold tracking-tight" :class="scrolled ? 'text-ink' : 'text-white'">FurShield</span>
        </a>

        <nav class="hidden items-center gap-7 lg:flex" aria-label="Primary">
            @foreach ($publicLinks as $link)
                <a href="{{ route($link['route']) }}" class="nav-link" @if (request()->routeIs($link['active'])) aria-current="page" @endif :class="!scrolled && 'text-white hover:text-white [text-shadow:0_1px_2px_rgba(7,24,28,0.75)]'">{{ $link['label'] }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-1">
            <button type="button" class="grid size-11 place-items-center rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" :class="scrolled ? 'text-ink' : 'text-white'" @click="$store.palette.show()" :aria-expanded="$store.palette.open ? 'true' : 'false'" aria-controls="command-palette" aria-label="Search">
                <x-ui.icon name="search" />
            </button>
            @auth
                @if (auth()->user()->isOwner())
                    <button type="button" class="relative grid size-11 place-items-center rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" :class="scrolled ? 'text-ink' : 'text-white'" @click="$store.cart.show()" :aria-expanded="$store.cart.open ? 'true' : 'false'" aria-controls="mini-cart" aria-label="Open cart">
                        <x-ui.icon name="shopping-cart" />
                        <span data-cart-badge @class([
                            'absolute right-1 top-1 grid min-w-4 place-items-center rounded-full bg-success px-1 text-[0.65rem] leading-4 text-text-inverse',
                            'hidden' => ($shellBadges['cart'] ?? 0) < 1,
                        ])>{{ $shellBadges['cart'] ?? 0 }}</span>
                    </button>
                @endif
            @endauth
            @auth
                @include('layouts.partials.alerts', ['variant' => 'public'])
            @endauth
            <div class="hidden items-center gap-3 lg:flex">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-ghost !py-2 !text-sm" @if (request()->routeIs('dashboard', 'owner.dashboard', 'vet.dashboard', 'shelter.dashboard', 'admin.dashboard')) aria-current="page" @endif>Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm font-medium" :class="scrolled ? 'text-ink-mute hover:text-tide' : 'text-white/85 hover:text-white'">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-sm font-semibold" :class="scrolled ? 'text-ink-soft' : 'text-white [text-shadow:0_1px_2px_rgba(7,24,28,0.75)]'">Login</a>
                <a href="{{ route('register') }}" class="btn-ghost !py-2.5 !text-sm">Join FurShield</a>
            @endauth
            </div>

            <button type="button" @click="open = !open" class="rounded-xl border p-2 backdrop-blur lg:hidden" :class="scrolled ? 'border-tide/20 bg-white text-ink' : 'border-white/20 bg-white/10 text-white'" :aria-expanded="open ? 'true' : 'false'" :aria-label="open ? 'Close menu' : 'Open menu'">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
    </div>

    <div x-show="open" x-transition class="border-t border-tide/10 bg-mist/95 px-4 py-4 backdrop-blur lg:hidden">
        <nav class="flex flex-col gap-3" aria-label="Primary">
            @foreach ($publicLinks as $link)
                <a href="{{ route($link['route']) }}" class="nav-link" @if (request()->routeIs($link['active'])) aria-current="page" @endif>{{ $link['label'] }}</a>
            @endforeach
            @auth
                <a href="{{ route('notifications.index') }}" class="nav-link" @if (request()->routeIs('notifications.*')) aria-current="page" @endif>Notifications</a>
                <a href="{{ route('dashboard') }}" class="btn-primary" @if (request()->routeIs('dashboard', 'owner.dashboard', 'vet.dashboard', 'shelter.dashboard', 'admin.dashboard')) aria-current="page" @endif>Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn-ghost">Login</a>
                <a href="{{ route('register') }}" class="btn-primary">Join FurShield</a>
            @endauth
        </nav>
    </div>
</header>
