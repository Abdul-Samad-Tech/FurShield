@extends('layouts.app')
@section('title', $assessment['title'])
@section('content')
@php
    $panel = match ($assessment['level']) {
        'emergency' => 'bg-danger text-text-inverse',
        'soon' => 'bg-warning-soft text-text',
        default => 'bg-success-soft text-text',
    };
    $pins = collect($clinics)->filter(fn (array $clinic): bool => $clinic['latitude'] !== null && $clinic['longitude'] !== null)->values();
@endphp

<section class="{{ $panel }} triage-result pt-28">
    <div class="mx-auto max-w-3xl px-4 py-fs-lg sm:px-6">
        <p class="text-sm font-medium">Symptom check · {{ $assessment['species_label'] }}</p>
        <h1 class="mt-fs-2xs font-display text-5xl font-bold">{{ $assessment['title'] }}</h1>
        <p class="mt-fs-sm text-base">{{ $assessment['reason'] }}</p>
        <p class="mt-fs-xs text-base">You selected {{ implode(', ', $assessment['symptom_labels']) }}.</p>
        <ul class="mt-fs-md grid gap-fs-xs">
            @foreach ($assessment['tips'] as $tip)
                <li>{{ $tip }}</li>
            @endforeach
        </ul>
        <p class="mt-fs-md text-base" role="note">{{ $disclaimer }}</p>

        @if ($assessment['level'] === 'emergency')
            <div class="mt-fs-md">
                @if ($emergencyTel)
                    <x-ui.button variant="secondary" size="lg" class="w-full sm:w-auto" :href="$emergencyTel">Call {{ $emergencyPhone }}</x-ui.button>
                @else
                    <p class="text-base">This demo has no emergency phone number set. Use the clinics below, or the vet you already know.</p>
                @endif
            </div>
        @endif

        <div class="mt-fs-md rounded-fs-lg bg-surface-raised p-fs-md text-text">
            <h2 class="font-display text-2xl font-semibold">Save summary to the health timeline</h2>
            @auth
                @if (auth()->user()->isOwner())
                    @if ($pets->isEmpty())
                        <x-ui.empty-state class="py-fs-md" icon="paw-print" title="No pets yet" description="Add a pet, then come back to this result to save the summary.">
                            <x-slot:action>
                                <x-ui.button :href="route('owner.pets.create')">Add a pet</x-ui.button>
                            </x-slot:action>
                        </x-ui.empty-state>
                    @else
                        <form method="POST" action="{{ route('triage.save') }}" class="mt-fs-sm grid gap-fs-sm">
                            @csrf
                            <input type="hidden" name="species" value="{{ $assessment['species'] }}">
                            @foreach ($assessment['symptoms'] as $symptom)
                                <input type="hidden" name="symptoms[]" value="{{ $symptom }}">
                            @endforeach
                            <x-ui.select label="Pet" name="pet_id" id="triage-pet" required :error="$errors->first('pet_id')">
                                <option value="">Choose a pet</option>
                                @foreach ($pets as $pet)
                                    <option value="{{ $pet->id }}" @selected((string) old('pet_id') === (string) $pet->id)>{{ $pet->name }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.button type="submit">Save summary</x-ui.button>
                        </form>
                    @endif
                @else
                    <p class="mt-fs-xs text-sm text-text">Sign in as the pet owner to save this on a health timeline.</p>
                @endif
            @else
                <p class="mt-fs-xs text-sm text-text">Sign in as the pet owner to save this on a health timeline. This result stays in this browser until you leave.</p>
                <x-ui.button class="mt-fs-sm" :href="route('login')" variant="secondary">Sign in to save this summary</x-ui.button>
            @endauth
        </div>
    </div>
</section>

@if ($assessment['level'] === 'emergency')
    <section class="mx-auto max-w-3xl px-4 py-fs-lg text-text sm:px-6" data-nearby>
        <h2 class="font-display text-3xl font-semibold">Nearest vets</h2>
        <p class="mt-fs-xs text-sm text-text" role="status" data-nearby-status>
            @if ($located)
                Sorted by distance from the location you shared.
            @else
                Share a location to sort clinics, or search the full directory by city.
            @endif
        </p>
        <div class="mt-fs-md">
            <div data-nearby-map class="relative isolate z-0 h-64 overflow-hidden rounded-fs-lg border border-border bg-surface-sunken" role="region" aria-label="Map of veterinarians">
                <div data-nearby-canvas class="absolute inset-0"></div>
                <div data-nearby-placeholder class="absolute inset-0 z-10 grid place-items-center bg-surface-sunken p-fs-md text-center">
                    <p class="text-sm text-text">The map loads when you reach it. The list works without it.</p>
                </div>
                <p data-nearby-error class="absolute inset-0 z-10 hidden place-items-center bg-surface-raised p-fs-md text-center text-sm text-text" role="alert">The map didn’t load. The list below still works.</p>
            </div>
            <p class="mt-fs-2xs text-xs text-text-muted">Map data © <a class="underline underline-offset-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors</p>
            <script type="application/json" id="nearby-pins">@json($pins)</script>
        </div>

        <div class="mt-fs-md grid gap-fs-sm">
            @forelse ($clinics as $clinic)
                <article id="vet-{{ $clinic['id'] }}" data-vet-id="{{ $clinic['id'] }}" class="rounded-fs-lg border border-border bg-surface-raised p-fs-md data-[selected=true]:ring-2 data-[selected=true]:ring-focus">
                    <h3 class="font-display text-2xl font-semibold">{{ $clinic['name'] }}</h3>
                    <p class="mt-fs-2xs text-sm text-text">{{ $clinic['specialization'] }} · {{ $clinic['city'] }}</p>
                    @if ($clinic['open_now'])
                        <p class="mt-fs-xs"><span class="inline-flex items-center gap-fs-2xs rounded-fs-xl bg-success-soft px-fs-xs py-fs-3xs text-xs font-medium text-text"><span class="size-1.5 rounded-full bg-success" aria-hidden="true"></span>Open now</span></p>
                    @endif
                    @if ($clinic['distance_label'])
                        <p class="mt-fs-xs text-sm font-medium text-text">{{ $clinic['distance_label'] }}</p>
                    @elseif ($clinic['latitude'] === null)
                        <p class="mt-fs-xs text-sm text-text">This clinic is not on the map yet.</p>
                    @endif
                    <div class="mt-fs-sm flex flex-wrap gap-fs-2xs">
                        <x-ui.button :href="$clinic['url']" size="sm">View clinic</x-ui.button>
                        @if ($clinic['latitude'] !== null)
                            <x-ui.button type="button" variant="secondary" size="sm" data-nearby-focus="{{ $clinic['id'] }}" aria-label="Show {{ $clinic['name'] }} on the map">Map</x-ui.button>
                        @endif
                        @if ($clinic['tel'])
                            <x-ui.button class="md:hidden" :href="$clinic['tel']" variant="secondary" size="sm">Call</x-ui.button>
                        @endif
                        @if ($clinic['directions_url'])
                            <x-ui.button class="md:hidden" :href="$clinic['directions_url']" variant="secondary" size="sm" target="_blank" rel="noopener noreferrer">Directions</x-ui.button>
                        @endif
                    </div>
                </article>
            @empty
                <x-ui.empty-state icon="stethoscope" title="No clinics to show" description="The directory does not have a veterinarian yet. Search by city on the vets page.">
                    <x-slot:action>
                        <x-ui.button :href="route('vets.index')">Find a vet</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            @endforelse
        </div>
        <x-ui.button class="mt-fs-md" :href="route('vets.index', array_filter(request()->only(['latitude', 'longitude'])))" variant="secondary">See all vets</x-ui.button>
    </section>
@else
    <section class="mx-auto max-w-3xl px-4 py-fs-lg sm:px-6">
        <x-ui.button :href="route('vets.index')" variant="secondary">Find a vet</x-ui.button>
        <x-ui.button class="mt-fs-sm sm:ml-fs-sm sm:mt-0" :href="route('triage')" variant="ghost">Start again</x-ui.button>
    </section>
@endif

<section class="mx-auto max-w-3xl px-4 pb-24 sm:px-6">
    @if ($assessment['level'] === 'emergency')
        <x-ui.button :href="route('triage')" variant="secondary">Start again</x-ui.button>
    @endif
</section>
@endsection
