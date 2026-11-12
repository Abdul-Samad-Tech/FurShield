@extends('layouts.app')
@section('title', $shelter->shelter_name ?? $shelter->name)
@section('content')
<section class="mx-auto max-w-4xl px-4 pb-20 pt-28 sm:px-6">
    <div class="surface overflow-hidden">
        <x-card-image type="role" :seed="$shelter->id + 2" :alt="$shelter->shelter_name ?? $shelter->name" variant="hero" />
        <div class="p-8">
            <h1 class="font-display text-4xl font-bold">{{ $shelter->shelter_name ?? $shelter->name }}</h1>
            <p class="mt-2 text-ink-mute">{{ $shelter->city }} · Contact: {{ $shelter->contact_person }} · {{ $shelter->phone }}</p>
            <p class="mt-4 text-ink-soft">{{ $shelter->address }}</p>
            <div class="mt-2 font-semibold text-tide">★ {{ $average ?: 'New' }}</div>
            <a href="{{ route('adopt.index') }}" class="btn-primary mt-6 inline-flex">Browse adoptable pets</a>
        </div>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2">
        @foreach ($listings as $listing)
            <a href="{{ route('adopt.show', $listing) }}" class="surface overflow-hidden">
                <x-card-image :src="$listing->photo_path" type="adoption" :seed="$listing->id" :alt="$listing->pet_name" variant="card" />
                <div class="p-4 font-display text-xl font-semibold">{{ $listing->pet_name }}</div>
            </a>
        @endforeach
    </div>

    @if ($canRate)
        <form method="POST" action="{{ route('feedback.user', $shelter) }}" class="surface mt-8 space-y-3 p-6">
            @csrf
            <h2 class="font-display text-2xl font-semibold">Rate this shelter</h2>
            <input type="number" name="rating" min="1" max="5" class="field" required>
            <textarea name="comment" rows="3" class="field" placeholder="Comment"></textarea>
            <button class="btn-primary">Submit rating</button>
        </form>
    @endif

    <div class="mt-8 space-y-3">
        @foreach ($feedbacks as $feedback)
            <div class="surface p-5">
                <div class="font-medium">{{ $feedback->user->name }} · ★ {{ $feedback->rating }}</div>
                <p class="mt-2 text-ink-mute">{{ $feedback->comment }}</p>
            </div>
        @endforeach
    </div>
</section>
@endsection
