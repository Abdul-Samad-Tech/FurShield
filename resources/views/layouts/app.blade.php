@php
    $shell = trim($__env->yieldContent('shell')) ?: 'public';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if ($shell === 'dashboard')
        <title>@yield('title', 'Dashboard') — FurShield</title>
    @else
        <title>@yield('title', 'FurShield') — Every Paw/Wing Deserves a Shield of Love</title>
    @endif
    <script>
        try {
            if (localStorage.getItem('furshield-theme') !== 'light') {
                document.documentElement.classList.add('dark');
            }
            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.documentElement.classList.add('fs-motion');
            }
        } catch (e) {}
    </script>
    <link rel="icon" href="{{ asset('images/brand-shield.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@1,800&family=Outfit:wght@400;500;600;700&family=Syne:wght@600;700;800&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@1,800&family=Outfit:wght@400;500;600;700&family=Syne:wght@600;700;800&display=swap"></noscript>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <noscript><style>[data-reveal]{opacity:1!important;transform:none!important}</style></noscript>
    @stack('head')
</head>
<body @class(['min-h-screen bg-page-wash text-text', 'overflow-x-hidden' => $shell !== 'dashboard'])>
    <div id="fs-boot" class="fixed inset-0 z-[70] bg-page-wash px-3 pt-3 sm:px-4" aria-hidden="true">
        <div class="mx-auto h-16 max-w-7xl animate-pulse rounded-fs-lg bg-surface-sunken motion-reduce:animate-none"></div>
        <div class="mx-auto mt-6 h-64 w-[calc(100%-1rem)] max-w-[90rem] animate-pulse rounded-fs-lg bg-surface-sunken motion-reduce:animate-none sm:w-[calc(100%-1.5rem)]"></div>
        <div class="mx-auto mt-6 grid w-[calc(100%-1rem)] max-w-[90rem] gap-4 sm:w-[calc(100%-1.5rem)] sm:grid-cols-3">
            <div class="h-40 animate-pulse rounded-fs-lg bg-surface-sunken motion-reduce:animate-none"></div>
            <div class="h-40 animate-pulse rounded-fs-lg bg-surface-sunken motion-reduce:animate-none"></div>
            <div class="hidden h-40 animate-pulse rounded-fs-lg bg-surface-sunken motion-reduce:animate-none sm:block"></div>
        </div>
    </div>
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[80] focus:rounded-fs-md focus:bg-success focus:px-fs-sm focus:py-fs-xs focus:text-sm focus:font-medium focus:text-text-inverse focus:outline-none focus:ring-2 focus:ring-focus">Skip to content</a>

    <x-ui.toast :message="session('success')" :offset="$shell === 'dashboard' ? 'top-20' : 'top-28'" />

    @if ($shell === 'dashboard')
        @include('layouts.partials.dashboard.shell')
    @else
        @include('layouts.partials.nav')
        <main id="main" tabindex="-1">
            <x-ui.error-summary class="mx-auto mt-28 max-w-7xl px-4 sm:px-6 lg:px-8" />
            @if (trim($__env->yieldContent('content')) !== '')
                @yield('content')
            @else
                @isset($header)
                    <div class="mx-auto max-w-7xl px-4 pt-28 sm:px-6 lg:px-8">{{ $header }}</div>
                @endisset
                @isset($slot)
                    {{ $slot }}
                @endisset
            @endif
        </main>
        @include('layouts.partials.footer')
    @endif

    @include('layouts.partials.command-palette')
    @include('layouts.partials.mini-cart')

    @auth
        @if (auth()->user()->isOwner() && config('assistant.enabled') === true)
            @include('layouts.partials.assistant', [
                'shell' => $shell,
                'emergencyTel' => \App\Support\Furshield::emergencyTel(),
            ])
        @endif
    @endauth

    @stack('scripts')
    <script>
        (function () {
            var boot = document.getElementById('fs-boot');
            var loader = document.getElementById('fs-nav-loader');
            var started = Date.now();
            var hide = function () { if (boot && boot.parentNode) boot.remove(); };
            var loaderDone = false;
            var finishLoader = function () {
                if (!loader || loaderDone) return;
                loaderDone = true;
                var wait = Math.max(0, 420 - (Date.now() - started));
                setTimeout(function () {
                    loader.classList.add('is-done');
                    setTimeout(function () { if (loader.parentNode) loader.remove(); }, 240);
                }, wait);
            };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () { requestAnimationFrame(hide); });
            } else {
                requestAnimationFrame(hide);
            }
            if (document.readyState === 'complete') {
                finishLoader();
            } else {
                window.addEventListener('load', finishLoader);
            }
            setTimeout(function () {
                hide();
                finishLoader();
                document.documentElement.classList.remove('fs-motion');
            }, 1600);
        })();
    </script>
</body>
</html>
