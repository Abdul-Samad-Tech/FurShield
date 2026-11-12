@extends('layouts.dashboard')
@section('title', 'Vet Appointments')
@section('content')
<div x-data="{ ready: false }" x-init="ready = true">
    <div x-show="!ready" class="grid gap-fs-sm" role="status" aria-label="Loading visits">
        <x-ui.skeleton variant="card" />
        <x-ui.skeleton variant="card" />
    </div>

    <div x-show="ready" x-cloak>
        <x-ui.error-summary />
        <h1 class="font-display text-4xl font-bold text-text">Visits</h1>
        <p class="mt-fs-2xs max-w-2xl text-text">Today, the days ahead, and the record of visits that already happened.</p>

        <script type="application/json" id="vet-open-slots">@json($calendars)</script>

        <x-ui.tabs class="mt-fs-md" label="Visit lists" :initial="request('page') ? 'history' : 'today'" :tabs="['today' => 'Today', 'upcoming' => 'Upcoming', 'history' => 'History']">
            <x-ui.tab id="today">
                @if ($today->isEmpty())
                    <x-ui.empty-state icon="calendar" title="No visits today" description="Approved and pending visits for today show up here." />
                @else
                    <div class="grid gap-fs-sm">
                        @foreach ($today as $appt)
                            @include('vet.partials.appointment', ['appt' => $appt])
                        @endforeach
                    </div>
                @endif
            </x-ui.tab>
            <x-ui.tab id="upcoming">
                @if ($upcoming->isEmpty())
                    <x-ui.empty-state icon="calendar" title="Nothing later" description="Visits after today land in this list." />
                @else
                    <div class="grid gap-fs-sm">
                        @foreach ($upcoming as $appt)
                            @include('vet.partials.appointment', ['appt' => $appt])
                        @endforeach
                    </div>
                @endif
            </x-ui.tab>
            <x-ui.tab id="history">
                @if ($history->isEmpty())
                    <x-ui.empty-state icon="inbox" title="No past visits" description="Completed, declined, and missed visits collect here." />
                @else
                    <div class="grid gap-fs-sm">
                        @foreach ($history as $appt)
                            @include('vet.partials.appointment', ['appt' => $appt])
                        @endforeach
                    </div>
                    <div class="mt-fs-md">{{ $history->links() }}</div>
                @endif
            </x-ui.tab>
        </x-ui.tabs>
    </div>
</div>
@endsection
