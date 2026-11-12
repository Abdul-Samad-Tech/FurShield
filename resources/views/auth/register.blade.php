@extends('layouts.app')

@section('title', 'Register')

@section('content')
<section class="mx-auto max-w-xl px-4 pb-20 pt-28 sm:px-6">
    <div class="surface p-8" data-reveal x-data="{ role: '{{ old('role', 'owner') }}' }">
        <h1 class="font-display text-4xl font-bold">Join FurShield</h1>
        <p class="mt-2 text-ink-mute">Register as an owner, veterinarian, or shelter.</p>

        <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium">I am a</label>
                <div class="mt-2 grid grid-cols-3 gap-2">
                    @foreach ($roles as $roleOption)
                        <label class="cursor-pointer rounded-2xl border px-3 py-3 text-center text-sm font-medium transition"
                            :class="role === '{{ $roleOption->value }}' ? 'border-tide bg-tide text-white' : 'border-mist-deep bg-white'">
                            <input type="radio" class="hidden" name="role" value="{{ $roleOption->value }}" x-model="role" @checked(old('role', 'owner') === $roleOption->value)>
                            {{ $roleOption->label() }}
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>

            <div>
                <label class="text-sm font-medium">Full name</label>
                <input name="name" value="{{ old('name') }}" class="field mt-1" required>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div x-show="role === 'shelter'" x-cloak class="space-y-4">
                <div>
                    <label class="text-sm font-medium">Shelter name</label>
                    <input name="shelter_name" value="{{ old('shelter_name') }}" class="field mt-1">
                </div>
                <div>
                    <label class="text-sm font-medium">Contact person</label>
                    <input name="contact_person" value="{{ old('contact_person') }}" class="field mt-1">
                </div>
            </div>

            <div>
                <label class="text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="field mt-1" required>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <label class="text-sm font-medium">Phone</label>
                <input name="phone" value="{{ old('phone') }}" class="field mt-1" required>
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <div>
                <label class="text-sm font-medium">Address</label>
                <textarea name="address" rows="2" class="field mt-1" required>{{ old('address') }}</textarea>
                <x-input-error :messages="$errors->get('address')" class="mt-2" />
            </div>

            <div>
                <label class="text-sm font-medium">City</label>
                <input name="city" value="{{ old('city') }}" class="field mt-1">
            </div>

            <div>
                <label class="text-sm font-medium">Password</label>
                <input type="password" name="password" class="field mt-1" required>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <label class="text-sm font-medium">Confirm password</label>
                <input type="password" name="password_confirmation" class="field mt-1" required>
            </div>

            <button class="btn-primary w-full">Create account</button>
            <p class="text-center text-sm text-ink-mute">Already registered? <a href="{{ route('login') }}" class="text-tide">Login</a></p>
        </form>

        @include('auth.partials.google')
    </div>
</section>
@endsection
