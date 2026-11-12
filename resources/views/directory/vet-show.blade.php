@extends('layouts.app')
@section('title', $vet->name)
@section('content')
<section class="mx-auto max-w-4xl px-4 pb-20 pt-28 sm:px-6">
    <div class="surface overflow-hidden">
        <x-card-image type="role" :seed="$vet->id" :alt="$vet->name" variant="hero" />
        <div class="p-8">
            <h1 class="font-display text-4xl font-bold">{{ $vet->name }}</h1>
            <p class="mt-2 text-ink-mute">{{ $vet->specialization ?? 'General practice' }} · {{ $vet->city }} · {{ $vet->experience_years ?? 0 }} years</p>
            <p class="mt-4 text-ink-soft">{{ $vet->bio }}</p>
            <div class="mt-4 text-sm">Available: {{ $availability !== '' ? $availability : 'Contact the clinic for times' }}</div>
            <div class="mt-2 font-semibold text-tide">★ {{ $average ?: 'New' }}</div>
            @auth
                @if (auth()->user()->isOwner())
                    <a href="{{ route('owner.appointments.create', ['vet' => $vet->id]) }}" class="btn-primary mt-6 inline-flex">Book appointment</a>
                @endif
            @endauth
        </div>
    </div>

    @if ($canRate)
        <form method="POST" action="{{ route('feedback.user', $vet) }}" class="surface mt-8 space-y-3 p-6">
            @csrf
            <h2 class="font-display text-2xl font-semibold">Rate this veterinarian</h2>
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
