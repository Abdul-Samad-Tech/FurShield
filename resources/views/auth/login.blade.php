@extends('layouts.app')

@section('title', 'Login')

@section('content')
<section class="mx-auto max-w-xl px-4 pb-20 pt-28 sm:px-6">
    <div class="surface p-8" data-reveal>
        <h1 class="font-display text-4xl font-bold">Welcome back</h1>
        <p class="mt-2 text-ink-mute">Sign in to your FurShield dashboard.</p>

        <x-auth-session-status class="mb-4 mt-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="field mt-1" required autofocus>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <label class="text-sm font-medium">Password</label>
                <input type="password" name="password" class="field mt-1" required>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-ink-mute">
                <input type="checkbox" name="remember" class="rounded border-mist-deep text-tide focus:ring-tide">
                Remember me
            </label>
            <button class="btn-primary w-full">Log in</button>
            <p class="text-center text-sm text-ink-mute">New here? <a href="{{ route('register') }}" class="text-tide">Create an account</a></p>
        </form>

        @include('auth.partials.google')

        <div class="mt-8">
            <h2 class="font-display text-lg font-semibold text-text">Sign in as a role</h2>
            <p class="mt-fs-3xs text-sm text-text-muted">Each demo account uses the password password.</p>
            <div class="mt-fs-sm grid grid-cols-1 gap-fs-xs sm:grid-cols-2">
                @foreach ($demoRoles as $demoRole)
                    <form method="POST" action="{{ route('login') }}" class="h-full" x-data="{ sending: false }" x-on:submit.prevent="if (sending) return; sending = true; $nextTick(() => $el.submit())">
                        @csrf
                        <input type="hidden" name="email" value="{{ $demoRole['email'] }}">
                        <input type="hidden" name="password" value="password">
                        <button
                            type="submit"
                            class="flex h-full min-h-[4.75rem] w-full items-center gap-fs-sm rounded-fs-md border border-border bg-surface-raised px-fs-sm py-fs-xs text-left text-text shadow-fs-xs hover:border-border-strong hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus focus-visible:ring-offset-2 focus-visible:ring-offset-surface motion-safe:transition motion-safe:duration-fs-fast motion-safe:ease-fs-standard"
                            x-bind:aria-busy="sending"
                        >
                            <span class="grid size-11 shrink-0 place-items-center rounded-fs-sm bg-success-soft text-text" aria-hidden="true">
                                <x-ui.icon :name="$demoRole['icon']" />
                            </span>
                            <span class="min-w-0">
                                <span class="sr-only">Sign in as </span>
                                <span class="block font-medium" x-show="!sending">{{ $demoRole['label'] }}</span>
                                <span class="block font-medium" x-show="sending" x-cloak>Signing in…</span>
                                <span class="block break-all text-xs leading-5 text-text-muted" x-show="!sending">{{ $demoRole['email'] }}</span>
                            </span>
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
