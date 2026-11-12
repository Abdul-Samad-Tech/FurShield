@extends('layouts.dashboard')

@section('title', 'My Pets')


@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="font-display text-4xl font-bold">My Pets</h1>
        <p class="mt-2 text-ink-mute">Tabbed multi-pet management with galleries and health links.</p>
    </div>
    <a href="{{ route('owner.pets.create') }}" class="btn-primary">Add pet</a>
</div>

<div class="mt-8" x-data="{ tab: {{ $pets->first()->id ?? 'null' }} }">
    @if ($pets->isNotEmpty())
        <div class="flex flex-wrap gap-2">
            @foreach ($pets as $pet)
                <button type="button" @click="tab = {{ $pet->id }}" class="rounded-full px-4 py-2 text-sm font-medium transition"
                    :class="tab === {{ $pet->id }} ? 'bg-tide text-white' : 'bg-white text-ink-soft border border-mist-deep'">
                    {{ $pet->name }}
                </button>
            @endforeach
        </div>
        @foreach ($pets as $pet)
            <div x-show="tab === {{ $pet->id }}" x-transition class="surface mt-6 overflow-hidden">
                <x-card-image :src="$pet->photo_path" type="pet" :seed="$pet->id" :alt="$pet->name" variant="hero" />
                <div class="p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        @php $care = $pet->careStatus(); @endphp
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="font-display text-3xl font-semibold">{{ $pet->name }}</h2>
                            <x-ui.status-pill :variant="$care->variant()">{{ $care->label() }}</x-ui.status-pill>
                        </div>
                        <p class="mt-1 text-ink-mute">{{ $pet->species }} · {{ $pet->breed }} · Age {{ $pet->age ?? '—' }} · {{ $pet->gender ?? '—' }}</p>
                        <p class="mt-4 max-w-2xl text-ink-soft">{{ $pet->medical_history }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('owner.pets.health', $pet) }}" class="btn-ghost !py-2 !text-sm">Health</a>
                        <a href="{{ route('owner.pets.edit', $pet) }}" class="btn-ghost !py-2 !text-sm">Edit</a>
                        <a href="{{ route('owner.pets.show', $pet) }}" class="btn-primary !py-2 !text-sm">Open</a>
                    </div>
                </div>
                <div class="mt-4 text-sm text-ink-mute">{{ $pet->health_records_count }} health records · {{ $pet->appointments_count }} appointments</div>
                </div>
            </div>
        @endforeach
    @else
        <div class="surface mt-6 p-8 text-center text-ink-mute">No pets yet.</div>
    @endif
</div>
@endsection
