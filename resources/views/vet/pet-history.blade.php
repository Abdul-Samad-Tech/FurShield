@extends('layouts.dashboard')
@section('title', 'Pet History')
@section('content')
<h1 class="font-display text-4xl font-bold">{{ $pet->name }} medical history</h1>
<p class="mt-2 text-ink-mute">Owner: {{ $pet->owner->name }} · {{ $pet->species }} {{ $pet->breed }}</p>
<div class="mt-8 grid gap-4 md:grid-cols-2">
    @foreach ($pet->healthRecords as $record)
        <div class="surface p-5">
            <div class="text-xs font-semibold uppercase tracking-wide text-tide">{{ $record->visit_date->format('M d, Y') }}</div>
            <h2 class="mt-1 font-display text-xl font-semibold">{{ $record->title }}</h2>
            <div class="mt-3 space-y-1 text-sm text-ink-soft">
                <div><strong>Symptoms:</strong> {{ $record->symptoms ?: '—' }}</div>
                <div><strong>Diagnosis:</strong> {{ $record->diagnosis ?: '—' }}</div>
                <div><strong>Treatment:</strong> {{ $record->treatment ?: '—' }}</div>
                <div><strong>Medications:</strong> {{ $record->medications ?: '—' }}</div>
                <div><strong>Lab:</strong> {{ $record->lab_results ?: '—' }}</div>
            </div>
        </div>
    @endforeach
</div>
@endsection
