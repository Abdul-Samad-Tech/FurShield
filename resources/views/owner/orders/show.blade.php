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

<div class="mt-fs-md flex flex-wrap items-center gap-fs-2xs">
    <x-ui.button :href="route('owner.orders.receipt', $order)" variant="secondary">Download receipt</x-ui.button>
    @if ($order->status === \App\Enums\OrderStatus::Placed)
        <form method="POST" action="{{ route('owner.orders.cancel', $order) }}">
            @csrf
            <x-ui.button type="submit" variant="danger">Cancel order</x-ui.button>
        </form>
    @endif
    <a href="{{ route('owner.cart.orders') }}" class="inline-flex min-h-11 items-center px-fs-sm text-sm font-medium text-success focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">All orders</a>
</div>

@if ($order->contact_email)
    <p class="mt-fs-sm text-sm text-text-muted">This is the same receipt sent to {{ $order->contact_email }}.</p>
@endif

<div class="mt-fs-md overflow-x-auto rounded-fs-lg border border-border p-fs-md" style="background:#ffffff;color:#102027;">
    @include('receipts.partials.sheet')
</div>
@endsection
