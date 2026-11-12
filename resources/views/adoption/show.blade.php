@extends('layouts.app')
@section('title', $listing->pet_name)
@section('content')
<section class="mx-auto max-w-5xl px-4 pb-20 pt-28 sm:px-6">
    <div class="grid gap-8 lg:grid-cols-2">
        <div class="surface overflow-hidden" data-reveal="zoom">
            <x-card-image :src="$listing->photo_path" type="adoption" :seed="$listing->id" :alt="$listing->pet_name" variant="square" />
        </div>
        <div data-reveal="left">
            <h1 class="font-display text-5xl font-bold">{{ $listing->pet_name }}</h1>
            <p class="mt-3 text-ink-mute">{{ $listing->species }} · {{ $listing->breed }} · Age {{ $listing->age ?? '—' }} · {{ $listing->gender }}</p>
            <p class="mt-2 text-sm font-medium text-tide">{{ $listing->health_status }}</p>
            <p class="mt-6 text-lg text-ink-soft">{{ $listing->description }}</p>
            <div class="mt-6 rounded-2xl bg-mist p-4 text-sm">
                Shelter: {{ $listing->shelter->shelter_name ?? $listing->shelter->name }} · {{ $listing->shelter->city }}
            </div>
            @auth
                @if (auth()->user()->isOwner())
                    <form method="POST" action="{{ route('adopt.interest', $listing) }}" class="surface mt-8 space-y-3 p-5">
                        @csrf
                        <h3 class="font-display text-xl font-semibold">Express interest</h3>
                        <textarea name="message" rows="4" class="field" placeholder="Tell the shelter about your home"></textarea>
                        <button class="btn-primary">Send interest</button>
                    </form>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn-primary mt-8 inline-flex">Login to apply</a>
            @endauth
        </div>
    </div>
</section>
@endsection
