@extends('layouts.dashboard')
@section('title', 'Appointments')
@section('content')
<div x-data="{ ready: false }" x-init="ready = true">
<div x-show="!ready" class="grid gap-fs-sm" role="status" aria-label="Loading visits">
    <x-ui.skeleton variant="text" />
    <x-ui.skeleton variant="card" />
</div>
<div x-show="ready" x-cloak>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="font-display text-4xl font-bold text-text">Appointments</h1>
        <p class="mt-fs-2xs text-text">Pending and approved visits can be moved or cancelled. The other person is told.</p>
    </div>
    <x-ui.button :href="route('owner.appointments.create')">Book appointment</x-ui.button>
</div>

<x-ui.error-summary class="mt-fs-md" />

<div class="mt-fs-md grid gap-fs-sm">
    @forelse ($appointments as $appt)
        @php
            $canChange = in_array($appt->status, [\App\Enums\AppointmentStatus::Pending, \App\Enums\AppointmentStatus::Approved], true);
        @endphp
        <article class="rounded-fs-lg border border-border bg-surface-raised p-fs-md text-text">
            <div class="flex flex-wrap items-start justify-between gap-fs-sm">
                <div>
                    <h2 class="font-display text-2xl font-semibold">{{ $appt->pet->name }} with {{ $appt->vet->name }}</h2>
                    <p class="mt-fs-2xs">{{ $appt->appointment_time->timezone(config('app.timezone'))->format('D, M j, g:i A') }}@if ($appt->reason) · {{ $appt->reason }}@endif</p>
                </div>
                <x-ui.status-pill :variant="$appt->status->variant()">{{ $appt->status->label() }}</x-ui.status-pill>
            </div>
            <div class="mt-fs-sm flex flex-wrap gap-fs-2xs">
                @if ($appt->status !== \App\Enums\AppointmentStatus::Cancelled)
                    <x-ui.button :href="route('owner.appointments.ics', $appt)" variant="secondary" size="sm">
                        <x-ui.icon name="download" />
                        Calendar file
                    </x-ui.button>
                @endif
                @if ($canChange)
                    <x-ui.button :href="route('owner.appointments.create', ['reschedule' => $appt->id])" variant="ghost" size="sm">Reschedule</x-ui.button>
                    <x-ui.confirm-dialog
                        name="cancel-visit-{{ $appt->id }}"
                        title="Cancel this visit?"
                        message="The vet is told, and the time opens for someone else."
                        confirm="Cancel visit"
                        cancel="Keep visit"
                        :action="route('owner.appointments.cancel', $appt)"
                        method="PATCH"
                    >
                        <x-slot:trigger>
                            <x-ui.button type="button" variant="danger" size="sm">Cancel</x-ui.button>
                        </x-slot:trigger>
                    </x-ui.confirm-dialog>
                @endif
            </div>
        </article>
    @empty
        <x-ui.empty-state icon="calendar" title="No visits yet" description="Book a time from a vet's open schedule.">
            <x-slot:action>
                <x-ui.button :href="route('owner.appointments.create')">Book appointment</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    @endforelse
</div>
<div class="mt-fs-md">{{ $appointments->links() }}</div>
</div>
</div>
@endsection
