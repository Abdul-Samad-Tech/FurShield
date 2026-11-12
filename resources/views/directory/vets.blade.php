@extends('layouts.app')
@section('title', 'Vets near me')
@section('content')
@php
    $kept = request()->only(['latitude', 'longitude', 'city']);
    $pins = collect($clinics)->filter(fn (array $clinic): bool => $clinic['latitude'] !== null && $clinic['longitude'] !== null)->values();
@endphp
<section class="mx-auto max-w-7xl px-4 pb-24 pt-28 sm:px-6 lg:px-8" data-nearby>
    <div class="max-w-2xl">
        <h1 class="font-display text-5xl font-bold text-text">Vets near me</h1>
        <p class="mt-fs-xs text-text">Clinics on the map, closest first when you share where you are. If location is off, search by city.</p>
    </div>

    <p class="mt-fs-sm text-sm text-text" role="status" data-nearby-status>
        @if ($located)
            Sorted by distance from the location you shared.
        @else
            Search by city, or use your location to sort by distance.
        @endif
    </p>

    <div class="mt-fs-md flex flex-wrap items-end gap-fs-sm">
        <form method="GET" action="{{ route('vets.index') }}" class="grid flex-1 gap-fs-2xs sm:max-w-md">
            @foreach (request()->only(['latitude', 'longitude', 'specialization', 'rating', 'today']) as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            <label for="vet-city" class="text-sm font-medium text-text">City</label>
            <div class="flex flex-wrap gap-fs-2xs">
                <input id="vet-city" name="city" value="{{ request('city') }}" maxlength="120" class="min-h-11 flex-1 rounded-fs-md border border-border-strong bg-surface-raised px-fs-sm text-sm text-text focus:border-focus focus:outline-none focus:ring-2 focus:ring-focus/40" placeholder="Karachi">
                <x-ui.button type="submit">Search city</x-ui.button>
            </div>
        </form>
        <x-ui.button type="button" variant="secondary" data-nearby-locate>Use my location</x-ui.button>
    </div>

    <div class="mt-fs-sm flex flex-wrap gap-fs-2xs" role="group" aria-label="Filters">
        <a href="{{ route('vets.index', $kept) }}" class="inline-flex min-h-11 items-center rounded-fs-xl border px-fs-sm text-sm font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus {{ request()->filled('specialization') || request()->filled('rating') || request()->boolean('today') ? 'border-border-strong bg-surface-raised text-text' : 'border-success bg-success-soft text-text' }}" aria-pressed="{{ request()->filled('specialization') || request()->filled('rating') || request()->boolean('today') ? 'false' : 'true' }}">All</a>
        @foreach ($specializations as $name)
            @php $active = request('specialization') === $name; @endphp
            <a href="{{ route('vets.index', array_merge($kept, $active ? [] : ['specialization' => $name], request()->only(['rating', 'today']))) }}" class="inline-flex min-h-11 items-center rounded-fs-xl border px-fs-sm text-sm font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus {{ $active ? 'border-success bg-success-soft text-text' : 'border-border-strong bg-surface-raised text-text' }}" aria-pressed="{{ $active ? 'true' : 'false' }}">{{ $name }}</a>
        @endforeach
        @php $rated = (float) request('rating') >= 4; @endphp
        <a href="{{ route('vets.index', array_merge($kept, request()->only(['specialization', 'today']), $rated ? [] : ['rating' => 4])) }}" class="inline-flex min-h-11 items-center rounded-fs-xl border px-fs-sm text-sm font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus {{ $rated ? 'border-success bg-success-soft text-text' : 'border-border-strong bg-surface-raised text-text' }}" aria-pressed="{{ $rated ? 'true' : 'false' }}">4+ stars</a>
        @php $today = request()->boolean('today'); @endphp
        <a href="{{ route('vets.index', array_merge($kept, request()->only(['specialization', 'rating']), $today ? [] : ['today' => 1])) }}" class="inline-flex min-h-11 items-center rounded-fs-xl border px-fs-sm text-sm font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus {{ $today ? 'border-success bg-success-soft text-text' : 'border-border-strong bg-surface-raised text-text' }}" aria-pressed="{{ $today ? 'true' : 'false' }}">Available today</a>
    </div>

    <div class="mt-fs-md lg:grid lg:grid-cols-2 lg:items-start lg:gap-fs-md">
        <div class="lg:sticky lg:top-28">
            <div data-nearby-map class="relative isolate z-0 h-80 overflow-hidden rounded-fs-lg border border-border bg-surface-sunken md:h-[28rem]" role="region" aria-label="Map of veterinarians">
                <div data-nearby-canvas class="absolute inset-0"></div>
                <div data-nearby-placeholder class="absolute inset-0 z-10 grid place-items-center bg-surface-sunken p-fs-md text-center">
                    <div>
                        <x-ui.skeleton variant="card" />
                        <p class="mt-fs-sm text-sm text-text">The map loads when you reach it. The list works without it.</p>
                    </div>
                </div>
                <p data-nearby-error class="absolute inset-0 z-10 hidden place-items-center bg-surface-raised p-fs-md text-center text-sm text-text" role="alert">The map didn’t load. The list below still works.</p>
            </div>
            <p class="mt-fs-2xs text-xs text-text-muted">Map data © <a class="underline underline-offset-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors</p>
            <script type="application/json" id="nearby-pins">@json($pins)</script>
        </div>

        <div class="mt-fs-md grid gap-fs-sm lg:mt-0">
            @forelse ($clinics as $clinic)
                <article id="vet-{{ $clinic['id'] }}" data-vet-id="{{ $clinic['id'] }}" class="rounded-fs-lg border border-border bg-surface-raised p-fs-md text-text data-[selected=true]:ring-2 data-[selected=true]:ring-focus">
                    <div class="flex flex-wrap items-start justify-between gap-fs-sm">
                        <div>
                            <h2 class="font-display text-2xl font-semibold">{{ $clinic['name'] }}</h2>
                            <p class="mt-fs-2xs text-sm text-text">{{ $clinic['specialization'] }} · {{ $clinic['city'] }}</p>
                            @if ($clinic['hours'] !== '')
                                <p class="mt-fs-2xs text-sm text-text">{{ $clinic['hours'] }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-fs-2xs">
                            @if ($clinic['open_now'])
                                <span class="inline-flex items-center gap-fs-2xs rounded-fs-xl bg-success-soft px-fs-xs py-fs-3xs text-xs font-medium text-text">
                                    <span class="size-1.5 rounded-full bg-success" aria-hidden="true"></span>
                                    Open now
                                </span>
                            @endif
                            <x-ui.badge>{{ $clinic['rating'] !== null ? $clinic['rating'].' stars' : 'New' }}</x-ui.badge>
                        </div>
                    </div>
                    @if ($clinic['distance_label'])
                        <p class="mt-fs-sm text-sm font-medium text-text">{{ $clinic['distance_label'] }}</p>
                    @elseif ($clinic['latitude'] === null)
                        <p class="mt-fs-sm text-sm text-text">This clinic is not on the map yet.</p>
                    @endif
                    <div class="mt-fs-sm flex flex-wrap gap-fs-2xs">
                        <x-ui.button :href="$clinic['url']" size="sm">View clinic</x-ui.button>
                        @if ($clinic['latitude'] !== null)
                            <x-ui.button type="button" variant="secondary" size="sm" data-nearby-focus="{{ $clinic['id'] }}" aria-label="Show {{ $clinic['name'] }} on the map">
                                <x-ui.icon name="map-pin" size="sm" />
                                Map
                            </x-ui.button>
                        @endif
                        @if ($clinic['tel'])
                            <x-ui.button class="md:hidden" :href="$clinic['tel']" variant="secondary" size="sm" aria-label="Call {{ $clinic['name'] }}">
                                <x-ui.icon name="phone" size="sm" />
                                Call
                            </x-ui.button>
                        @endif
                        @if ($clinic['directions_url'])
                            <x-ui.button class="md:hidden" :href="$clinic['directions_url']" variant="secondary" size="sm" target="_blank" rel="noopener noreferrer" aria-label="Directions to {{ $clinic['name'] }}">
                                <x-ui.icon name="navigation" size="sm" />
                                Directions
                            </x-ui.button>
                        @endif
                    </div>
                </article>
            @empty
                <x-ui.empty-state icon="stethoscope" title="No veterinarians match" description="Try another city, or clear the filters.">
                    <x-slot:action>
                        <x-ui.button :href="route('vets.index', $kept)">Clear filters</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            @endforelse
        </div>
    </div>
</section>
@endsection
