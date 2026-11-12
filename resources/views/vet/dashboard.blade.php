@extends('layouts.dashboard')

@section('title', 'Vet Dashboard')

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
            <h1 class="font-display text-4xl font-bold text-text">Today, {{ auth()->user()->name }}</h1>
            <p class="mt-fs-2xs text-text-muted">The next patient is marked. Requests waiting for a yes or no sit beside the day.</p>
        </div>

        <div class="mt-fs-md grid gap-fs-sm sm:grid-cols-2">
            <x-ui.stat-card label="Visits today" :value="$today->count()" icon="calendar" :count="true" />
            <x-ui.stat-card label="Pending approvals" :value="$pending->count()" icon="inbox" :count="true" />
        </div>

        <div class="mt-fs-sm grid gap-fs-sm lg:grid-cols-12">
            <x-ui.card class="lg:col-span-7">
                <h2 class="font-display text-2xl font-semibold text-text">Today</h2>
                @if ($next && ! $today->contains('id', $next->id))
                    <div class="mt-fs-sm rounded-fs-md bg-success-soft p-fs-sm text-text">
                        <p class="text-sm font-medium text-success">Next patient</p>
                        <p class="mt-fs-2xs font-display text-xl font-semibold">{{ $next->pet->name }} · {{ $next->owner->name }}</p>
                        <p class="mt-fs-2xs">{{ $next->appointment_time->timezone(config('app.timezone'))->format('M j, g:i A') }}@if ($next->reason) · {{ $next->reason }}@endif</p>
                    </div>
                @endif
                @if ($today->isEmpty())
                    <x-ui.empty-state icon="calendar" title="No visits today" description="The day stays clear until a request is approved or a visit is booked." />
                @else
                    <ol class="relative mt-fs-md space-y-fs-sm border-l border-border pl-fs-md">
                        @foreach ($today as $appt)
                            @php $isNext = $next && $next->id === $appt->id; @endphp
                            <li @class(['rounded-fs-md p-fs-sm', 'bg-success-soft' => $isNext])>
                                <span @class(['absolute -left-1.5 mt-2 size-3 rounded-full', 'bg-tide-deep' => $isNext, 'bg-tide' => ! $isNext]) aria-hidden="true"></span>
                                @if ($isNext)
                                    <p class="text-sm font-medium text-success">Next patient</p>
                                @endif
                                <p class="font-medium text-text">{{ $appt->appointment_time->timezone(config('app.timezone'))->format('g:i A') }} · {{ $appt->pet->name }}</p>
                                <p class="text-sm text-text">{{ $appt->owner->name }}@if ($appt->reason) · {{ $appt->reason }}@endif</p>
                                <div class="mt-fs-2xs flex flex-wrap items-center gap-fs-sm">
                                    <x-ui.status-pill variant="neutral">{{ $appt->status->label() }}</x-ui.status-pill>
                                    <a href="{{ route('vet.pets.history', $appt->pet) }}" class="text-sm font-medium text-success focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">Medical history</a>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-ui.card>

            <x-ui.card class="lg:col-span-5">
                <h2 class="font-display text-2xl font-semibold text-text">Pending approvals</h2>
                @if ($pending->isEmpty())
                    <x-ui.empty-state icon="circle-check" title="Nothing waiting" description="New requests land here. Approve or decline each one in a click." />
                @else
                    <ul class="mt-fs-sm divide-y divide-border">
                        @foreach ($pending as $appt)
                            <li class="py-fs-sm">
                                <p class="font-medium text-text">{{ $appt->pet->name }} · {{ $appt->owner->name }}</p>
                                <p class="text-sm text-text-muted">{{ $appt->appointment_time->timezone(config('app.timezone'))->format('M j, g:i A') }}@if ($appt->reason) · {{ $appt->reason }}@endif</p>
                                <div class="mt-fs-sm flex flex-wrap gap-fs-2xs">
                                    <form method="POST" action="{{ route('vet.appointments.update', $appt) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <x-ui.button type="submit" size="sm" aria-label="Approve {{ $appt->pet->name }}">Approve</x-ui.button>
                                    </form>
                                    <form method="POST" action="{{ route('vet.appointments.update', $appt) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                        <x-ui.button type="submit" size="sm" variant="danger" aria-label="Decline {{ $appt->pet->name }}">Decline</x-ui.button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </div>
    </div>
</div>
@endsection
