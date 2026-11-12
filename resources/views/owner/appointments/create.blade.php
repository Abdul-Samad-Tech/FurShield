@extends('layouts.dashboard')
@section('title', $reschedule ? 'Move a visit' : 'Book Appointment')
@section('content')
<div x-data="bookingWizard">
    <div x-show="!ready" class="grid gap-fs-sm" role="status" aria-label="Loading booking">
        <x-ui.skeleton variant="text" />
        <x-ui.skeleton variant="card" />
    </div>

    <div x-show="ready" x-cloak>
        <x-ui.error-summary />
        <h1 class="max-w-[16ch] text-balance font-display text-4xl font-bold text-text">{{ $reschedule ? 'Move this visit' : 'Book a visit' }}</h1>
        <p class="mt-fs-2xs max-w-2xl text-text">Choose the pet, then the vet and day, then an open time. Taken and past times stay off the grid.</p>

        @if ($pets->isEmpty())
            <x-ui.empty-state class="mt-fs-md" icon="paw-print" title="Add a pet first" description="A visit is booked for one of your pets.">
                <x-slot:action>
                    <x-ui.button :href="route('owner.pets.create')">Add a pet</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <script type="application/json" id="booking-data">@json($payload)</script>

            <ol class="mt-fs-md flex flex-wrap gap-fs-sm text-sm" aria-label="Booking steps">
                <li :aria-current="step === 1 ? 'step' : false"><span class="font-medium text-text">1 Pet</span></li>
                <li :aria-current="step === 2 ? 'step' : false"><span class="font-medium text-text">2 Vet and date</span></li>
                <li :aria-current="step === 3 ? 'step' : false"><span class="font-medium text-text">3 Time</span></li>
            </ol>

            <form method="POST" action="{{ $reschedule ? route('owner.appointments.reschedule', $reschedule) : route('owner.appointments.store') }}" class="mt-fs-md grid max-w-3xl gap-fs-md" x-on:submit="if (step !== 3 || !time) { $event.preventDefault(); next() }">
                @csrf
                @if ($reschedule)
                    @method('PATCH')
                @endif

                <fieldset x-show="step === 1" class="grid gap-fs-sm rounded-fs-lg border border-border bg-surface-raised p-fs-md">
                    <legend class="px-fs-2xs font-display text-2xl font-semibold text-text">Which pet?</legend>
                    <label class="grid gap-fs-2xs text-sm font-medium text-text" for="booking-pet">
                        Pet
                        <select id="booking-pet" name="pet_id" x-model="petId" class="rounded-fs-md border border-border-strong bg-surface-raised px-fs-sm py-fs-xs text-sm text-text focus:border-focus focus:outline-none focus:ring-2 focus:ring-focus/40">
                            <option value="">Choose a pet</option>
                            @foreach ($pets as $pet)
                                <option value="{{ $pet->id }}">{{ $pet->name }} ({{ $pet->species }})</option>
                            @endforeach
                        </select>
                    </label>
                    <x-ui.input label="Reason" name="reason" x-model="reason" maxlength="200" />
                    <x-ui.textarea label="Notes for the vet" name="owner_notes" rows="3" x-model="notes">{{ old('owner_notes', $reschedule->owner_notes ?? '') }}</x-ui.textarea>
                    <x-ui.button type="button" class="w-fit" x-on:click="next()" x-bind:disabled="!petId">Continue</x-ui.button>
                </fieldset>

                <fieldset x-show="step === 2" x-cloak class="grid gap-fs-sm rounded-fs-lg border border-border bg-surface-raised p-fs-md">
                    <legend class="px-fs-2xs font-display text-2xl font-semibold text-text">Vet and date</legend>
                    <template x-if="!locked">
                        <div class="grid gap-fs-sm sm:grid-cols-2">
                            <label class="grid gap-fs-2xs text-sm font-medium text-text">
                                City
                                <input type="search" x-model="city" class="rounded-fs-md border border-border-strong bg-surface-raised px-fs-sm py-fs-xs text-sm text-text focus:border-focus focus:outline-none focus:ring-2 focus:ring-focus/40" placeholder="City">
                            </label>
                            <label class="grid gap-fs-2xs text-sm font-medium text-text">
                                Specialization
                                <input type="search" x-model="specialty" class="rounded-fs-md border border-border-strong bg-surface-raised px-fs-sm py-fs-xs text-sm text-text focus:border-focus focus:outline-none focus:ring-2 focus:ring-focus/40" placeholder="Specialization">
                            </label>
                        </div>
                    </template>
                    <div class="grid gap-fs-2xs" role="radiogroup" aria-label="Veterinarian">
                        <template x-for="vet in filteredVets()" :key="vet.id">
                            <label class="flex cursor-pointer items-start gap-fs-sm rounded-fs-md border border-border-strong px-fs-sm py-fs-xs text-text has-[:checked]:border-success has-[:checked]:bg-success-soft focus-within:ring-2 focus-within:ring-focus">
                                <input type="radio" name="vet_id" class="mt-1" :value="String(vet.id)" x-model="vetId" @change="selectVet(vet.id)">
                                <span>
                                    <span class="block font-medium" x-text="vet.name"></span>
                                    <span class="block text-sm" x-text="[vet.specialization || 'General practice', vet.city].filter(Boolean).join(' · ')"></span>
                                </span>
                            </label>
                        </template>
                        <p x-show="filteredVets().length === 0" class="text-sm text-text">No vet matches that filter.</p>
                    </div>
                    <div x-show="selectedVet()">
                        <p class="text-sm font-medium text-text">Date</p>
                        <div class="mt-fs-2xs flex flex-wrap gap-fs-2xs">
                            <template x-for="day in dates()" :key="day.date">
                                <button type="button" class="min-h-11 rounded-fs-md border border-border-strong px-fs-sm text-sm text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" :class="date === day.date ? 'border-success bg-success-soft' : ''" :aria-pressed="date === day.date ? 'true' : 'false'" @click="date = day.date; time = ''" x-text="day.label"></button>
                            </template>
                        </div>
                        <p x-show="dates().length === 0" class="mt-fs-2xs text-sm text-text">This vet has no open times in the next two weeks.</p>
                    </div>
                    <div x-show="selectedVet() && selectedVet().calendar.earliest" class="rounded-fs-md bg-success-soft p-fs-sm text-text">
                        <p class="text-sm font-medium">Earliest open time</p>
                        <p class="mt-fs-2xs" x-text="selectedVet() && selectedVet().calendar.earliest ? selectedVet().calendar.earliest.label : ''"></p>
                        <x-ui.button type="button" class="mt-fs-sm" variant="secondary" size="sm" x-on:click="chooseEarliest()">Use this time</x-ui.button>
                    </div>
                    <div class="flex flex-wrap gap-fs-2xs">
                        <x-ui.button type="button" variant="ghost" x-show="!locked" x-on:click="back()">Back</x-ui.button>
                        <x-ui.button type="button" x-on:click="next()" x-bind:disabled="!vetId || !date">Continue</x-ui.button>
                    </div>
                </fieldset>

                <fieldset x-show="step === 3" x-cloak class="grid gap-fs-sm rounded-fs-lg border border-border bg-surface-raised p-fs-md">
                    <legend class="px-fs-2xs font-display text-2xl font-semibold text-text">Open times</legend>
                    <p class="text-sm text-text" x-show="selectedVet()" x-text="selectedVet() ? selectedVet().name : ''"></p>
                    <div class="grid grid-cols-2 gap-fs-2xs sm:grid-cols-3" role="radiogroup" aria-label="Open times">
                        <template x-for="slot in slots()" :key="slot.value">
                            <label class="flex min-h-11 cursor-pointer items-center justify-center rounded-fs-md border border-border-strong px-fs-xs text-sm text-text has-[:checked]:border-success has-[:checked]:bg-success-soft focus-within:ring-2 focus-within:ring-focus">
                                <input type="radio" name="appointment_time" class="sr-only" :value="slot.value" x-model="time">
                                <span x-text="slot.label"></span>
                            </label>
                        </template>
                    </div>
                    <p x-show="slots().length === 0" class="text-sm text-text">No open times on this day. Go back and choose another date.</p>
                    <div class="flex flex-wrap gap-fs-2xs">
                        <x-ui.button type="button" variant="ghost" x-on:click="back()">Back</x-ui.button>
                        <x-ui.button type="submit" x-bind:disabled="!time">{{ $reschedule ? 'Move visit' : 'Request visit' }}</x-ui.button>
                    </div>
                </fieldset>
            </form>
        @endif
    </div>
</div>
@endsection
