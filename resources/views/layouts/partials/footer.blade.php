<footer class="pb-3 pt-16">
    <div class="relative mx-auto w-[calc(100%-1rem)] max-w-[90rem] overflow-hidden rounded-fs-lg border border-white/10 bg-[#03181C] text-white shadow-fs-lg sm:w-[calc(100%-1.5rem)]">
    <div class="h-1 bg-tide" aria-hidden="true"></div>
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(25,168,155,0.25),transparent_35%),radial-gradient(circle_at_80%_0%,rgba(232,168,124,0.18),transparent_30%)]"></div>
    <div class="relative grid gap-10 px-4 py-14 sm:grid-cols-2 sm:px-6 lg:grid-cols-[minmax(0,1.5fr)_auto_auto] lg:gap-x-16 lg:px-8">
        <div class="sm:col-span-2 lg:col-span-1">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/brand-shield.png') }}" alt="" width="40" height="44" class="size-10 object-contain">
                <div class="font-display text-3xl font-bold">FurShield</div>
            </div>
            <p class="mt-3 max-w-md text-white/70">Every paw and wing deserves a shield of love — one platform for owners, vets, shelters, and thoughtful pet care.</p>
            <a href="{{ route('register') }}" class="mt-6 inline-flex min-h-11 items-center justify-center rounded-fs-md bg-success px-fs-md text-sm font-medium text-text-inverse shadow-fs-sm hover:bg-tide-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus focus-visible:ring-offset-2 focus-visible:ring-offset-[#03181C]">Create Account</a>
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
            <div class="text-sm font-semibold uppercase tracking-[0.2em] text-bloom-soft">Company</div>
            <div class="mt-4 flex flex-col gap-2 text-white/75">
                <a href="{{ route('about') }}" class="hover:text-white" @if (request()->routeIs('about')) aria-current="page" @endif>About Us</a>
                <a href="{{ route('contact') }}" class="hover:text-white" @if (request()->routeIs('contact')) aria-current="page" @endif>Contact</a>
            </div>
        </div>
    </div>
    <div class="relative border-t border-white/10 px-4 py-5 text-center text-sm text-white/50">
        © {{ now()->year }} FurShield. Built for responsible pet care.
    </div>
    </div>
</footer>
