@extends('layouts.app')

@section('title', 'Home')

@section('content')
<section data-hero class="relative min-h-screen overflow-hidden bg-hero-mesh text-white">
    <div class="absolute inset-0">
        @php $heroPhoto = \App\Support\DummyImages::describe('images/catalog-couch.jpg', 'care', 1); @endphp
        <img
            data-hero-visual
            src="{{ $heroPhoto['url'] }}"
            width="{{ $heroPhoto['width'] }}"
            height="{{ $heroPhoto['height'] }}"
            alt="{{ $heroPhoto['alt'] }}"
            class="h-full w-full object-cover opacity-45"
            fetchpriority="high"
            decoding="async"
        >
        <canvas data-hero-canvas class="pointer-events-none absolute inset-0 h-full w-full" aria-hidden="true"></canvas>
        <div class="absolute inset-0 bg-gradient-to-r from-[#07181C]/95 via-[#0A2E2C]/75 to-transparent"></div>
        <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-[#07181C]/85 to-transparent"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_40%,rgba(25,168,155,0.25),transparent_40%)]"></div>
    </div>

    <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col justify-center px-4 pb-24 pt-32 sm:px-6 lg:px-8">
        <h1 data-hero-brand class="max-w-[12ch] text-balance font-display text-5xl font-extrabold leading-[0.95] tracking-tight sm:text-6xl md:text-7xl lg:text-8xl">
            FurShield
        </h1>
        <p data-hero-copy class="mt-6 max-w-[65ch] text-lg leading-relaxed text-mist md:text-xl">
            Every paw and wing deserves a shield of love — manage health, book care, shop essentials, and find forever homes in one calm place.
        </p>
        <div data-hero-cta class="mt-10 flex flex-wrap gap-4">
            <a href="{{ route('register') }}" class="btn-primary" data-magnetic>Start Protecting</a>
            <a href="{{ route('adopt.index') }}" class="btn-secondary" data-magnetic>Meet Adoptable Pets</a>
        </div>
    </div>

    <div class="pointer-events-none absolute bottom-8 left-1/2 -translate-x-1/2 text-white/70" aria-hidden="true">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 9l-7 7-7-7"/></svg>
    </div>
</section>

@php
    $recordPhoto = \App\Support\DummyImages::describe(null, 'pet', 0);
    $shopPhoto = \App\Support\DummyImages::describe(null, 'product', 0);
    $adoptPhoto = \App\Support\DummyImages::describe(null, 'adoption', 0);
    $carePhoto = \App\Support\DummyImages::describe(null, 'care', 0);
    $ownerPhoto = \App\Support\DummyImages::describe(null, 'role', 0);
    $vetPhoto = \App\Support\DummyImages::describe(null, 'role', 1);
    $shelterPhoto = \App\Support\DummyImages::describe(null, 'role', 2);
    $faqs = [
        ['pay', 'Can I pay inside FurShield?', 'Yes. Checkout accepts a card or pay on collection. The full card number is not stored.'],
        ['delivery', 'Will a product be delivered?', 'No. Delivery is outside this product. The cart is a list you place, not a shipment.'],
        ['pets', 'Can one owner keep more than one pet?', 'Yes. Each pet has its own record: identity, health notes, and documents.'],
        ['record', 'Who works from that record?', 'The owner holds it. A veterinarian uses it for appointments and treatments. A shelter uses it when listing an animal and answering adoption interest.'],
        ['visit', 'What happens when I book a visit?', 'You request a time. The veterinarian can approve it, reschedule it, or complete it, and can log the treatment afterward.'],
        ['voices', 'Are the voices on this page real customers?', 'No. They are sample illustrations of the three roles. FurShield has no verified customer quotes.'],
    ];
@endphp

<section class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
    <div class="max-w-2xl">
        <h2 class="section-title">The record holds the rest of the work</h2>
        <p class="section-copy">Health, appointments, shopping, and adoption stay attached to the pet the owner already keeps.</p>
    </div>

    <div class="mt-14 grid gap-4 md:grid-cols-6" data-reveal>
        <a href="{{ route('register') }}" class="flex flex-col overflow-hidden rounded-fs-lg bg-tide-deep text-text-inverse focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus focus-visible:ring-offset-2 focus-visible:ring-offset-surface md:col-span-4 md:row-span-2">
            <img src="{{ $recordPhoto['url'] }}" width="{{ $recordPhoto['width'] }}" height="{{ $recordPhoto['height'] }}" alt="{{ $recordPhoto['alt'] }}" class="h-64 w-full object-cover md:h-80" decoding="async">
            <div class="flex flex-1 flex-col justify-end p-8">
                <h3 class="font-display text-3xl font-semibold">One record for each pet</h3>
                <p class="mt-3 max-w-xl text-lg text-text-inverse">Identity, health notes, and documents stay together. Veterinarians and shelters work from that same record.</p>
            </div>
        </a>
        <a href="{{ route('register') }}" class="flex flex-col justify-between rounded-fs-lg border border-border bg-surface-raised p-8 text-text shadow-fs-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus focus-visible:ring-offset-2 md:col-span-2">
            <x-ui.icon name="calendar" size="lg" />
            <div class="mt-10">
                <h3 class="font-display text-2xl font-semibold">Book a visit</h3>
                <p class="mt-3">Request a time. The veterinarian approves, reschedules, or completes it, then logs the treatment.</p>
            </div>
        </a>
        <a href="{{ route('products.index') }}" class="overflow-hidden rounded-fs-lg border border-border bg-surface-raised text-text shadow-fs-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus focus-visible:ring-offset-2 md:col-span-2">
            <img src="{{ $shopPhoto['url'] }}" width="{{ $shopPhoto['width'] }}" height="{{ $shopPhoto['height'] }}" alt="{{ $shopPhoto['alt'] }}" class="h-36 w-full object-cover" loading="lazy" decoding="async">
            <div class="p-8">
                <h3 class="font-display text-2xl font-semibold">Shop essentials</h3>
                <p class="mt-3">Food, grooming, toys, and care supplies go in a cart. Checkout records payment and whether the order is picked up or delivered.</p>
            </div>
        </a>
        <a href="{{ route('adopt.index') }}" class="overflow-hidden rounded-fs-lg border border-border bg-surface-raised text-text shadow-fs-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus focus-visible:ring-offset-2 md:col-span-3">
            <img src="{{ $adoptPhoto['url'] }}" width="{{ $adoptPhoto['width'] }}" height="{{ $adoptPhoto['height'] }}" alt="{{ $adoptPhoto['alt'] }}" class="h-52 w-full object-cover" loading="lazy" decoding="async">
            <div class="p-8">
                <h3 class="font-display text-2xl font-semibold">Animals waiting for a home</h3>
                <p class="mt-3">Shelters list pets with health status and care history. An owner can express interest from that listing.</p>
            </div>
        </a>
        <a href="{{ route('care.index') }}" class="overflow-hidden rounded-fs-lg border border-border bg-surface-raised text-text shadow-fs-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus focus-visible:ring-offset-2 md:col-span-3">
            <img src="{{ $carePhoto['url'] }}" width="{{ $carePhoto['width'] }}" height="{{ $carePhoto['height'] }}" alt="{{ $carePhoto['alt'] }}" class="h-52 w-full object-cover" loading="lazy" decoding="async">
            <div class="p-8">
                <h3 class="font-display text-2xl font-semibold">Care written for households</h3>
                <p class="mt-3">Feeding, hygiene, exercise, and training articles sit next to the record, not in a separate product.</p>
            </div>
        </a>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl pb-8">
        <h2 class="section-title">Three roles, one record</h2>
        <p class="section-copy">The picture stays beside that role while you read.</p>
    </div>

    <article class="grid items-center gap-10 py-16 lg:min-h-screen lg:grid-cols-2">
        <div class="home-story-visual">
            <img src="{{ $ownerPhoto['url'] }}" width="{{ $ownerPhoto['width'] }}" height="{{ $ownerPhoto['height'] }}" alt="{{ $ownerPhoto['alt'] }}" class="aspect-[4/5] max-h-[70vh] w-full rounded-fs-lg object-cover" loading="lazy" decoding="async">
        </div>
        <div>
            <h3 class="font-display text-4xl font-semibold text-text md:text-5xl">Owners hold the record</h3>
            <p class="mt-6 max-w-xl text-lg text-text">Keep a profile for every pet, upload health and insurance documents, book or cancel a visit, and shop from the same account.</p>
            <div class="mt-8">
                <x-ui.button href="{{ route('register') }}">Open a record</x-ui.button>
            </div>
        </div>
    </article>

    <article class="grid items-center gap-10 py-16 lg:min-h-screen lg:grid-cols-2">
        <div class="home-story-visual">
            <img src="{{ $vetPhoto['url'] }}" width="{{ $vetPhoto['width'] }}" height="{{ $vetPhoto['height'] }}" alt="{{ $vetPhoto['alt'] }}" class="aspect-[4/5] max-h-[70vh] w-full rounded-fs-lg object-cover" loading="lazy" decoding="async">
        </div>
        <div>
            <h3 class="font-display text-4xl font-semibold text-text md:text-5xl">Veterinarians answer from it</h3>
            <p class="mt-6 max-w-xl text-lg text-text">Review the history the owner already kept. Approve, reschedule, or complete the appointment, then log the treatment.</p>
            <div class="mt-8">
                <x-ui.button href="{{ route('register') }}" variant="secondary">Join as a veterinarian</x-ui.button>
            </div>
        </div>
    </article>

    <article class="grid items-center gap-10 py-16 lg:min-h-screen lg:grid-cols-2">
        <div class="home-story-visual">
            <img src="{{ $shelterPhoto['url'] }}" width="{{ $shelterPhoto['width'] }}" height="{{ $shelterPhoto['height'] }}" alt="{{ $shelterPhoto['alt'] }}" class="aspect-[4/5] max-h-[70vh] w-full rounded-fs-lg object-cover" loading="lazy" decoding="async">
        </div>
        <div>
            <h3 class="font-display text-4xl font-semibold text-text md:text-5xl">Shelters list from the same place</h3>
            <p class="mt-6 max-w-xl text-lg text-text">Publish an adoptable pet with care logs and health status, then respond when someone expresses interest.</p>
            <div class="mt-8">
                <x-ui.button href="{{ route('register') }}" variant="secondary">Join as a shelter</x-ui.button>
            </div>
        </div>
    </article>
</section>

<section class="bg-tide-deep text-text-inverse" aria-label="Live counts">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-20 sm:px-6 md:grid-cols-3 lg:px-8">
        <div>
            <div class="font-display text-5xl font-bold tabular-nums" data-count="{{ $productCount }}">{{ $productCount }}</div>
            <p class="mt-3 text-lg text-text-inverse">Products in the catalog</p>
        </div>
        <div>
            <div class="font-display text-5xl font-bold tabular-nums" data-count="{{ $vetCount }}">{{ $vetCount }}</div>
            <p class="mt-3 text-lg text-text-inverse">Veterinarians on FurShield</p>
        </div>
        <div>
            <div class="font-display text-5xl font-bold tabular-nums" data-count="{{ $adoptableCount }}">{{ $adoptableCount }}</div>
            <p class="mt-3 text-lg text-text-inverse">Animals listed for adoption</p>
        </div>
    </div>
    @if ($productCount === 0 && $vetCount === 0 && $adoptableCount === 0)
        <p class="mx-auto max-w-7xl px-4 pb-10 text-lg text-text-inverse sm:px-6 lg:px-8">The catalog, clinic list, and adoption board are empty right now.</p>
    @endif
</section>

<section class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
    <h2 class="section-title">How an owner moves through it</h2>
    <ol class="mt-14 grid gap-12 md:grid-cols-3">
        <li>
            <p class="font-display text-5xl font-semibold text-tide">1</p>
            <h3 class="mt-4 font-display text-2xl font-semibold text-text">Create the record</h3>
            <p class="mt-3 text-text">Register, then add each pet. The record is the place health notes and documents live.</p>
        </li>
        <li>
            <p class="font-display text-5xl font-semibold text-tide">2</p>
            <h3 class="mt-4 font-display text-2xl font-semibold text-text">Book care and fill a cart</h3>
            <p class="mt-3 text-text">Request a veterinarian from that record, and add essentials to a cart. Nothing is charged and nothing is shipped.</p>
        </li>
        <li>
            <p class="font-display text-5xl font-semibold text-tide">3</p>
            <h3 class="mt-4 font-display text-2xl font-semibold text-text">Follow an adoption</h3>
            <p class="mt-3 text-text">Read a shelter listing, including health status, and send interest. The shelter answers from the same board.</p>
        </li>
    </ol>
</section>

<section class="bg-bloom text-text">
    <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-6 px-4 py-10 sm:px-6 md:flex-row md:items-center lg:px-8">
        <p class="max-w-3xl font-display text-3xl font-semibold leading-tight md:text-4xl">Pet in trouble? Check symptoms in 60 seconds</p>
        <x-ui.button :href="route('triage')">Check symptoms</x-ui.button>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
    <h2 class="section-title max-w-3xl">Sample voices, not real customers</h2>
    <p class="section-copy">These lines show how the three roles talk about the record. They are illustrations. Nobody said them.</p>
    <div class="mt-14 grid gap-12 lg:grid-cols-3">
        <blockquote class="border-t border-border pt-8">
            <p class="font-display text-2xl font-semibold leading-snug text-text">I keep each pet’s visits and documents in one record, then book the next appointment from that same page.</p>
            <footer class="mt-6 text-text">Sample owner</footer>
        </blockquote>
        <blockquote class="border-t border-border pt-8">
            <p class="font-display text-2xl font-semibold leading-snug text-text">I open the record the owner already keeps, then approve the visit and log what I treated.</p>
            <footer class="mt-6 text-text">Sample veterinarian</footer>
        </blockquote>
        <blockquote class="border-t border-border pt-8">
            <p class="font-display text-2xl font-semibold leading-snug text-text">I list an animal with the care we have already given, and read adoption interest in the same place.</p>
            <footer class="mt-6 text-text">Sample shelter</footer>
        </blockquote>
    </div>
</section>

<section class="mx-auto max-w-3xl px-4 pb-24 sm:px-6 lg:px-8" x-data="{ open: null }">
    <h2 class="section-title">Questions before you start a record</h2>
    <div class="mt-10 border-t border-border">
        @foreach ($faqs as [$id, $question, $answer])
            <div class="border-b border-border">
                <h3>
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-6 py-5 text-left font-display text-xl font-semibold text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus"
                        @click="open = open === '{{ $id }}' ? null : '{{ $id }}'"
                        :aria-expanded="(open === '{{ $id }}').toString()"
                        aria-controls="faq-{{ $id }}"
                    >
                        <span>{{ $question }}</span>
                        <span class="motion-safe:transition motion-safe:duration-300" :class="open === '{{ $id }}' ? 'rotate-180' : ''">
                            <x-ui.icon name="chevron-down" />
                        </span>
                    </button>
                </h3>
                <div id="faq-{{ $id }}" role="region" x-show="open === '{{ $id }}'" x-cloak>
                    <p class="max-w-2xl pb-5 text-text">{{ $answer }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

<section class="bg-tide-deep text-text-inverse">
    <div class="mx-auto flex max-w-7xl flex-col items-start gap-8 px-4 py-24 sm:px-6 lg:px-8">
        <h2 class="max-w-3xl font-display text-4xl font-semibold leading-tight md:text-6xl">Start the record. The rest of the care follows it.</h2>
        <p class="max-w-xl text-lg text-text-inverse">Register as an owner, veterinarian, or shelter. The pet stays the shared object.</p>
        <div class="flex flex-wrap gap-4">
            <x-ui.button href="{{ route('register') }}">Create an account</x-ui.button>
            <x-ui.button href="{{ route('adopt.index') }}" variant="secondary">See animals waiting</x-ui.button>
        </div>
    </div>
</section>
@endsection
