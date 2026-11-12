@extends('layouts.dashboard')

@section('title', 'Shelter Dashboard')

@section('content')
<div x-data="{ ready: false }" x-init="ready = true">
    <div x-show="!ready" class="grid gap-fs-sm md:grid-cols-3" role="status" aria-label="Loading dashboard">
        <x-ui.skeleton variant="card" />
        <x-ui.skeleton variant="card" />
        <x-ui.skeleton variant="card" />
    </div>

    <div x-show="ready" x-cloak>
        <x-ui.error-summary />

        <div data-reveal>
            <h1 class="font-display text-4xl font-bold text-text">{{ auth()->user()->shelter_name ?? auth()->user()->name }}</h1>
            <p class="mt-fs-2xs text-text-muted">Interest moves from a first note, to a yes, to an adoption.</p>
        </div>

        <div class="mt-fs-md grid gap-fs-sm sm:grid-cols-3">
            <x-ui.stat-card label="Listings" :value="$listingCount" icon="house" :count="true" />
            <x-ui.stat-card label="Interest" :value="$interestCount" icon="heart" :count="true" />
            <x-ui.stat-card label="Adopted" :value="$stages[2]['count']" icon="circle-check" :count="true" />
        </div>

        <div class="mt-fs-sm grid gap-fs-sm lg:grid-cols-12">
            <x-ui.card class="lg:col-span-7">
                <div class="flex items-center justify-between gap-fs-sm">
                    <h2 class="font-display text-2xl font-semibold text-text">Interest funnel</h2>
                    <a href="{{ route('shelter.listings') }}" class="text-sm font-medium text-success focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">Listings</a>
                </div>
                @if ($interestCount === 0)
                    <x-ui.empty-state icon="heart" title="No interest yet" description="When someone asks about a listing, the request shows up here." />
                @else
                    <svg viewBox="0 0 100 72" class="mt-fs-md h-32 w-full" fill="none" aria-hidden="true">
                        @foreach ($stages as $index => $stage)
                            @if ($stage['width'] > 0)
                                <rect
                                    x="{{ (100 - $stage['width']) / 2 }}"
                                    y="{{ $index * 24 }}"
                                    width="{{ $stage['width'] }}"
                                    height="18"
                                    rx="4"
                                    class="{{ $stage['fill'] }}"
                                />
                            @endif
                        @endforeach
                    </svg>
                    <ul class="mt-fs-sm divide-y divide-border">
                        @foreach ($stages as $stage)
                            <li class="flex items-center justify-between py-fs-xs text-text">
                                <span>{{ $stage['label'] }}</span>
                                <span class="font-display text-2xl font-semibold" data-count="{{ $stage['count'] }}">{{ $stage['count'] }}</span>
                            </li>
                        @endforeach
                        <li class="flex items-center justify-between py-fs-xs text-text">
                            <span>Declined</span>
                            <span class="font-display text-2xl font-semibold" data-count="{{ $rejectedCount }}">{{ $rejectedCount }}</span>
                        </li>
                    </ul>
                @endif
            </x-ui.card>

            <x-ui.card class="lg:col-span-5">
                <h2 class="font-display text-2xl font-semibold text-text">Latest interest</h2>
                @if ($interests->isEmpty())
                    <x-ui.empty-state icon="inbox" title="No requests" description="Adopter notes appear here with the animal they asked about." />
                @else
                    <ul class="mt-fs-sm divide-y divide-border">
                        @foreach ($interests as $interest)
                            <li class="py-fs-sm">
                                <a href="{{ route('shelter.listings.show', $interest->listing) }}" class="font-medium text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">{{ $interest->listing->pet_name }}</a>
                                <p class="text-sm text-text-muted">{{ $interest->owner->name }} · {{ ucfirst($interest->status) }}</p>
                                @if ($interest->message)
                                    <p class="mt-fs-2xs text-sm text-text">{{ $interest->message }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </div>
    </div>
</div>
@endsection
