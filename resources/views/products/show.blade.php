@extends('layouts.app')
@section('title', $product->name)
@section('content')
<section class="mx-auto max-w-7xl px-4 pb-20 pt-28 sm:px-6 lg:px-8">
    <div class="grid items-start gap-8 lg:grid-cols-2">
        <div class="space-y-6">
            <div class="surface overflow-hidden">
                <x-card-image :src="$product->image_path" type="product" :seed="$product->id" :alt="$product->name" variant="square" eager />
            </div>

            <div class="surface p-6">
                <h2 class="font-display text-2xl font-semibold text-ink">Product details</h2>
                <p class="mt-3 text-ink-mute">{{ $product->description }}</p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-mist/70 p-4">
                        <div class="text-xs uppercase tracking-[0.16em] text-ink-mute">Category</div>
                        <div class="mt-1 font-semibold text-ink">{{ $product->category->label() }}</div>
                    </div>
                    <div class="rounded-2xl bg-mist/70 p-4">
                        <div class="text-xs uppercase tracking-[0.16em] text-ink-mute">Price</div>
                        <div class="mt-1 font-semibold text-ink">{{ \App\Support\Furshield::money($product->price) }}</div>
                    </div>
                    <div class="rounded-2xl bg-mist/70 p-4">
                        <div class="text-xs uppercase tracking-[0.16em] text-ink-mute">Rating</div>
                        <div class="mt-1 font-semibold text-ink">★ {{ number_format((float) $product->average_rating, 1) }} / 5</div>
                    </div>
                    <div class="rounded-2xl bg-mist/70 p-4">
                        <div class="text-xs uppercase tracking-[0.16em] text-ink-mute">Stock</div>
                        <div class="mt-1 font-semibold text-ink">{{ $product->stock_quantity }} units</div>
                    </div>
                    <div class="rounded-2xl bg-mist/70 p-4">
                        <div class="text-xs uppercase tracking-[0.16em] text-ink-mute">Availability</div>
                        <div class="mt-1 font-semibold {{ $product->stock_quantity > 0 ? 'text-tide-deep' : 'text-red-600' }}">
                            {{ $product->stock_quantity > 0 ? 'In stock' : 'Out of stock' }}
                        </div>
                    </div>
                    <div class="rounded-2xl bg-mist/70 p-4">
                        <div class="text-xs uppercase tracking-[0.16em] text-ink-mute">Listing status</div>
                        <div class="mt-1 font-semibold text-ink">{{ $product->is_active ? 'Active' : 'Inactive' }}</div>
                    </div>
                </div>

                <div class="mt-6 border-t border-mist-deep pt-5 text-sm text-ink-soft">
                    <div class="flex flex-wrap justify-between gap-2">
                        <span>Product ID</span>
                        <strong class="text-ink">#{{ $product->id }}</strong>
                    </div>
                    <div class="mt-2 flex flex-wrap justify-between gap-2">
                        <span>Added on</span>
                        <strong class="text-ink">{{ $product->created_at?->format('M d, Y') }}</strong>
                    </div>
                    <div class="mt-2 flex flex-wrap justify-between gap-2">
                        <span>Last updated</span>
                        <strong class="text-ink">{{ $product->updated_at?->format('M d, Y') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div data-reveal="left">
            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-tide">{{ $product->category->label() }}</div>
            <h1 class="mt-3 font-display text-5xl font-bold">{{ $product->name }}</h1>
            <p class="mt-4 text-lg text-ink-mute">{{ $product->description }}</p>

            <div class="surface mt-8 space-y-3 p-5 text-sm">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-ink-mute">Price</span>
                    <span class="text-2xl font-semibold text-ink">{{ \App\Support\Furshield::money($product->price) }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-ink-mute">Rating</span>
                    <span class="font-medium">★ {{ number_format((float) $product->average_rating, 1) }} / 5</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-ink-mute">Stock quantity</span>
                    <span class="font-medium">{{ $product->stock_quantity }} units</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-ink-mute">Availability</span>
                    <span class="font-medium {{ $product->stock_quantity > 0 ? 'text-tide-deep' : 'text-red-600' }}">
                        {{ $product->stock_quantity > 0 ? 'In stock' : 'Out of stock' }}
                    </span>
                </div>
            </div>

            @auth
                @if (auth()->user()->isOwner() && $product->stock_quantity > 0)
                    <form
                        method="POST"
                        action="{{ route('owner.cart.add', $product) }}"
                        class="mt-8 flex flex-wrap items-end gap-3"
                        x-data='{ product: @json($card) }'
                        @submit.prevent="$store.cart.add(product, $event.target.quantity.value)"
                    >
                        @csrf
                        <div>
                            <label for="quantity" class="text-sm font-medium text-text">Quantity</label>
                            <input id="quantity" type="number" name="quantity" value="1" min="1" max="{{ min(20, $product->stock_quantity) }}" class="field mt-fs-3xs !w-24">
                        </div>
                        <x-ui.button type="submit">Add to cart</x-ui.button>
                    </form>
                    @if ($product->isLowStock())
                        <p class="mt-fs-sm"><x-ui.badge variant="bloom">Low stock · {{ $product->stock_quantity }} left</x-ui.badge></p>
                    @endif
                    <form method="POST" action="{{ route('products.rate', $product) }}" class="surface mt-8 space-y-3 p-5">
                        @csrf
                        <h3 class="font-display text-xl font-semibold">Rate this product</h3>
                        <input type="number" name="rating" min="1" max="5" class="field" required placeholder="Rating 1-5">
                        <textarea name="comment" rows="3" class="field" placeholder="Comment"></textarea>
                        <button class="btn-ghost">Submit rating</button>
                    </form>
                @elseif (auth()->user()->isOwner())
                    <p class="mt-8 text-sm font-medium text-red-600">This product is currently out of stock.</p>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn-primary mt-8 inline-flex">Log in to add to cart</a>
            @endauth
        </div>
    </div>

    <div class="mt-12">
        <h2 class="font-display text-3xl font-semibold">Reviews</h2>
        <div class="mt-6 space-y-4">
            @forelse ($feedbacks as $feedback)
                <div class="surface p-5">
                    <div class="font-medium">{{ $feedback->user->name }} · ★ {{ $feedback->rating }}</div>
                    <p class="mt-2 text-ink-mute">{{ $feedback->comment }}</p>
                </div>
            @empty
                <p class="text-ink-mute">No reviews yet.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
