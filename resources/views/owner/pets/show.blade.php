@extends('layouts.dashboard')
@section('title', $pet->name)
@section('content')
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        @php $care = $pet->careStatus(); @endphp
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="font-display text-4xl font-bold">{{ $pet->name }}</h1>
            <x-ui.status-pill :variant="$care->variant()">{{ $care->label() }}</x-ui.status-pill>
        </div>
        <p class="mt-2 text-ink-mute">{{ $pet->species }} · {{ $pet->breed }} · Age {{ $pet->age ?? '—' }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('owner.pets.health', $pet) }}" class="btn-primary !py-2 !text-sm">Health timeline</a>
        <a href="{{ route('owner.pets.edit', $pet) }}" class="btn-ghost !py-2 !text-sm">Edit</a>
        <x-ui.confirm-dialog
            name="delete-pet"
            title="Delete this pet profile?"
            message="This removes the pet profile from your records."
            confirm="Delete pet"
            :action="route('owner.pets.destroy', $pet)"
            method="DELETE"
        >
            <x-slot:trigger>
                <x-ui.button type="button" variant="danger" size="sm">Delete</x-ui.button>
            </x-slot:trigger>
        </x-ui.confirm-dialog>
    </div>
</div>

<x-ui.card class="mt-8">
    <div class="flex flex-wrap items-start justify-between gap-fs-md">
        <div>
            <h2 class="font-display text-2xl font-semibold text-text">Digital Pet ID</h2>
            <p class="mt-fs-2xs max-w-xl text-sm text-text">Choose what a finder can see. Age, medical history, records, and your email stay off the public page.</p>
        </div>
        <img src="{{ route('owner.pets.tag', $pet) }}" alt="QR code for {{ $pet->name }}'s public tag" width="148" height="148" class="size-36 rounded-fs-md bg-surface">
    </div>

    <div class="mt-fs-md flex flex-wrap gap-fs-2xs">
        <x-ui.button :href="route('owner.pets.tag', [$pet, 'download' => 1])">
            <x-ui.icon name="download" />
            Download tag
        </x-ui.button>
        <x-ui.button :href="route('pets.public', $pet->public_token)" variant="secondary" target="_blank" rel="noopener noreferrer">
            Open public page
        </x-ui.button>
        <x-ui.confirm-dialog
            name="regenerate-pet-tag"
            title="Replace this pet tag?"
            message="The current link and QR code will stop working. Print a new tag after this."
            confirm="Replace tag"
            :action="route('owner.pets.public-token.regenerate', $pet)"
        >
            <x-slot:trigger>
                <x-ui.button type="button" variant="ghost" size="sm">Replace tag link</x-ui.button>
            </x-slot:trigger>
        </x-ui.confirm-dialog>
    </div>

    <form method="POST" action="{{ route('owner.pets.public-id.update', $pet) }}" class="mt-fs-md grid gap-fs-md">
        @csrf
        @method('PATCH')

        <fieldset class="grid gap-fs-2xs">
            <legend class="text-sm font-medium text-text">Public fields</legend>
            <input type="hidden" name="fields_present" value="1">
            <div class="grid gap-fs-2xs sm:grid-cols-2">
                @foreach (\App\Services\PetIdService::FIELDS as $key => $label)
                    <label class="flex items-center gap-fs-2xs rounded-fs-md border border-border-strong bg-surface-raised px-fs-sm py-fs-xs text-sm text-text focus-within:ring-2 focus-within:ring-focus">
                        <input
                            type="checkbox"
                            name="fields[]"
                            value="{{ $key }}"
                            class="size-4 accent-success focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus"
                            @checked(old('fields_present') ? in_array($key, (array) old('fields', []), true) : $pet->shares($key))
                        >
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <label class="flex items-center gap-fs-2xs text-sm font-medium text-text">
            <input
                type="checkbox"
                name="is_lost"
                value="1"
                class="size-4 accent-danger focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus"
                @checked(old('fields_present') ? old('is_lost') === '1' : $pet->is_lost)
            >
            Mark as lost
        </label>

        <x-ui.textarea
            label="Last seen"
            name="lost_note"
            :error="$errors->first('lost_note')"
            hint="Shown on the public page only while this pet is marked lost."
            maxlength="500"
            rows="3"
        >{!! old('lost_note', $pet->lost_note) !!}</x-ui.textarea>

        <x-ui.button type="submit" class="w-fit">Save public tag</x-ui.button>
    </form>

    <div class="mt-fs-md border-t border-border pt-fs-md">
        <h3 class="font-display text-xl font-semibold text-text">Found reports</h3>
        @forelse ($pet->sightings as $sighting)
            <article class="mt-fs-sm rounded-fs-md bg-surface-sunken px-fs-sm py-fs-xs text-sm text-text">
                <p>{{ $sighting->message }}</p>
                <p class="mt-fs-3xs">
                    {{ $sighting->finder_name ?: 'Someone' }}
                    · {{ $sighting->created_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
                    @if ($sighting->latitude !== null && $sighting->longitude !== null)
                        · <a class="font-medium text-success underline-offset-2 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" href="https://maps.google.com/?q={{ $sighting->latitude }},{{ $sighting->longitude }}" target="_blank" rel="noopener noreferrer">Map</a>
                    @endif
                </p>
            </article>
        @empty
            <p class="mt-fs-2xs text-sm text-text">No one has reported this pet yet.</p>
        @endforelse
    </div>
</x-ui.card>

<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <div class="surface overflow-hidden">
        <x-card-image :src="$pet->photo_path" type="pet" :seed="$pet->id" :alt="$pet->name" variant="card" />
        <div class="p-6">
        <h2 class="font-display text-2xl font-semibold">Medical history</h2>
        <p class="mt-3 text-ink-soft">{{ $pet->medical_history ?: 'No notes yet.' }}</p>
        @if (!empty($pet->gallery))
            <div class="mt-6 grid grid-cols-3 gap-3">
                @foreach ($pet->gallery as $image)
                    @php $photo = \App\Support\DummyImages::describe($image, 'pet', $pet->id); @endphp
                    <img src="{{ $photo['url'] }}" alt="{{ $photo['alt'] }}" @if ($photo['width']) width="{{ $photo['width'] }}" @endif @if ($photo['height']) height="{{ $photo['height'] }}" @endif loading="lazy" decoding="async" class="aspect-square rounded-2xl object-cover">
                @endforeach
            </div>
        @endif
        </div>
    </div>
    <div class="surface p-6">
        <h2 class="font-display text-2xl font-semibold">Recent activity</h2>
        <div class="mt-4 space-y-3">
            @foreach ($pet->appointments->take(5) as $appt)
                <div class="rounded-2xl bg-mist/70 px-4 py-3 text-sm">
                    {{ $appt->appointment_time->format('M d, Y') }} · {{ $appt->vet->name }} · {{ $appt->status->label() }}
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
