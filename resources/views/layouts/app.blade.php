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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Syne:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <noscript><style>[data-reveal]{opacity:1!important;transform:none!important}</style></noscript>
    @stack('head')
</head>
<body @class(['min-h-screen', 'bg-page-wash text-text' => $shell === 'dashboard', 'overflow-x-hidden' => $shell !== 'dashboard'])>
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[80] focus:rounded-fs-md focus:bg-success focus:px-fs-sm focus:py-fs-xs focus:text-sm focus:font-medium focus:text-text-inverse focus:outline-none focus:ring-2 focus:ring-focus">Skip to content</a>

    <x-ui.toast :message="session('success')" :offset="$shell === 'dashboard' ? 'top-20' : 'top-24'" />

    @if ($shell === 'dashboard')
        @include('layouts.partials.dashboard.shell')
    @else
        @include('layouts.partials.nav')
        <main id="main" tabindex="-1">
            <x-ui.error-summary class="mx-auto mt-24 max-w-7xl px-4 sm:px-6 lg:px-8" />
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
</body>
</html>
