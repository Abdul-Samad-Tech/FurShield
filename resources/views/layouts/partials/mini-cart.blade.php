@if ($cartState !== null)
    <script type="application/json" id="fs-cart-boot">@json($cartState)</script>
    <div
        x-data="fsCart"
        x-show="$store.cart.open"
        x-cloak
        class="fixed inset-0 z-[70] flex justify-end"
        @keydown.escape.window="if ($store.cart.open && !$store.palette.open) $store.cart.hide()"
    >
        <div class="absolute inset-0 bg-text/40" @click="$store.cart.hide()"></div>
        <aside
            id="mini-cart"
            data-cart-panel
            role="dialog"
            aria-modal="true"
            aria-labelledby="mini-cart-title"
            tabindex="-1"
            class="relative flex h-full w-full max-w-md flex-col bg-surface-raised text-text shadow-fs-lg"
            @keydown.tab="trap($event)"
        >
            <div class="flex items-center justify-between gap-fs-sm border-b border-border px-fs-md py-fs-sm">
                <h2 id="mini-cart-title" class="font-display text-2xl font-semibold">Cart</h2>
                <button type="button" class="grid size-11 place-items-center rounded-fs-sm text-text hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="$store.cart.hide()" aria-label="Close cart">
                    <x-ui.icon name="x" />
                </button>
            </div>
            <div class="flex-1 overflow-y-auto px-fs-md py-fs-sm">
                <div x-show="$store.cart.busy && $store.cart.items.length === 0" role="status">
                    <x-ui.skeleton variant="card" />
                    <p class="sr-only">Loading cart</p>
                </div>
                <p x-show="!$store.cart.busy && $store.cart.items.length === 0" x-cloak class="text-sm text-text">Your cart is empty.</p>
                <p x-show="$store.cart.error" x-cloak class="mb-fs-sm rounded-fs-md border border-danger/30 bg-danger-soft px-fs-sm py-fs-xs text-sm text-text" role="alert" x-text="$store.cart.error"></p>
                <ul class="grid gap-fs-sm">
                    <template x-for="item in $store.cart.items" :key="item.id">
                        <li class="rounded-fs-md border border-border p-fs-sm">
                            <div class="flex gap-fs-sm">
                                <img :src="item.image" :alt="item.name" class="size-16 shrink-0 rounded-fs-sm object-cover">
                                <div class="min-w-0 flex-1">
                                    <a :href="item.url" class="font-medium text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" x-text="item.name"></a>
                                    <p class="text-sm text-text-muted" x-text="item.line_label"></p>
                                    <div class="mt-fs-2xs flex items-center gap-fs-2xs">
                                        <button type="button" class="grid size-11 place-items-center rounded-fs-sm border border-border-strong hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" :aria-label="'Decrease quantity of ' + item.name" :disabled="item.pending || item.quantity <= 1" @click="$store.cart.setQuantity(item, item.quantity - 1)">−</button>
                                        <span class="min-w-6 text-center text-sm" x-text="item.quantity"></span>
                                        <button type="button" class="grid size-11 place-items-center rounded-fs-sm border border-border-strong hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" :aria-label="'Increase quantity of ' + item.name" :disabled="item.pending || item.quantity >= Math.min(20, item.stock)" @click="$store.cart.setQuantity(item, item.quantity + 1)">+</button>
                                        <button type="button" class="ml-auto min-h-11 rounded-fs-sm px-fs-xs text-sm font-medium hover:bg-danger-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="$store.cart.remove(item)">Remove</button>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>
            <div class="border-t border-border px-fs-md py-fs-sm">
                <p class="font-display text-xl font-semibold" x-text="'Total ' + $store.cart.total_label"></p>
                <div class="mt-fs-sm flex flex-wrap gap-fs-2xs">
                    <button type="button" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-fs-md bg-success px-fs-md text-sm font-medium text-text-inverse focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus disabled:opacity-60" :disabled="$store.cart.items.length === 0 || $store.cart.busy" @click="$store.cart.checkout()">Place order</button>
                    <a :href="$store.cart.cart_url" class="inline-flex min-h-11 items-center justify-center rounded-fs-md border border-border-strong px-fs-md text-sm font-medium text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">View cart</a>
                </div>
            </div>
        </aside>
    </div>
@endif
