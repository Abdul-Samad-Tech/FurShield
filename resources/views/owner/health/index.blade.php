@extends('layouts.dashboard')
@section('title', 'Health Timeline')
@section('content')
<h1 class="font-display text-4xl font-bold">{{ $pet->name }} health timeline</h1>
<p class="mt-2 text-ink-mute">Vaccinations, treatments, documents, and insurance in one visual timeline.</p>

<div class="mt-10 grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
    <section class="surface p-6">
        <h2 class="font-display text-2xl font-semibold">Timeline</h2>
        <div class="relative mt-8 ml-3 space-y-8 border-l-2 border-tide/20 pl-8">
            @forelse ($records as $record)
                <article class="relative">
                    <span class="timeline-dot"></span>
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-tide">{{ $record->visit_date->format('M d, Y') }} · {{ \App\Enums\HealthRecordType::tryFrom($record->record_type)?->label() ?? $record->record_type }}</div>
                    <h3 class="mt-1 font-display text-xl font-semibold">{{ $record->title }}</h3>
                    @if ($record->next_due_date)
                        <p class="mt-2 text-sm text-ink-soft">Next due {{ $record->next_due_date->format('M d, Y') }}@if ($record->recurrence_days) · every {{ $record->recurrence_days }} days @endif</p>
                    @endif
                    <p class="mt-2 text-sm text-ink-mute">{{ $record->diagnosis }}</p>
                    <p class="mt-1 text-sm text-ink-soft">{{ $record->treatment }}</p>
                    @if ($record->document_path)
                        <a href="{{ asset('storage/'.$record->document_path) }}" target="_blank" class="mt-2 inline-block text-sm font-medium text-tide">View document</a>
                    @endif
                </article>
            @empty
                <p class="text-ink-mute">No health records yet.</p>
            @endforelse
        </div>
    </section>

    <div class="space-y-6">
        <form method="POST" action="{{ route('owner.pets.health.store', $pet) }}" enctype="multipart/form-data" class="surface space-y-3 p-6">
            @csrf
            <h2 class="font-display text-xl font-semibold">Add record</h2>
            <input type="date" name="visit_date" class="field" required aria-label="Visit date">
            <x-ui.select
                name="record_type"
                label="Record type"
                required
                :error="$errors->first('record_type')"
                hint="Vaccination and checkup repeat every 365 days. Deworming repeats every 90 days."
            >
                <option value="" @selected(old('record_type') === null || old('record_type') === '')>Choose a type</option>
                @foreach (\App\Enums\HealthRecordType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(old('record_type') === $type->value)>{{ $type->optionLabel() }}</option>
                @endforeach
            </x-ui.select>
            <input name="title" placeholder="Title" class="field" required>
            <textarea name="diagnosis" placeholder="Diagnosis" class="field" rows="2"></textarea>
            <textarea name="treatment" placeholder="Treatment" class="field" rows="2"></textarea>
            <textarea name="allergies" placeholder="Allergies" class="field" rows="2"></textarea>
            <input type="file" name="document" class="field">
            <button class="btn-primary w-full">Save record</button>
        </form>

        <section class="surface p-6">
            <h2 class="font-display text-xl font-semibold">Insurance policies</h2>
            <div class="mt-4 space-y-3">
                @forelse ($policies as $policy)
                    <div class="rounded-2xl bg-mist/70 p-4 text-sm">
                        <div class="font-medium">{{ $policy->provider_name }} · {{ $policy->policy_number }}</div>
                        <div class="text-ink-mute">{{ optional($policy->valid_from)->format('M Y') }} – {{ optional($policy->valid_to)->format('M Y') }}</div>
                        @if ($policy->document_path)
                            <a href="{{ asset('storage/'.$policy->document_path) }}" class="text-tide" target="_blank">View file</a>
                        @endif
                    </div>
                @empty
                    <p class="text-ink-mute">No policies uploaded.</p>
                @endforelse
            </div>
            <form method="POST" action="{{ route('owner.pets.insurance.store', $pet) }}" enctype="multipart/form-data" class="mt-5 space-y-3 border-t border-mist-deep pt-5">
                @csrf
                <input name="provider_name" placeholder="Provider" class="field" required>
                <input name="policy_number" placeholder="Policy number" class="field" required>
                <div class="grid grid-cols-2 gap-3">
                    <input type="date" name="valid_from" class="field">
                    <input type="date" name="valid_to" class="field">
                </div>
                <textarea name="claim_notes" placeholder="Claim notes" class="field" rows="2"></textarea>
                <input type="file" name="document" class="field">
                <button class="btn-ghost w-full">Upload policy</button>
            </form>
        </section>
    </div>
</div>
@endsection
