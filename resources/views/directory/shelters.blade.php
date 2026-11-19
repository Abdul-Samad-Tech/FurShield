@extends('layouts.app')
@section('title', 'Shelters')
@section('content')
<x-public.page-hero
    title="Animal shelters"
    copy="Browse shelters, rate them, and discover adoptable pets."
    image="images/catalog-two-dogs.jpg"
    alt="A corgi and a small terrier running on a dirt path"
/>
<section class="mx-auto max-w-7xl px-4 pb-20 pt-8 sm:px-6 lg:px-8">
    <form method="GET" class="surface mt-8 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3">
        <input name="q" value="{{ request('q') }}" placeholder="Search shelter" class="field">
        <input name="city" value="{{ request('city') }}" placeholder="City" class="field">
        <button class="btn-primary">Search</button>
    </form>
    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($shelters as $i => $shelter)
            <a href="{{ route('shelters.show', $shelter) }}" data-reveal class="surface overflow-hidden transition hover:-translate-y-1">
                <x-card-image type="role" :seed="$shelter->id + 2" :alt="$shelter->shelter_name ?? $shelter->name" variant="card" />
                <div class="p-5">
                    <h2 class="font-display text-2xl font-semibold">{{ $shelter->shelter_name ?? $shelter->name }}</h2>
                    <p class="mt-1 text-sm text-ink-mute">{{ $shelter->city }} · {{ $shelter->contact_person }}</p>
                </div>
            </a>
        @endforeach
    </div>
    <div class="mt-8">{{ $shelters->links() }}</div>
</section>
@endsection
