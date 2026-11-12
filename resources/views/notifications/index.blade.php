@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
<section class="mx-auto max-w-3xl px-4 pb-20 pt-28 sm:px-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-4xl font-bold">Notifications</h1>
            <p class="mt-2 text-ink-mute">In-app alerts for appointments, orders, and care reminders.</p>
        </div>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button class="btn-ghost !py-2 !text-sm">Mark all read</button>
        </form>
    </div>

    <div class="mt-8 space-y-3">
        @forelse ($notifications as $notification)
            <form method="POST" action="{{ route('notifications.read', $notification) }}" class="surface p-5 {{ $notification->read_at ? 'opacity-70' : '' }}">
                @csrf
                <button type="submit" class="w-full text-left">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-display text-lg font-semibold">{{ $notification->title }}</div>
                            <p class="mt-1 text-sm text-ink-mute">{{ $notification->message }}</p>
                            <div class="mt-2 text-xs uppercase tracking-wide text-tide">{{ $notification->type }} · {{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                        @unless ($notification->read_at)
                            <span class="rounded-full bg-tide px-2 py-0.5 text-[10px] font-semibold uppercase text-white">New</span>
                        @endunless
                    </div>
                </button>
            </form>
        @empty
            <div class="surface p-8 text-ink-mute">No notifications yet.</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $notifications->links() }}</div>
</section>
@endsection
