@php
    $canChange = in_array($appt->status, [\App\Enums\AppointmentStatus::Pending, \App\Enums\AppointmentStatus::Approved], true);
    $canComplete = in_array($appt->status, \App\Services\AppointmentService::HOLDING, true);
    $when = $appt->appointment_time->timezone(config('app.timezone'));
@endphp

<article class="rounded-fs-lg border border-border bg-surface-raised p-fs-md text-text shadow-fs-sm">
    <div class="flex flex-wrap items-start justify-between gap-fs-sm">
        <div>
            <h3 class="font-display text-2xl font-semibold">{{ $appt->pet->name }}</h3>
            <p class="mt-fs-2xs text-text">{{ $when->format('D, M j, g:i A') }} · {{ $appt->owner->name }}</p>
            @if ($appt->reason)
                <p class="mt-fs-2xs text-sm text-text">{{ $appt->reason }}</p>
            @endif
            @if ($appt->owner_notes)
                <p class="mt-fs-2xs text-sm text-text">{{ $appt->owner_notes }}</p>
            @endif
        </div>
        <x-ui.status-pill :variant="$appt->status->variant()">{{ $appt->status->label() }}</x-ui.status-pill>
    </div>

    <div class="mt-fs-sm flex flex-wrap gap-fs-2xs">
        <x-ui.button :href="route('vet.pets.history', $appt->pet)" variant="ghost" size="sm">Medical history</x-ui.button>
        @if ($appt->status !== \App\Enums\AppointmentStatus::Cancelled)
            <x-ui.button :href="route('vet.appointments.ics', $appt)" variant="secondary" size="sm">
                <x-ui.icon name="download" />
                Calendar file
            </x-ui.button>
        @endif
        @if ($appt->status === \App\Enums\AppointmentStatus::Pending)
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
        @endif
        @if ($canChange)
            <x-ui.modal name="reschedule-{{ $appt->id }}" title="Move this visit">
                <x-slot:trigger>
                    <x-ui.button type="button" variant="secondary" size="sm">Reschedule</x-ui.button>
                </x-slot:trigger>
                <form method="POST" action="{{ route('vet.appointments.reschedule', $appt) }}" class="mt-fs-sm grid gap-fs-sm" x-data="visitReschedule({{ $appt->id }})">
                    @csrf
                    @method('PATCH')
                    <p class="text-sm text-text">Pick another open time. The owner is notified.</p>
                    <div>
                        <p class="text-sm font-medium text-text" id="reschedule-date-{{ $appt->id }}">Date</p>
                        <div class="mt-fs-2xs flex flex-wrap gap-fs-2xs" role="group" aria-labelledby="reschedule-date-{{ $appt->id }}">
                            <template x-for="day in dateList()" :key="day.date">
                                <button type="button" class="min-h-11 rounded-fs-md border border-border-strong px-fs-sm text-sm text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" :class="date === day.date ? 'border-success bg-success-soft' : ''" :aria-pressed="date === day.date ? 'true' : 'false'" @click="date = day.date; time = ''" x-text="day.label"></button>
                            </template>
                        </div>
                    </div>
                    <fieldset>
                        <legend class="text-sm font-medium text-text">Open times</legend>
                        <div class="mt-fs-2xs grid grid-cols-2 gap-fs-2xs sm:grid-cols-3" role="radiogroup" aria-label="Open times">
                            <template x-for="slot in slots()" :key="slot.value">
                                <label class="flex min-h-11 cursor-pointer items-center justify-center rounded-fs-md border border-border-strong px-fs-xs text-sm text-text has-[:checked]:border-success has-[:checked]:bg-success-soft focus-within:ring-2 focus-within:ring-focus">
                                    <input type="radio" name="appointment_time" class="sr-only" :value="slot.value" x-model="time">
                                    <span x-text="slot.label"></span>
                                </label>
                            </template>
                        </div>
                        <p x-show="slots().length === 0" class="mt-fs-2xs text-sm text-text">No open times on this day.</p>
                    </fieldset>
                    <x-ui.button type="submit" x-bind:disabled="!time">Move visit</x-ui.button>
                </form>
            </x-ui.modal>
        @endif
    </div>

    @if ($canComplete)
        <form method="POST" action="{{ route('vet.appointments.treatment', $appt) }}" class="mt-fs-md grid gap-fs-sm border-t border-border pt-fs-md">
            @csrf
            <h4 class="font-display text-xl font-semibold text-text">Complete and add treatment</h4>
            <x-ui.input label="Treatment title" name="title" required maxlength="160" :error="$errors->first('title')" />
            <div class="grid gap-fs-sm md:grid-cols-2">
                <x-ui.textarea label="Diagnosis" name="diagnosis" rows="2" />
                <x-ui.textarea label="Treatment" name="treatment" rows="2" />
                <x-ui.textarea label="Symptoms" name="symptoms" rows="2" />
                <x-ui.textarea label="Medications" name="medications" rows="2" />
                <x-ui.textarea label="Lab results" name="lab_results" rows="2" />
                <x-ui.textarea label="Notes" name="notes" rows="2" />
            </div>
            <x-ui.button type="submit" class="w-fit">Save treatment and complete</x-ui.button>
        </form>
    @endif
</article>
