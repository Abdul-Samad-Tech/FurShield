@props([
    'product',
    'showActions' => true,
    'eager' => false,
])

@php
    $inStock = $product->stock_quantity > 0;
@endphp

<article {{ $attributes->merge(['class' => 'flex h-full flex-col overflow-hidden rounded-fs-lg border border-border bg-surface-raised shadow-fs-xs']) }}>
    <a href="{{ route('products.show', $product) }}" class="block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">
        <x-card-image :src="$product->image_path" type="product" :seed="$product->id" :alt="$product->name" variant="card" :eager="$eager" />
    </a>

    <div class="flex flex-1 flex-col gap-fs-2xs p-fs-sm">
        <div class="flex items-center justify-between gap-fs-2xs">
            <p class="text-xs font-medium text-text-muted">{{ $product->category->label() }}</p>
            @if ($product->isLowStock())
                <x-ui.badge variant="bloom">Low stock · {{ $product->stock_quantity }}</x-ui.badge>
            @elseif (! $inStock)
                <span class="inline-flex items-center rounded-fs-xs bg-danger-soft px-fs-2xs py-fs-3xs text-xs font-medium text-text">Out of stock</span>
            @endif
        </div>

        <a href="{{ route('products.show', $product) }}" class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">
            <h3 class="font-display text-xl font-semibold text-text">{{ $product->name }}</h3>
        </a>

        @if ($product->description)
            <p class="line-clamp-2 text-sm text-text">{{ $product->description }}</p>
        @endif

        <p class="mt-auto font-display text-lg font-semibold tabular-nums text-text">{{ \App\Support\Furshield::money($product->price) }}</p>

        @if ($showActions)
            <div class="mt-fs-2xs flex gap-fs-2xs">
                <x-ui.button type="button" variant="secondary" class="flex-1" @click="$dispatch('quick-view', {{ $product->id }})">Quick view</x-ui.button>
                @if ($inStock)
                    @auth
                        @if (auth()->user()->isOwner())
                            <form method="POST" action="{{ route('owner.cart.add', $product) }}" class="flex-1" @submit.prevent="$dispatch('add-to-cart', {{ $product->id }})">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <x-ui.button type="submit" class="w-full">Add to cart</x-ui.button>
                            </form>
                        @endif
                    @else
                        <x-ui.button :href="route('login')" class="flex-1">Add to cart</x-ui.button>
                    @endauth
                @else
                    <x-ui.button type="button" class="flex-1" disabled>Out of stock</x-ui.button>
                @endif
            </div>
        @endif
    </div>
</article>
