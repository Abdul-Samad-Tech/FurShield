@extends('layouts.dashboard')
@section('title', 'Orders')
@section('content')
<h1 class="font-display text-4xl font-bold text-text">Orders</h1>
<p class="mt-fs-2xs text-text-muted">Receipts for orders you have placed. Open one to see payment and cancel it while it is still open.</p>

<div class="mt-fs-md space-y-fs-sm">
    @forelse ($orders as $order)
        <article class="rounded-fs-lg border border-border bg-surface-raised p-fs-md">
            <div class="flex flex-wrap items-start justify-between gap-fs-sm">
                <div>
                    <h2 class="font-display text-2xl font-semibold text-text">
                        <a href="{{ route('owner.orders.show', $order) }}" class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">Order #{{ $order->id }}</a>
                    </h2>
                    <p class="mt-fs-3xs text-sm text-text-muted">{{ optional($order->order_date)->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</p>
                </div>
                <div class="flex flex-wrap gap-fs-2xs">
                    @if ($order->payment_status)
                        <x-ui.status-pill :variant="$order->payment_status->tone()">{{ $order->payment_status->label() }}</x-ui.status-pill>
                    @endif
                    <x-ui.status-pill :variant="$order->status === \App\Enums\OrderStatus::Cancelled ? 'danger' : 'success'">{{ $order->status->label() }}</x-ui.status-pill>
                </div>
            </div>
            <ul class="mt-fs-sm space-y-fs-2xs text-sm text-text">
                @foreach ($order->items as $item)
                    <li>{{ $item->quantity }} × {{ $item->product?->name ?? 'Removed product' }}</li>
                @endforeach
            </ul>
            <div class="mt-fs-sm flex flex-wrap items-center justify-between gap-fs-sm">
                <p class="font-semibold text-text">Total {{ \App\Support\Furshield::money($order->total_amount) }}</p>
                <x-ui.button :href="route('owner.orders.show', $order)" variant="secondary">View receipt</x-ui.button>
            </div>
        </article>
    @empty
        <x-ui.empty-state title="No orders yet" icon="receipt" description="When you check out, the receipt will show up here.">
            <x-slot:action>
                <x-ui.button :href="route('products.index')">Browse products</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    @endforelse
</div>
<div class="mt-fs-md">{{ $orders->links() }}</div>
@endsection
