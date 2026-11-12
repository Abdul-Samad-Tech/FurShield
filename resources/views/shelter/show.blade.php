@extends('layouts.dashboard')
@section('title', $listing->pet_name)
@section('content')
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="font-display text-4xl font-bold">{{ $listing->pet_name }}</h1>
        <p class="mt-2 text-ink-mute">{{ $listing->species }} · {{ $listing->breed }} · {{ $listing->health_status }}</p>
    </div>
    <form method="POST" action="{{ route('shelter.listings.status', $listing) }}" class="flex gap-2">
        @csrf @method('PATCH')
        <select name="status" class="field">
            @foreach (['available','pending','adopted'] as $status)
                <option value="{{ $status }}" @selected($listing->status->value === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="btn-ghost">Update status</button>
    </form>
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <section class="surface p-6">
        <h2 class="font-display text-2xl font-semibold">Care logs</h2>
        <div class="mt-4 space-y-3">
            @forelse ($listing->careLogs as $log)
                <div class="rounded-2xl bg-mist/70 p-4 text-sm">
                    <div class="font-medium">{{ $log->care_type }} · {{ $log->logged_at->format('M d, Y g:i A') }}</div>
                    <div class="text-ink-mute">{{ $log->notes }}</div>
                </div>
            @empty
                <p class="text-ink-mute">No care logs yet.</p>
            @endforelse
        </div>
        <form method="POST" action="{{ route('shelter.listings.care', $listing) }}" class="mt-5 space-y-3 border-t border-mist-deep pt-5">
            @csrf
            <input name="care_type" class="field" placeholder="feeding / grooming / medical" required>
            <textarea name="notes" rows="2" class="field" placeholder="Notes"></textarea>
            <button class="btn-primary w-full">Add care log</button>
        </form>
    </section>

    <section class="surface p-6">
        <h2 class="font-display text-2xl font-semibold">Adopter interests</h2>
        <div class="mt-4 space-y-4">
            @forelse ($listing->interests as $interest)
                <div class="rounded-2xl border border-mist-deep p-4">
                    <div class="font-medium">{{ $interest->owner->name }} · {{ $interest->status }}</div>
                    <p class="mt-1 text-sm text-ink-mute">{{ $interest->message }}</p>
                    <form method="POST" action="{{ route('shelter.interests.update', $interest) }}" class="mt-3 space-y-2">
                        @csrf @method('PATCH')
                        <select name="status" class="field">
                            @foreach (['pending','accepted','rejected','finalized'] as $status)
                                <option value="{{ $status }}" @selected($interest->status === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <textarea name="shelter_response" rows="2" class="field" placeholder="Response to adopter">{{ $interest->shelter_response }}</textarea>
                        <button class="btn-ghost w-full">Respond</button>
                    </form>
                </div>
            @empty
                <p class="text-ink-mute">No interests yet.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
