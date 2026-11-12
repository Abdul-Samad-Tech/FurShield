@extends('layouts.dashboard')
@section('title', 'Shelter Listings')
@section('content')
<div class="flex items-center justify-between gap-3">
    <h1 class="font-display text-4xl font-bold">Adoption listings</h1>
    <a href="{{ route('shelter.listings.create') }}" class="btn-primary">Add pet</a>
</div>
<div class="mt-8 space-y-4">
    @foreach ($listings as $listing)
        <a href="{{ route('shelter.listings.show', $listing) }}" class="surface flex items-center gap-4 overflow-hidden p-4">
            <x-card-image :src="$listing->photo_path" type="adoption" :seed="$listing->id" :alt="$listing->pet_name" variant="thumb" />
            <div class="min-w-0 flex-1">
                <div class="font-display text-xl font-semibold">{{ $listing->pet_name }}</div>
                <div class="text-sm text-ink-mute">{{ $listing->species }} · {{ $listing->breed }} · {{ $listing->status->label() }} · {{ $listing->interests_count }} interests</div>
            </div>
        </a>
    @endforeach
</div>
<div class="mt-6">{{ $listings->links() }}</div>
@endsection
