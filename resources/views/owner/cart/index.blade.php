@extends('layouts.dashboard')
@section('title', 'Cart')
@section('content')
<h1 class="font-display text-4xl font-bold text-text">Your cart</h1>
<p class="mt-fs-2xs text-text-muted">Review the cart, then continue to checkout to add contact details and payment.</p>

<div x-data x-cloak>
    <template x-if="$store.cart.items.length === 0">
        <div class="mt-fs-md">
            <x-ui.empty-state title="Cart is empty" icon="shopping-cart" description="Add something from the shop. It stays here until you place the order.">
                <x-slot:action>
                    <x-ui.button :href="route('products.index')">Browse products</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </div>
    </template>
    <div x-show="$store.cart.items.length > 0" class="mt-fs-md space-y-fs-sm">
        <template x-for="item in $store.cart.items" :key="item.id">
            <article class="rounded-fs-lg border border-border bg-surface-raised p-fs-sm">
                <div class="flex flex-wrap items-start gap-fs-sm">
                    <a :href="item.url" class="shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">
                        <img :src="item.image" :alt="item.name" class="size-16 rounded-fs-md object-cover">
                    </a>
                    <div class="min-w-0 flex-1">
                        <a :href="item.url" class="font-display text-xl font-semibold text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" x-text="item.name"></a>
                        <p class="mt-fs-3xs text-sm text-text-muted" x-text="item.price_label + ' each'"></p>
                        <p class="mt-fs-3xs text-sm text-text" x-text="item.line_label"></p>
                    </div>
                    <div class="flex items-center gap-fs-2xs">
                        <button type="button" class="grid size-11 place-items-center rounded-fs-sm border border-border-strong text-text hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" :aria-label="'Decrease quantity of ' + item.name" :disabled="item.pending || item.quantity <= 1" @click="$store.cart.setQuantity(item, item.quantity - 1)">−</button>
                        <span class="min-w-8 text-center text-sm font-medium text-text" x-text="item.quantity"></span>
                        <button type="button" class="grid size-11 place-items-center rounded-fs-sm border border-border-strong text-text hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" :aria-label="'Increase quantity of ' + item.name" :disabled="item.pending || item.quantity >= Math.min(20, item.stock)" @click="$store.cart.setQuantity(item, item.quantity + 1)">+</button>
                        <button type="button" class="min-h-11 rounded-fs-sm px-fs-sm text-sm font-medium text-text hover:bg-danger-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="$store.cart.remove(item)">Remove</button>
                    </div>
                </div>
            </article>
        </template>
        <div class="flex flex-wrap items-center justify-between gap-fs-sm rounded-fs-lg border border-border bg-surface-raised p-fs-md">
            <p class="font-display text-2xl font-semibold text-text" x-text="'Total ' + $store.cart.total_label"></p>
            <a href="{{ route('owner.checkout.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-fs-md bg-success px-fs-md text-sm font-medium text-text-inverse focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">Checkout</a>
        </div>
        <p x-show="$store.cart.error" x-cloak class="rounded-fs-md border border-danger/30 bg-danger-soft px-fs-sm py-fs-xs text-sm text-text" role="alert" x-text="$store.cart.error"></p>
    </div>
</div>

<noscript>
    @if ($order && $order->items->isNotEmpty())
        <div class="mt-fs-md space-y-fs-sm">
            @foreach ($order->items as $item)
                <article class="rounded-fs-lg border border-border bg-surface-raised p-fs-sm">
                    <h2 class="font-display text-xl font-semibold">{{ $item->product->name }}</h2>
                    <p class="text-sm text-text-muted">{{ \App\Support\Furshield::money($item->price_each) }} each · {{ \App\Support\Furshield::money($item->price_each * $item->quantity) }}</p>
                    <form method="POST" action="{{ route('owner.cart.update', $item) }}" class="mt-fs-sm flex items-center gap-fs-2xs">
                        @csrf
                        @method('PUT')
                        <label for="qty-{{ $item->id }}" class="sr-only">Quantity of {{ $item->product->name }}</label>
                        <input id="qty-{{ $item->id }}" type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ min(20, $item->product->stock_quantity) }}" class="min-h-11 w-20 rounded-fs-md border border-border-strong px-fs-sm">
                        <button type="submit" class="min-h-11 rounded-fs-md border border-border-strong px-fs-sm text-sm">Update</button>
                    </form>
                    <form method="POST" action="{{ route('owner.cart.remove', $item) }}" class="mt-fs-2xs">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="min-h-11 text-sm">Remove</button>
                    </form>
                </article>
            @endforeach
            <p class="font-display text-2xl">Total {{ \App\Support\Furshield::money($order->total_amount) }}</p>
            <a href="{{ route('owner.checkout.create') }}" class="mt-fs-sm inline-flex min-h-11 items-center rounded-fs-md bg-success px-fs-md text-text-inverse">Checkout</a>
        </div>
    @else
        <p class="mt-fs-md text-text">Cart is empty. <a href="{{ route('products.index') }}">Browse products</a></p>
    @endif
</noscript>
@endsection
