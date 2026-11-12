@extends('layouts.dashboard')
@section('title', 'Orders')
@section('content')
<h1 class="font-display text-4xl font-bold">Placed orders</h1>
<div class="mt-8 space-y-4">
    @forelse ($orders as $order)
        <div class="surface p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="font-display text-xl font-semibold">Order #{{ $order->id }}</div>
                <div class="text-sm text-ink-mute">{{ optional($order->order_date)->format('M d, Y g:i A') }}</div>
            </div>
            <div class="mt-4 space-y-3">
                @foreach ($order->items as $item)
                    <div class="flex flex-wrap items-start gap-3 rounded-2xl border border-mist-deep bg-mist/40 p-3">
                        <x-card-image :src="$item->product->image_path" type="product" :seed="$item->product->id" :alt="$item->product->name" variant="thumb" />
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-tide">{{ $item->product->category->label() }}</div>
                            <a href="{{ route('products.show', $item->product) }}" class="font-medium hover:text-tide">{{ $item->product->name }}</a>
                            <p class="mt-1 line-clamp-2 text-sm text-ink-mute">{{ $item->product->description }}</p>
                            <div class="mt-2 text-sm text-ink-soft">
                                {{ $item->quantity }} × {{ \App\Support\Furshield::money($item->price_each) }}
                                = <strong class="text-ink">{{ \App\Support\Furshield::money($item->price_each * $item->quantity) }}</strong>
                                · ★ {{ number_format((float) $item->product->average_rating, 1) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 font-semibold">Total {{ \App\Support\Furshield::money($order->total_amount) }}</div>
        </div>
    @empty
        <div class="surface p-8 text-ink-mute">No orders yet.</div>
    @endforelse
</div>
<div class="mt-6">{{ $orders->links() }}</div>
@endsection
