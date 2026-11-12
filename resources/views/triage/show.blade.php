@extends('layouts.app')
@section('title', 'Symptom check')
@section('content')
<section class="mx-auto max-w-3xl px-4 pb-24 pt-28 sm:px-6" x-data="symptomCheck">
    <h1 class="max-w-[14ch] text-balance font-display text-4xl font-bold text-text sm:text-5xl">Symptom check</h1>
    <p id="triage-disclaimer" class="mt-fs-sm text-base text-text" role="note">{{ $disclaimer }}</p>
    <p class="mt-fs-xs text-sm text-text">If your pet is struggling to breathe, having a seizure, or may have been poisoned, choose that symptom. Those always come back as Emergency now.</p>

    <form method="POST" action="{{ route('triage.store') }}" class="mt-fs-md" aria-describedby="triage-disclaimer" @submit="onSubmit($event)">
        @csrf
        <input type="hidden" name="latitude" x-model="latitude">
        <input type="hidden" name="longitude" x-model="longitude">

        <fieldset x-show="step === 1" class="grid gap-fs-sm">
            <legend class="font-display text-3xl font-semibold text-text">Which animal?</legend>
            <p class="text-sm text-text" aria-live="polite">Step 1 of 2</p>
            <div class="grid grid-cols-2 gap-fs-sm sm:grid-cols-3">
                @foreach ($species as $key => $label)
                    <label class="block">
                        <input class="peer sr-only" type="radio" name="species" value="{{ $key }}" x-model="species" @checked(old('species') === $key)>
                        <span class="flex min-h-16 cursor-pointer items-center justify-center rounded-fs-lg border border-border-strong bg-surface-raised px-fs-sm text-center text-base font-medium text-text peer-checked:border-success peer-checked:bg-success-soft peer-focus-visible:outline-none peer-focus-visible:ring-2 peer-focus-visible:ring-focus">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            <p x-show="speciesError" x-cloak class="text-sm text-danger" role="alert" x-text="speciesError"></p>
            <x-ui.button type="button" @click="next()">Continue</x-ui.button>
        </fieldset>

        <fieldset x-show="step === 2" class="grid gap-fs-sm">
            <legend class="font-display text-3xl font-semibold text-text">What are you seeing?</legend>
            <p class="text-sm text-text">Step 2 of 2. Choose every symptom that fits. Large targets, more than one is fine.</p>
            <div class="grid grid-cols-2 gap-fs-sm">
                @foreach ($symptoms as $key => $label)
                    <label class="block">
                        <input class="peer sr-only" type="checkbox" name="symptoms[]" value="{{ $key }}" x-model="symptoms" @checked(in_array($key, old('symptoms', []), true))>
                        <span class="flex min-h-16 cursor-pointer items-center justify-center rounded-fs-lg border border-border-strong bg-surface-raised px-fs-sm text-center text-base font-medium text-text peer-checked:border-success peer-checked:bg-success-soft peer-focus-visible:outline-none peer-focus-visible:ring-2 peer-focus-visible:ring-focus">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            <p x-show="symptomError" x-cloak class="text-sm text-danger" role="alert" x-text="symptomError"></p>
            <p class="text-sm text-text" role="status" x-text="locationNote"></p>
            <p x-show="submitting" x-cloak class="text-sm text-text" role="status">Checking symptoms…</p>
            <div class="flex flex-wrap gap-fs-sm">
                <x-ui.button type="button" variant="secondary" @click="step = 1">Back</x-ui.button>
                <x-ui.button type="submit">Check symptoms</x-ui.button>
            </div>
        </fieldset>
    </form>
</section>
@endsection
