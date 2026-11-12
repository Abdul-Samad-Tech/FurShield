@extends('layouts.dashboard')
@section('title', 'Vet Profile')
@section('content')
<div x-data="{ ready: false }" x-init="ready = true">
    <div x-show="!ready" class="grid max-w-2xl gap-fs-sm" role="status" aria-label="Loading profile">
        <x-ui.skeleton variant="text" />
        <x-ui.skeleton variant="card" />
    </div>

    <div x-show="ready" x-cloak>
        <h1 class="font-display text-4xl font-bold text-text">Your clinic profile</h1>
        <x-ui.error-summary class="mt-fs-md max-w-2xl" />
        <form method="POST" action="{{ route('vet.profile.update') }}" class="mt-fs-md grid max-w-2xl gap-fs-sm">
            @csrf
            @method('PATCH')
            <input type="hidden" name="windows_present" value="1">
            <x-ui.input label="Specialization" name="specialization" :value="old('specialization', $user->specialization)" maxlength="160" />
            <x-ui.input label="Experience (years)" name="experience_years" type="number" min="0" max="60" :value="old('experience_years', $user->experience_years)" />
            <x-ui.input label="City" name="city" :value="old('city', $user->city)" maxlength="120" />
            <x-ui.input label="Phone" name="phone" :value="old('phone', $user->phone)" maxlength="30" />
            <x-ui.textarea label="Address" name="address" rows="2">{{ old('address', $user->address) }}</x-ui.textarea>
            <x-ui.textarea label="Bio" name="bio" rows="3">{{ old('bio', $user->bio) }}</x-ui.textarea>

            <fieldset class="grid gap-fs-sm rounded-fs-lg border border-border bg-surface-raised p-fs-md" x-data="slotEditor">
                <legend class="px-fs-2xs font-display text-2xl font-semibold text-text">Open hours</legend>
                <p class="text-sm text-text">Each row is one weekday window. Visits are cut into equal lengths, and owners only see times nobody else has taken.</p>
                <script type="application/json" id="slot-editor-seed">@json($windows)</script>
                <template x-for="(row, index) in rows" :key="index">
                    <div class="grid gap-fs-2xs rounded-fs-md border border-border p-fs-sm sm:grid-cols-4">
                        <label class="grid gap-fs-2xs text-sm font-medium text-text">
                            Weekday
                            <select class="rounded-fs-md border border-border-strong bg-surface-raised px-fs-xs py-fs-xs text-sm text-text focus:border-focus focus:outline-none focus:ring-2 focus:ring-focus/40" x-model="row.weekday" :name="`windows[${index}][weekday]`">
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                                <option value="6">Saturday</option>
                                <option value="7">Sunday</option>
                            </select>
                        </label>
                        <label class="grid gap-fs-2xs text-sm font-medium text-text">
                            Start
                            <input type="time" class="rounded-fs-md border border-border-strong bg-surface-raised px-fs-xs py-fs-xs text-sm text-text focus:border-focus focus:outline-none focus:ring-2 focus:ring-focus/40" x-model="row.start" :name="`windows[${index}][start]`">
                        </label>
                        <label class="grid gap-fs-2xs text-sm font-medium text-text">
                            End
                            <input type="time" class="rounded-fs-md border border-border-strong bg-surface-raised px-fs-xs py-fs-xs text-sm text-text focus:border-focus focus:outline-none focus:ring-2 focus:ring-focus/40" x-model="row.end" :name="`windows[${index}][end]`">
                        </label>
                        <label class="grid gap-fs-2xs text-sm font-medium text-text">
                            Length
                            <select class="rounded-fs-md border border-border-strong bg-surface-raised px-fs-xs py-fs-xs text-sm text-text focus:border-focus focus:outline-none focus:ring-2 focus:ring-focus/40" x-model="row.slot_minutes" :name="`windows[${index}][slot_minutes]`">
                                <option value="10">10 min</option>
                                <option value="15">15 min</option>
                                <option value="20">20 min</option>
                                <option value="30">30 min</option>
                                <option value="45">45 min</option>
                                <option value="60">60 min</option>
                            </select>
                        </label>
                        <x-ui.button type="button" variant="ghost" size="sm" class="w-fit sm:col-span-4" x-on:click="remove(index)" aria-label="Remove this window">Remove</x-ui.button>
                    </div>
                </template>
                <p x-show="rows.length === 0" x-cloak class="text-sm text-text">No open hours yet. Add a window before owners can book.</p>
                <x-ui.button type="button" variant="secondary" class="w-fit" x-on:click="add()">Add window</x-ui.button>
            </fieldset>

            <x-ui.button type="submit" class="w-fit">Save profile</x-ui.button>
        </form>
    </div>
</div>
@endsection
