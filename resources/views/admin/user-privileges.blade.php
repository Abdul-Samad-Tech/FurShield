@extends('layouts.dashboard')

@section('title', 'User Privileges')


@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="font-display text-4xl font-bold">User privileges</h1>
        <p class="mt-2 max-w-2xl text-ink-mute">
            Assign feature access to individual users with checkboxes. Role matrix remains a separate defaults page.
        </p>
    </div>
    <a href="{{ route('admin.privileges') }}" class="btn-ghost">Open role matrix</a>
</div>

<form method="GET" class="surface mt-8 grid gap-3 p-4 md:grid-cols-3">
    <input name="q" value="{{ request('q') }}" placeholder="Search name or email" class="field">
    <select name="role" class="field">
        <option value="">All roles</option>
        @foreach ($roles as $role)
            <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
        @endforeach
    </select>
    <button class="btn-primary">Filter users</button>
</form>

<div class="mt-8 space-y-3">
    @forelse ($users as $user)
        <div class="surface flex flex-wrap items-center justify-between gap-4 p-5">
            <div class="min-w-0">
                <div class="font-display text-xl font-semibold">{{ $user->name }}</div>
                <div class="text-sm text-ink-mute">{{ $user->email }} · {{ $user->role->label() }} · {{ $user->city ?? '—' }}</div>
                <div class="mt-1 text-xs text-tide">{{ $user->privileges_count }} privilege records saved</div>
            </div>
            <a href="{{ route('admin.user-privileges.edit', $user) }}" class="btn-primary !py-2 !text-sm">Edit privileges</a>
        </div>
    @empty
        <div class="surface p-8 text-ink-mute">No users found.</div>
    @endforelse
</div>

<div class="mt-6">{{ $users->links() }}</div>
@endsection
