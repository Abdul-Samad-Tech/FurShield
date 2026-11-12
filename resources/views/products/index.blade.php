@extends('layouts.app')
@section('title', 'Products')
@section('content')
@php
    $chipQuery = request()->except(['page', 'category']);
    $lcpProduct = $products->first();
    $lcpImage = $lcpProduct
        ? \App\Support\DummyImages::describe($lcpProduct->image_path, 'product', (int) $lcpProduct->id)
        : null;
@endphp
@if ($lcpImage)
    @push('head')
        <link rel="preload" as="image" href="{{ $lcpImage['url'] }}" fetchpriority="high">
    @endpush
@endif
<section
    class="mx-auto max-w-7xl px-4 pb-20 pt-28 sm:px-6 lg:px-8"
    x-data="fsShop"
    data-catalog-id="shop-catalog"
    @quick-view="openQuick($event.detail)"
    @add-to-cart="addProduct($event.detail)"
    @keydown.escape.window="onEscape()"
>
    <div>
        <h1 class="text-balance font-display text-4xl font-bold text-text sm:text-5xl">Pet care shop</h1>
        <p class="mt-fs-2xs max-w-[65ch] text-text">Browse food, grooming, toys, health, accessories, and training. Checkout takes contact details, a pickup or delivery address, and payment.</p>
    </div>

    <div class="sticky top-20 z-30 -mx-4 mt-fs-md border-y border-border bg-surface/95 px-4 py-fs-sm backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="flex gap-fs-2xs overflow-x-auto" role="group" aria-label="Product categories">
            <a
                href="{{ route('products.index', $chipQuery) }}"
                data-shop-filter
                class="inline-flex min-h-11 shrink-0 items-center rounded-fs-xl border px-fs-sm text-sm font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus {{ request()->filled('category') ? 'border-border-strong bg-surface-raised text-text' : 'border-success bg-success-soft text-text' }}"
                @unless (request()->filled('category')) aria-current="page" @endunless
                @click="if (!$event.metaKey && !$event.ctrlKey && !$event.shiftKey) filtering = true"
            >All</a>
            @foreach ($categories as $category)
                <a
                    href="{{ route('products.index', array_merge($chipQuery, ['category' => $category->value])) }}"
                    data-shop-filter
                    class="inline-flex min-h-11 shrink-0 items-center rounded-fs-xl border px-fs-sm text-sm font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus {{ request('category') === $category->value ? 'border-success bg-success-soft text-text' : 'border-border-strong bg-surface-raised text-text' }}"
                    @if (request('category') === $category->value) aria-current="page" @endif
                    @click="if (!$event.metaKey && !$event.ctrlKey && !$event.shiftKey) filtering = true"
                >{{ $category->label() }}</a>
            @endforeach
        </div>
        <form method="GET" class="mt-fs-sm flex flex-col gap-fs-2xs sm:flex-row" @submit="filtering = true">
            @if (request()->filled('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <label for="shop-search" class="sr-only">Search products</label>
            <input id="shop-search" name="q" value="{{ request('q') }}" placeholder="Search products" class="min-h-11 flex-1 rounded-fs-md border border-border-strong bg-surface-raised px-fs-sm text-sm text-text focus:border-focus focus:outline-none focus:ring-2 focus:ring-focus/40">
            <label for="shop-sort" class="sr-only">Sort products</label>
            <select id="shop-sort" name="sort" class="min-h-11 rounded-fs-md border border-border-strong bg-surface-raised px-fs-sm text-sm text-text focus:border-focus focus:outline-none focus:ring-2 focus:ring-focus/40">
                <option value="">Newest</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>Price low to high</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>Price high to low</option>
                <option value="rating" @selected(request('sort') === 'rating')>Top rated</option>
            </select>
            <x-ui.button type="submit">Search</x-ui.button>
        </form>
    </div>

    @if ($recommendation['pet'])
        <section id="recommended-for" class="mt-fs-lg" aria-labelledby="recommended-heading">
            <h2 id="recommended-heading" class="font-display text-2xl font-semibold text-text">Recommended for {{ $recommendation['pet']->name }}</h2>
            <p class="mt-fs-3xs text-sm text-text-muted">Picked from {{ $recommendation['pet']->name }}'s species and age.</p>
            @if ($recommendation['products']->isEmpty())
                <x-ui.empty-state class="mt-fs-sm" title="Nothing picked yet" icon="shopping-bag" description="Nothing in the shop matches {{ $recommendation['pet']->name }} yet." />
            @else
                <div class="mt-fs-sm grid gap-fs-sm sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($recommendation['products'] as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    @if ($buyAgain->isNotEmpty())
        <section class="mt-fs-lg" aria-labelledby="buy-again-heading">
            <h2 id="buy-again-heading" class="font-display text-2xl font-semibold text-text">Buy again</h2>
            <p class="mt-fs-3xs text-sm text-text-muted">From orders you already placed.</p>
            <div class="mt-fs-sm grid gap-fs-sm sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($buyAgain as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    <div x-show="filtering" x-cloak class="mt-fs-lg grid gap-fs-sm sm:grid-cols-2 lg:grid-cols-3" role="status" aria-label="Loading products">
        @foreach (range(1, 6) as $unused)
            <x-ui.skeleton variant="card" />
        @endforeach
    </div>

    <div id="shop-grid" x-show="!filtering" class="mt-fs-lg">
        <h2 class="sr-only">Available products</h2>
        @if ($products->isEmpty())
            <x-ui.empty-state title="No products" icon="shopping-bag" description="Nothing matches that search. Try another category or clear the search.">
                <x-slot:action>
                    <x-ui.button :href="route('products.index')">Clear filters</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <div class="grid gap-fs-sm sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <x-product-card :product="$product" :eager="$loop->first" />
                @endforeach
            </div>
            <div class="mt-fs-md">{{ $products->links() }}</div>
        @endif
    </div>

    <div
        x-show="quick !== null || quickError !== '' || loadingQuick"
        x-cloak
        class="fixed inset-0 z-[75] flex items-end justify-center p-fs-sm sm:items-center"
        role="presentation"
    >
        <div class="absolute inset-0 bg-text/40" @click="closeQuick()"></div>
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="quick-view-title"
            tabindex="-1"
            x-ref="quickDialog"
            class="relative max-h-[calc(100vh-2rem)] w-full max-w-lg overflow-y-auto rounded-fs-lg border border-border bg-surface-raised p-fs-md text-text shadow-fs-lg focus:outline-none"
            @keydown.tab="trapQuick($event)"
        >
            <div class="flex items-start justify-between gap-fs-sm">
                <h2 id="quick-view-title" class="font-display text-2xl font-semibold" x-text="quick && quick.name ? quick.name : 'Product'"></h2>
                <button type="button" class="grid size-11 shrink-0 place-items-center rounded-fs-sm text-text hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="closeQuick()" aria-label="Close quick view">
                    <x-ui.icon name="x" />
                </button>
            </div>
            <div x-show="loadingQuick" class="mt-fs-sm" role="status">
                <x-ui.skeleton variant="card" />
                <p class="sr-only">Loading product</p>
            </div>
            <p x-show="quickError" x-cloak class="mt-fs-sm text-sm text-text" role="alert" x-text="quickError"></p>
            <template x-if="quick && quick.name && !loadingQuick">
                <div class="mt-fs-sm">
                    <img :src="quick.image" :alt="quick.name" class="h-48 w-full rounded-fs-md object-cover">
                    <p class="mt-fs-sm text-xs font-medium text-text-muted" x-text="quick.category"></p>
                    <p class="mt-fs-2xs text-sm text-text" x-text="quick.description"></p>
                    <p class="mt-fs-sm font-display text-2xl font-semibold" x-text="quick.price_label"></p>
                    <p class="mt-fs-3xs text-sm text-text-muted" x-text="quick.rating + ' out of 5'"></p>
                    <p x-show="quick.low_stock" class="mt-fs-2xs">
                        <span class="inline-flex items-center rounded-fs-xs bg-warning-soft px-fs-2xs py-fs-3xs text-xs font-medium text-text">Low stock</span>
                    </p>
                    <p x-show="quick.stock < 1" class="mt-fs-2xs text-sm text-text">Out of stock</p>
                    <div class="mt-fs-md flex flex-wrap gap-fs-2xs">
                        <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-fs-md bg-success px-fs-md text-sm font-medium text-text-inverse focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" x-show="quick.add_url && quick.stock > 0" @click="$store.cart.add(quick, 1); closeQuick()">Add to cart</button>
                        <a :href="quick.url" class="inline-flex min-h-11 items-center justify-center rounded-fs-md border border-border-strong bg-surface-raised px-fs-md text-sm font-medium text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">Full details</a>
                    </div>
                </div>
            </template>
        </div>
    </div>
</section>
<script type="application/json" id="shop-catalog">@json($catalog)</script>
@endsection
