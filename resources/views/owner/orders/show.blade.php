@extends('layouts.dashboard')
@section('title', 'Order '.$order->id)
@section('content')
<div class="flex flex-wrap items-end justify-between gap-fs-sm">
    <div>
        <p class="text-sm font-medium text-text-muted">Order #{{ $order->id }}</p>
        <h1 class="font-display text-4xl font-bold text-text">{{ $order->status === \App\Enums\OrderStatus::Cancelled ? 'Cancelled order' : 'Order receipt' }}</h1>
        <p class="mt-fs-2xs text-text-muted">{{ optional($order->order_date)->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</p>
    </div>
    <div class="flex flex-wrap gap-fs-2xs">
        @if ($order->payment_status)
            <x-ui.status-pill :variant="$order->payment_status->tone()">{{ $order->payment_status->label() }}</x-ui.status-pill>
        @endif
        <x-ui.status-pill :variant="$order->status === \App\Enums\OrderStatus::Cancelled ? 'danger' : 'success'">{{ $order->status->label() }}</x-ui.status-pill>
    </div>
</div>

@if (session('success'))
    <p class="mt-fs-md rounded-fs-md border border-success/30 bg-success-soft px-fs-sm py-fs-xs text-sm text-text" role="status">{{ session('success') }}</p>
@endif

@error('order')
    <p class="mt-fs-md rounded-fs-md border border-danger/30 bg-danger-soft px-fs-sm py-fs-xs text-sm text-text" role="alert">{{ $message }}</p>
@enderror

<div class="mt-fs-md grid gap-fs-md lg:grid-cols-[minmax(0,1.4fr)_minmax(16rem,0.8fr)]">
    <section class="rounded-fs-lg border border-border bg-surface-raised p-fs-md">
        <h2 class="font-display text-2xl font-semibold text-text">Items</h2>
        <ul class="mt-fs-sm divide-y divide-border">
            @forelse ($order->items as $item)
                <li class="flex items-start justify-between gap-fs-sm py-fs-sm">
                    <div>
                        <p class="font-medium text-text">{{ $item->product?->name ?? 'Removed product' }}</p>
                        <p class="text-sm text-text-muted">{{ $item->quantity }} × {{ \App\Support\Furshield::money($item->price_each) }}</p>
                    </div>
                    <p class="text-sm font-medium text-text">{{ \App\Support\Furshield::money($item->price_each * $item->quantity) }}</p>
                </li>
            @empty
                <li class="py-fs-sm text-sm text-text-muted">This order has no items.</li>
            @endforelse
        </ul>
        <p class="mt-fs-sm font-display text-2xl font-semibold text-text">Total {{ \App\Support\Furshield::money($order->total_amount) }}</p>
    </section>

    <div class="space-y-fs-md">
        <section class="rounded-fs-lg border border-border bg-surface-raised p-fs-md">
            <h2 class="font-display text-2xl font-semibold text-text">Payment</h2>
            @if ($order->payment)
                <dl class="mt-fs-sm grid gap-fs-xs text-sm">
                    <div>
                        <dt class="text-text-muted">Reference</dt>
                        <dd class="font-medium text-text">{{ $order->payment->reference }}</dd>
                    </div>
                    <div>
                        <dt class="text-text-muted">Method</dt>
                        <dd class="font-medium text-text">
                            {{ $order->payment->method->label() }}
                            @if ($order->payment->card_last4)
                                · {{ $order->payment->card_brand }} ending {{ $order->payment->card_last4 }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-text-muted">Status</dt>
                        <dd class="font-medium text-text">{{ $order->payment->status->label() }}</dd>
                    </div>
                </dl>
            @else
                <p class="mt-fs-sm text-sm text-text-muted">No payment record is attached to this order.</p>
            @endif
        </section>

        <section class="rounded-fs-lg border border-border bg-surface-raised p-fs-md">
            <h2 class="font-display text-2xl font-semibold text-text">Receipt details</h2>
            <dl class="mt-fs-sm grid gap-fs-xs text-sm">
                <div>
                    <dt class="text-text-muted">Contact</dt>
                    <dd class="font-medium text-text">{{ $order->contact_name }}</dd>
                    <dd class="text-text">{{ $order->contact_phone }}</dd>
                    <dd class="text-text">{{ $order->contact_email }}</dd>
                </div>
                <div>
                    <dt class="text-text-muted">Receive</dt>
                    <dd class="font-medium text-text">{{ $order->fulfillment?->label() ?? 'Not recorded' }}</dd>
                    @if ($order->fulfillment === \App\Enums\FulfillmentMethod::Delivery)
                        <dd class="text-text">{{ $order->address_line }}</dd>
                        <dd class="text-text">{{ $order->city }}</dd>
                    @endif
                </div>
                @if ($order->notes)
                    <div>
                        <dt class="text-text-muted">Note</dt>
                        <dd class="text-text">{{ $order->notes }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        @if ($order->status === \App\Enums\OrderStatus::Placed)
            <form method="POST" action="{{ route('owner.orders.cancel', $order) }}">
                @csrf
                <x-ui.button type="submit" variant="danger">Cancel order</x-ui.button>
            </form>
        @endif

        <a href="{{ route('owner.cart.orders') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-success focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">All orders</a>
    </div>
</div>
@endsection
