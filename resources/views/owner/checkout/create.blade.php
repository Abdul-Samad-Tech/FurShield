@extends('layouts.dashboard')
@section('title', 'Checkout')
@section('content')
<h1 class="font-display text-4xl font-bold text-text">Checkout</h1>
<p class="mt-fs-2xs text-text-muted">Confirm who the order is for, how you will receive it, and how you will pay.</p>

@if (! $order || $order->items->isEmpty())
    <div class="mt-fs-md">
        <x-ui.empty-state title="Nothing to check out" icon="shopping-cart" description="Your cart is empty. Add a product, then come back to place the order.">
            <x-slot:action>
                <x-ui.button :href="route('products.index')">Browse products</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    </div>
@else
    <form
        method="POST"
        action="{{ route('owner.checkout.store') }}"
        class="mt-fs-md grid gap-fs-md lg:grid-cols-[minmax(0,1.4fr)_minmax(16rem,0.8fr)]"
        x-data="{ fulfillment: @js(old('fulfillment', 'pickup')), payment: @js(old('payment_method', 'card')), sending: false }"
        @submit="sending = true"
    >
        @csrf
        <div class="space-y-fs-md">
            <x-ui.error-summary />

            <section class="rounded-fs-lg border border-border bg-surface-raised p-fs-md">
                <h2 class="font-display text-2xl font-semibold text-text">Contact</h2>
                <div class="mt-fs-sm grid gap-fs-sm">
                    <x-ui.input label="Name" name="contact_name" required autocomplete="name" :value="old('contact_name', $user->name)" :error="$errors->first('contact_name')" />
                    <x-ui.input label="Phone" name="contact_phone" type="tel" required autocomplete="tel" :value="old('contact_phone', $user->phone)" :error="$errors->first('contact_phone')" />
                    <x-ui.input label="Email" name="contact_email" type="email" required autocomplete="email" :value="old('contact_email', $user->email)" :error="$errors->first('contact_email')" />
                </div>
            </section>

            <section class="rounded-fs-lg border border-border bg-surface-raised p-fs-md">
                <h2 class="font-display text-2xl font-semibold text-text">Receive the order</h2>
                <div class="mt-fs-sm grid gap-fs-sm">
                    <x-ui.select label="How you will receive it" name="fulfillment" required x-model="fulfillment" :error="$errors->first('fulfillment')">
                        <option value="pickup" @selected(old('fulfillment', 'pickup') === 'pickup')>Pickup from FurShield</option>
                        <option value="delivery" @selected(old('fulfillment') === 'delivery')>Deliver to an address</option>
                    </x-ui.select>
                    <div x-show="fulfillment === 'delivery'" class="grid gap-fs-sm">
                        <x-ui.input label="Address" name="address_line" autocomplete="street-address" :value="old('address_line', $user->address)" :error="$errors->first('address_line')" />
                        <x-ui.input label="City" name="city" autocomplete="address-level2" :value="old('city', $user->city)" :error="$errors->first('city')" />
                    </div>
                    <x-ui.textarea label="Note" name="notes" rows="3" hint="Optional. For example, a gate code or a pet the order is for." :error="$errors->first('notes')">{{ old('notes') }}</x-ui.textarea>
                </div>
            </section>

            <section class="rounded-fs-lg border border-border bg-surface-raised p-fs-md">
                <h2 class="font-display text-2xl font-semibold text-text">Payment</h2>
                <p class="mt-fs-2xs text-sm text-text-muted">A card payment is checked here and only the brand and last four digits are kept. The full number and security code are not stored.</p>
                <div class="mt-fs-sm grid gap-fs-sm">
                    <x-ui.select label="Payment method" name="payment_method" required x-model="payment" :error="$errors->first('payment_method')">
                        <option value="card" @selected(old('payment_method', 'card') === 'card')>Card</option>
                        <option value="pay_on_collection" @selected(old('payment_method') === 'pay_on_collection')>Pay on collection</option>
                    </x-ui.select>
                    <div x-show="payment === 'card'" class="grid gap-fs-sm">
                        <x-ui.input label="Name on card" name="cardholder" autocomplete="cc-name" :value="old('cardholder', $user->name)" :error="$errors->first('cardholder')" />
                        <x-ui.input label="Card number" name="card_number" inputmode="numeric" autocomplete="cc-number" placeholder="4242 4242 4242 4242" :error="$errors->first('card_number')" />
                        <div class="grid gap-fs-sm sm:grid-cols-2">
                            <x-ui.input label="Expiry" name="card_expiry" inputmode="numeric" autocomplete="cc-exp" placeholder="MM/YY" :value="old('card_expiry')" :error="$errors->first('card_expiry')" />
                            <x-ui.input label="Security code" name="card_cvc" inputmode="numeric" autocomplete="cc-csc" :error="$errors->first('card_cvc')" />
                        </div>
                        <p class="text-sm text-text-muted">Use 4242 4242 4242 4242 for a successful demo payment. A number ending in 0002 is declined.</p>
                    </div>
                </div>
            </section>
        </div>

        <aside class="h-fit rounded-fs-lg border border-border bg-surface-raised p-fs-md">
            <h2 class="font-display text-2xl font-semibold text-text">Order</h2>
            <ul class="mt-fs-sm divide-y divide-border">
                @foreach ($order->items as $item)
                    <li class="flex items-start justify-between gap-fs-sm py-fs-xs">
                        <div>
                            <p class="font-medium text-text">{{ $item->product?->name ?? 'Unavailable product' }}</p>
                            <p class="text-sm text-text-muted">{{ $item->quantity }} × {{ \App\Support\Furshield::money($item->price_each) }}</p>
                        </div>
                        <p class="text-sm font-medium text-text">{{ \App\Support\Furshield::money($item->price_each * $item->quantity) }}</p>
                    </li>
                @endforeach
            </ul>
            <p class="mt-fs-sm font-display text-2xl font-semibold text-text">Total {{ \App\Support\Furshield::money($order->total_amount) }}</p>
            @error('cart')
                <p class="mt-fs-sm text-sm text-danger" role="alert">{{ $message }}</p>
            @enderror
            <x-ui.button type="submit" class="mt-fs-md w-full" x-bind:disabled="sending">
                <span x-show="!sending">Place order</span>
                <span x-show="sending" x-cloak>Placing order…</span>
            </x-ui.button>
            <a href="{{ route('owner.cart.index') }}" class="mt-fs-sm inline-flex min-h-11 items-center text-sm font-medium text-success focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">Back to cart</a>
        </aside>
    </form>
@endif
@endsection
