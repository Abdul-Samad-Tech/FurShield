@extends('layouts.dashboard')

@section('title', 'Edit User Privileges')


@section('content')
<div class="mb-6">
    <a href="{{ route('admin.user-privileges') }}" class="text-sm font-medium text-tide hover:text-tide-deep">← Back to users</a>
</div>

<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="font-display text-4xl font-bold">{{ $user->name }}</h1>
        <p class="mt-2 text-ink-mute">{{ $user->email }} · {{ $user->role->label() }}</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.user-privileges.update', $user) }}" class="surface mt-8 overflow-hidden p-0" x-data>
    @csrf
    @method('PUT')

    <div class="border-b border-mist-deep bg-mist/60 px-5 py-4 text-sm text-ink-mute">
        Tick checkboxes to allow this user access to each feature. Role defaults are shown as hints only.
        <div class="mt-3 flex gap-2">
            <button type="button" class="btn-ghost !py-1.5 !text-xs" @click="document.querySelectorAll('[data-user-priv]').forEach(el => el.checked = true)">Select all</button>
            <button type="button" class="btn-ghost !py-1.5 !text-xs" @click="document.querySelectorAll('[data-user-priv]').forEach(el => el.checked = false)">Clear all</button>
            <button type="button" class="btn-ghost !py-1.5 !text-xs" @click="
                document.querySelectorAll('[data-user-priv]').forEach(el => {
                    el.checked = el.dataset.roleDefault === '1';
                })
            ">Use role defaults</button>
        </div>
    </div>

    <div class="divide-y divide-mist-deep">
        @foreach ($privileges as $privilege)
            @php
                $isChecked = $userMap[$privilege->value] ?? false;
                $isRoleDefault = in_array($privilege->value, $roleDefaults, true);
            @endphp
            <label class="flex cursor-pointer items-start gap-4 px-5 py-4 transition hover:bg-mist/40">
                <input
                    type="checkbox"
                    name="privileges[]"
                    value="{{ $privilege->value }}"
                    data-user-priv
                    data-role-default="{{ $isRoleDefault ? '1' : '0' }}"
                    class="mt-1 h-5 w-5 rounded border-mist-deep text-tide focus:ring-tide/40"
                    @checked($isChecked)
                >
                <span class="min-w-0 flex-1">
                    <span class="block font-medium text-ink">{{ $privilege->label() }}</span>
                    <span class="mt-0.5 block text-xs text-ink-mute">{{ $privilege->value }}</span>
                    @if ($isRoleDefault)
                        <span class="mt-1 inline-block rounded-full bg-tide/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-tide">Role default</span>
                    @endif
                </span>
            </label>
        @endforeach
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-mist-deep bg-white/70 px-5 py-4">
        <p class="text-sm text-ink-mute">Saved privileges apply to this user only.</p>
        <button type="submit" class="btn-primary">Save user privileges</button>
    </div>
</form>
@endsection
