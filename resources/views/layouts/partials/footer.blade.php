<footer class="relative mt-24 overflow-hidden border-t border-tide/10 bg-ink text-white">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(25,168,155,0.25),transparent_35%),radial-gradient(circle_at_80%_0%,rgba(232,168,124,0.18),transparent_30%)]"></div>
    <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 md:grid-cols-[minmax(0,24rem)_auto_auto] md:justify-start md:gap-x-16 lg:px-8">
        <div>
            <div class="font-display text-3xl font-bold">FurShield</div>
            <p class="mt-3 max-w-md text-white/70">Every paw and wing deserves a shield of love — one platform for owners, vets, shelters, and thoughtful pet care.</p>
        </div>
        <div>
            <div class="text-sm font-semibold uppercase tracking-[0.2em] text-bloom-soft">Explore</div>
            <div class="mt-4 flex flex-col gap-2 text-white/75">
                <a href="{{ route('products.index') }}" class="hover:text-white" @if (request()->routeIs('products.*')) aria-current="page" @endif>Products</a>
                <a href="{{ route('care.index') }}" class="hover:text-white" @if (request()->routeIs('care.*')) aria-current="page" @endif>Care Library</a>
                <a href="{{ route('adopt.index') }}" class="hover:text-white" @if (request()->routeIs('adopt.*')) aria-current="page" @endif>Adoption</a>
            </div>
        </div>
        <div>
            <div class="text-sm font-semibold uppercase tracking-[0.2em] text-bloom-soft">Support</div>
            <div class="mt-4 flex flex-col gap-2 text-white/75">
                <a href="{{ route('about') }}" class="hover:text-white" @if (request()->routeIs('about')) aria-current="page" @endif>About Us</a>
                <a href="{{ route('contact') }}" class="hover:text-white" @if (request()->routeIs('contact')) aria-current="page" @endif>Contact</a>
                <a href="{{ route('register') }}" class="hover:text-white">Create Account</a>
            </div>
        </div>
    </div>
    <div class="relative border-t border-white/10 px-4 py-5 text-center text-sm text-white/50">
        © {{ now()->year }} FurShield. Built for responsible pet care.
    </div>
</footer>
