@extends('layouts.app')

@section('title', 'Home')

@section('content')
@php
    $recordPhoto = \App\Support\DummyImages::describe(null, 'pet', 0);
    $visitPhoto = \App\Support\DummyImages::describe(null, 'care', 2);
    $shopPhoto = \App\Support\DummyImages::describe(null, 'product', 0);
    $adoptPhoto = \App\Support\DummyImages::describe(null, 'adoption', 0);
    $ownerPhoto = \App\Support\DummyImages::describe(null, 'role', 0);
    $vetPhoto = \App\Support\DummyImages::describe(null, 'role', 1);
    $shelterPhoto = \App\Support\DummyImages::describe(null, 'role', 2);
    $cardClass = 'group flex h-full flex-col overflow-hidden rounded-fs-lg border border-border bg-surface-raised focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus focus-visible:ring-offset-2 focus-visible:ring-offset-surface motion-safe:transition motion-safe:duration-fs-fast motion-safe:ease-fs-standard hover:border-border-strong';
    $jobs = [
        ['register', 'One record for each pet', 'Identity, health notes, and documents stay together.', $recordPhoto],
        ['register', 'Book a visit', 'Request a time. The veterinarian approves it, reschedules it, or logs the treatment.', $visitPhoto],
        ['products.index', 'Shop essentials', 'Food, grooming, toys, and supplies. Checkout keeps pickup or delivery with the order.', $shopPhoto],
        ['adopt.index', 'Animals waiting', 'Shelters list pets with health status. An owner can send interest from the listing.', $adoptPhoto],
    ];
    $faqs = [
        ['pay', 'Can I pay inside FurShield?', 'Yes. Checkout accepts a card or pay on collection. The full card number is not stored.'],
        ['delivery', 'Will a product be delivered?', 'Checkout can record pickup or delivery and keeps the address with the order. FurShield does not run a courier.'],
        ['pets', 'Can one owner keep more than one pet?', 'Yes. Each pet has its own record: identity, health notes, and documents.'],
        ['record', 'Who works from that record?', 'The owner holds it. A veterinarian uses it for appointments and treatments. A shelter uses it when listing an animal and answering adoption interest.'],
        ['visit', 'What happens when I book a visit?', 'You request a time. The veterinarian can approve it, reschedule it, or complete it, and can log the treatment afterward.'],
        ['voices', 'Are the voices on this page real customers?', 'No. They are sample illustrations of the three roles. FurShield has no verified customer quotes.'],
    ];
@endphp

<section data-hero class="relative isolate mx-auto mt-24 flex h-auto min-h-0 w-[calc(100%-1rem)] max-h-none max-w-[90rem] flex-col overflow-hidden rounded-fs-lg bg-[#02181e] shadow-fs-lg sm:w-[calc(100%-1.5rem)] lg:h-[calc(100svh-7.5rem)] lg:min-h-[560px] lg:max-h-[760px]">
    <img
        src="{{ asset('images/hero-scene.jpg') }}"
        width="1920"
        height="1080"
        alt="A veterinarian with a golden retriever and a kitten in a clinic"
        class="relative h-auto w-full object-contain object-center lg:absolute lg:inset-0 lg:h-full lg:object-cover"
        fetchpriority="high"
        decoding="async"
    >
    <div class="pointer-events-none absolute inset-0 hidden bg-[linear-gradient(90deg,#02181e_0%,rgba(2,24,30,0.92)_34%,rgba(2,24,30,0.55)_52%,rgba(2,24,30,0.12)_68%,transparent_78%)] lg:block"></div>
    <div class="pointer-events-none absolute bottom-0 left-0 hidden h-24 w-[42%] rounded-[100%] bg-[#14b8aa]/30 blur-2xl lg:block"></div>

    <div class="relative flex w-full items-center justify-start px-6 py-8 sm:px-10 lg:flex-1 lg:px-16 lg:py-10">
        <div class="max-w-xl text-left">
            <p class="inline-flex h-8 items-center gap-2 rounded-full border border-[#2ee6d6]/80 bg-[#02181e]/40 px-3 text-[13px] font-medium text-[#d9fffb]">
                <x-ui.icon name="heart" size="sm" class="text-[#2ee6d6]" />
                Healthy Pets. Happier Lives.
            </p>
            <h1 class="mt-5 font-hero text-[clamp(3.25rem,6.4vw,6.25rem)] font-extrabold italic leading-[0.9] tracking-tight text-white">
                <span>Fur</span><span class="text-[#2ee6d6]">Shield</span>
            </h1>
            <p class="mt-4 font-display text-2xl font-bold leading-tight tracking-tight text-white sm:text-[1.75rem]">
                Complete <span class="relative inline-block text-[#2ee6d6]">Pet Care<svg class="absolute -bottom-1 left-0 h-2 w-full text-[#2ee6d6]" viewBox="0 0 140 8" fill="none" aria-hidden="true"><path d="M2 5c28-4 52-4 78-1s40 3 58 0" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg></span>, All in One Place
            </p>
            <p class="mt-4 max-w-[40ch] text-base leading-7 text-white/90">Every paw and wing deserves a shield of love — manage health, book care, shop essentials and find forever homes in one calm place.</p>
            <div class="mt-6 flex flex-wrap items-center justify-start gap-3">
                <a href="{{ route('register') }}" class="inline-flex h-12 items-center gap-2 rounded-full bg-[#2ee6d6] px-5 text-[15px] font-semibold text-[#04222a] shadow-[0_10px_30px_rgba(46,230,214,0.35)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                    <x-ui.icon name="paw-print" size="sm" />
                    Start Protecting
                    <x-ui.icon name="chevron-right" size="sm" />
                </a>
                <a href="{{ route('products.index') }}" class="inline-flex h-12 items-center gap-2 rounded-full border border-white/80 px-5 text-[15px] font-semibold text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                    <x-ui.icon name="shopping-bag" size="sm" />
                    Shop Pet Essentials
                </a>
            </div>
            <ul class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-white/85">
                <li class="inline-flex items-center gap-2"><x-ui.icon name="shield" size="sm" /> Trusted Care</li>
                <li class="hidden h-4 w-px bg-white/30 sm:block" aria-hidden="true"></li>
                <li class="inline-flex items-center gap-2"><x-ui.icon name="heart" size="sm" /> Expert Vets</li>
                <li class="hidden h-4 w-px bg-white/30 sm:block" aria-hidden="true"></li>
                <li class="inline-flex items-center gap-2"><x-ui.icon name="house" size="sm" /> Adopt &amp; Love</li>
            </ul>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" aria-labelledby="jobs-heading">
    <div class="max-w-2xl">
        <h2 id="jobs-heading" class="font-display text-3xl font-semibold tracking-tight text-text">What stays on the record</h2>
        <p class="mt-3 max-w-[52ch] text-base leading-relaxed text-text">Appointments, shopping, and adoption are not separate products. Each one opens from the pet the owner already keeps.</p>
    </div>
    <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($jobs as [$route, $title, $copy, $photo])
            <a href="{{ route($route) }}" class="{{ $cardClass }}">
                <img src="{{ $photo['url'] }}" width="{{ $photo['width'] }}" height="{{ $photo['height'] }}" alt="" class="h-40 w-full bg-surface-sunken object-contain object-center" @if (! $loop->first) loading="lazy" @endif decoding="async">
                <div class="flex flex-1 flex-col p-5">
                    <h3 class="font-display text-xl font-semibold text-text group-hover:underline">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-text">{{ $copy }}</p>
                </div>
            </a>
        @endforeach
    </div>
    <a href="{{ route('care.index') }}" class="mt-4 flex items-center justify-between gap-4 rounded-fs-lg border border-border bg-surface-raised px-5 py-4 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus focus-visible:ring-offset-2">
        <span>
            <span class="block font-display text-lg font-semibold text-text">Care library</span>
            <span class="mt-1 block text-sm leading-relaxed text-text">Feeding, hygiene, exercise, and training, written for the household that holds the record.</span>
        </span>
        <x-ui.icon name="chevron-right" class="shrink-0 text-success" />
    </a>
</section>

<section aria-labelledby="roles-heading">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 id="roles-heading" class="font-display text-3xl font-semibold tracking-tight text-text">Three people, one record</h2>
        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article class="flex h-full flex-col rounded-fs-lg border border-border bg-surface-raised p-4 shadow-fs-sm">
                <img src="{{ $ownerPhoto['url'] }}" width="{{ $ownerPhoto['width'] }}" height="{{ $ownerPhoto['height'] }}" alt="{{ $ownerPhoto['alt'] }}" class="aspect-[4/3] w-full rounded-fs-lg bg-surface-sunken object-contain object-center" loading="lazy" decoding="async">
                <h3 class="mt-5 font-display text-2xl font-semibold text-text">Owners hold the record</h3>
                <p class="mt-2 flex-1 text-base leading-relaxed text-text">Keep a profile for every pet, upload health and insurance documents, book or cancel a visit, and shop from the same account.</p>
                <div class="mt-5">
                    <x-ui.button href="{{ route('register') }}">Open a record</x-ui.button>
                </div>
            </article>
            <article class="flex h-full flex-col rounded-fs-lg border border-border bg-surface-raised p-4 shadow-fs-sm">
                <img src="{{ $vetPhoto['url'] }}" width="{{ $vetPhoto['width'] }}" height="{{ $vetPhoto['height'] }}" alt="{{ $vetPhoto['alt'] }}" class="aspect-[4/3] w-full rounded-fs-lg bg-surface-sunken object-contain object-center" loading="lazy" decoding="async">
                <h3 class="mt-5 font-display text-2xl font-semibold text-text">Veterinarians answer from it</h3>
                <p class="mt-2 flex-1 text-base leading-relaxed text-text">Review the history the owner already kept. Approve, reschedule, or complete the appointment, then log the treatment.</p>
                <div class="mt-5">
                    <x-ui.button href="{{ route('register') }}">Join as a veterinarian</x-ui.button>
                </div>
            </article>
            <article class="flex h-full flex-col rounded-fs-lg border border-border bg-surface-raised p-4 shadow-fs-sm sm:col-span-2 lg:col-span-1">
                <img src="{{ $shelterPhoto['url'] }}" width="{{ $shelterPhoto['width'] }}" height="{{ $shelterPhoto['height'] }}" alt="{{ $shelterPhoto['alt'] }}" class="aspect-[4/3] w-full rounded-fs-lg bg-surface-sunken object-contain object-center" loading="lazy" decoding="async">
                <h3 class="mt-5 font-display text-2xl font-semibold text-text">Shelters list from it</h3>
                <p class="mt-2 flex-1 text-base leading-relaxed text-text">Publish an adoptable pet with care logs and health status, then respond when someone expresses interest.</p>
                <div class="mt-5">
                    <x-ui.button href="{{ route('register') }}">Join as a shelter</x-ui.button>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" aria-label="Live counts">
    <div class="grid overflow-hidden rounded-fs-lg border border-border bg-surface-raised shadow-fs-sm sm:grid-cols-3">
        <div class="px-4 py-10 sm:border-r sm:border-border sm:px-6 lg:px-8">
            <p class="font-display text-3xl font-semibold tabular-nums text-text">{{ $productCount }}</p>
            <p class="mt-1 text-sm text-text">Products in the catalog</p>
        </div>
        <div class="border-t border-border px-4 py-10 sm:border-r sm:border-t-0 sm:px-6 lg:px-8">
            <p class="font-display text-3xl font-semibold tabular-nums text-text">{{ $vetCount }}</p>
            <p class="mt-1 text-sm text-text">Veterinarians on FurShield</p>
        </div>
        <div class="border-t border-border px-4 py-10 sm:border-t-0 sm:px-6 lg:px-8">
            <p class="font-display text-3xl font-semibold tabular-nums text-text">{{ $adoptableCount }}</p>
            <p class="mt-1 text-sm text-text">Animals listed for adoption</p>
        </div>
    </div>
    @if ($productCount === 0 && $vetCount === 0 && $adoptableCount === 0)
        <p class="mx-auto max-w-7xl px-4 pb-8 text-sm text-text sm:px-6 lg:px-8">The catalog, clinic list, and adoption board are empty right now.</p>
    @endif
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" aria-labelledby="path-heading">
    <h2 id="path-heading" class="font-display text-3xl font-semibold tracking-tight text-text">How an owner moves through it</h2>
    <ol class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <li class="rounded-fs-lg border border-border bg-surface-raised p-5 shadow-fs-sm sm:col-span-1">
            <p class="text-sm font-medium text-success">01</p>
            <h3 class="mt-2 font-display text-xl font-semibold text-text">Create the record</h3>
            <p class="mt-2 text-sm leading-relaxed text-text">Register, then add each pet. The record is where health notes and documents live.</p>
        </li>
        <li class="rounded-fs-lg border border-border bg-surface-raised p-5 shadow-fs-sm">
            <p class="text-sm font-medium text-success">02</p>
            <h3 class="mt-2 font-display text-xl font-semibold text-text">Book care and fill a cart</h3>
            <p class="mt-2 text-sm leading-relaxed text-text">Request a veterinarian from that record, then check out supplies for pickup or delivery. Card numbers are not stored.</p>
        </li>
        <li class="rounded-fs-lg border border-border bg-surface-raised p-5 shadow-fs-sm sm:col-span-2 lg:col-span-1">
            <p class="text-sm font-medium text-success">03</p>
            <h3 class="mt-2 font-display text-xl font-semibold text-text">Follow an adoption</h3>
            <p class="mt-2 text-sm leading-relaxed text-text">Read a shelter listing, including health status, and send interest. The shelter answers from the same board.</p>
        </li>
    </ol>
</section>

<section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
    <div class="flex flex-col items-start gap-4 rounded-fs-lg border border-border bg-surface-sunken px-5 py-5 sm:flex-row sm:items-center sm:gap-8 sm:px-6">
        <p class="max-w-xl font-display text-2xl font-semibold leading-tight text-text">Pet in trouble? Check symptoms in 60 seconds.</p>
        <x-ui.button :href="route('triage')">Check symptoms</x-ui.button>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" aria-labelledby="voices-heading">
    <h2 id="voices-heading" class="max-w-2xl font-display text-3xl font-semibold tracking-tight text-text">Sample lines, not customer quotes</h2>
    <p class="mt-3 max-w-[52ch] text-base leading-relaxed text-text">These show how the three roles talk about the record. Nobody said them.</p>
    <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <blockquote class="rounded-fs-lg border border-border bg-surface-raised p-5 shadow-fs-sm">
            <p class="text-base leading-relaxed text-text">I keep each pet’s visits and documents in one record, then book the next appointment from that same page.</p>
            <footer class="mt-3 text-sm font-medium text-text">Sample owner</footer>
        </blockquote>
        <blockquote class="rounded-fs-lg border border-border bg-surface-raised p-5 shadow-fs-sm">
            <p class="text-base leading-relaxed text-text">I open the record the owner already keeps, then approve the visit and log what I treated.</p>
            <footer class="mt-3 text-sm font-medium text-text">Sample veterinarian</footer>
        </blockquote>
        <blockquote class="rounded-fs-lg border border-border bg-surface-raised p-5 shadow-fs-sm sm:col-span-2 lg:col-span-1">
            <p class="text-base leading-relaxed text-text">I list an animal with the care we have already given, and read adoption interest in the same place.</p>
            <footer class="mt-3 text-sm font-medium text-text">Sample shelter</footer>
        </blockquote>
    </div>
</section>

<section class="mx-auto max-w-3xl px-4 pb-16 sm:px-6 lg:px-8" x-data="{ open: null }" aria-labelledby="faq-heading">
    <div class="rounded-fs-lg border border-border bg-surface-raised px-5 py-6 shadow-fs-sm sm:px-6">
    <h2 id="faq-heading" class="font-display text-3xl font-semibold tracking-tight text-text">Questions before you start a record</h2>
    <div class="mt-4">
        @foreach ($faqs as [$id, $question, $answer])
            <div class="border-b border-border">
                <h3>
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-4 py-4 text-left font-display text-lg font-semibold text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus"
                        @click="open = open === '{{ $id }}' ? null : '{{ $id }}'"
                        :aria-expanded="(open === '{{ $id }}').toString()"
                        aria-controls="faq-{{ $id }}"
                    >
                        <span>{{ $question }}</span>
                        <span class="grid size-11 shrink-0 place-items-center rounded-full bg-surface-sunken text-text motion-safe:transition motion-safe:duration-300" :class="open === '{{ $id }}' ? 'rotate-180' : ''">
                            <x-ui.icon name="chevron-down" size="lg" class="stroke-[2.5]" />
                        </span>
                    </button>
                </h3>
                <div id="faq-{{ $id }}" role="region" x-show="open === '{{ $id }}'" x-cloak>
                    <p class="max-w-2xl pb-4 text-base leading-relaxed text-text">{{ $answer }}</p>
                </div>
            </div>
        @endforeach
    </div>
    </div>
</section>

<section class="mx-auto w-[calc(100%-1rem)] max-w-[90rem] pb-16 sm:w-[calc(100%-1.5rem)]" aria-labelledby="pocket-heading">
    <div class="relative overflow-hidden rounded-fs-lg bg-[#02181e] px-5 py-10 text-white shadow-fs-lg sm:px-8 sm:py-14 lg:px-12">
        <img src="{{ asset('images/catalog-outdoors.jpg') }}" alt="" width="1200" height="1797" class="pointer-events-none absolute -left-10 top-0 h-56 w-40 object-cover opacity-30" loading="lazy" decoding="async">
        <img src="{{ asset('images/catalog-kitten.jpg') }}" alt="" width="1200" height="800" class="pointer-events-none absolute -right-8 bottom-0 h-48 w-40 object-cover opacity-25" loading="lazy" decoding="async">
        <div class="relative grid items-stretch gap-8 lg:grid-cols-[minmax(14rem,17rem)_minmax(0,1fr)_16rem] lg:gap-12">
            <div class="relative mx-auto h-[22rem] w-full max-w-[13rem] lg:mx-0 lg:h-auto lg:max-w-none">
                <div class="h-full lg:absolute lg:inset-0">
                    <x-public.phone-home />
                </div>
            </div>
            <div>
                <p class="text-sm font-semibold text-[#2ee6d6]">FurShield on your phone</p>
                <h2 id="pocket-heading" class="mt-3 max-w-md font-display text-4xl font-bold leading-tight tracking-tight sm:text-5xl">Pet care, ready in your hand</h2>
                <p class="mt-4 max-w-lg text-base leading-relaxed text-white/85">Open the record, book a visit, shop essentials, and follow an adoption from your phone.</p>
                <ul class="mt-6 grid gap-3 text-sm sm:grid-cols-2">
                    @foreach (['One record for every pet', 'Book a visit from that record', 'Shop food, grooming, and supplies', 'Tell a shelter you are interested'] as $point)
                        <li class="flex items-center gap-2">
                            <x-ui.icon name="circle-check" size="sm" class="shrink-0 text-[#2ee6d6]" />
                            <span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6 flex flex-wrap gap-3">
                    <div class="inline-flex min-h-12 items-center gap-2.5 rounded-lg bg-white px-3 py-1.5 text-[#102027]">
                        <svg viewBox="0 0 24 24" class="size-6 shrink-0" aria-hidden="true">
                            <path fill="currentColor" d="M16.37 12.72c-.03-2.3 1.88-3.4 1.97-3.46-1.08-1.57-2.75-1.79-3.34-1.81-1.4-.15-2.76.84-3.48.84-.73 0-1.84-.82-3.03-.8-1.55.02-3 .92-3.8 2.33-1.64 2.84-.42 7.02 1.16 9.32.78 1.13 1.7 2.39 2.91 2.34 1.17-.05 1.61-.75 3.03-.75 1.41 0 1.81.75 3.04.72 1.26-.02 2.06-1.14 2.82-2.27.9-1.3 1.26-2.57 1.28-2.63-.03-.01-2.45-.94-2.48-3.73zM14.7 6.3c.63-.78 1.06-1.86.94-2.94-.91.04-2.02.62-2.67 1.39-.58.68-1.1 1.78-.96 2.82 1.02.08 2.06-.51 2.69-1.27z"/>
                        </svg>
                        <span class="text-left leading-none">
                            <span class="block text-[10px] font-medium tracking-wide">Available on the</span>
                            <span class="mt-1 block font-display text-[15px] font-semibold tracking-tight">App Store</span>
                        </span>
                    </div>
                    <div class="inline-flex min-h-12 items-center gap-2.5 rounded-lg bg-white px-3 py-1.5 text-[#102027]">
                        <svg viewBox="0 0 24 24" class="size-6 shrink-0" aria-hidden="true">
                            <path fill="#34A853" d="M3.5 20.4 13.2 12 3.5 3.6c-.3.4-.5.9-.5 1.5v13.8c0 .6.2 1.1.5 1.5z"/>
                            <path fill="#FBBC04" d="M16.7 15.2 13.2 12 3.5 20.4c.5.5 1.2.6 2 .2l11.2-5.4z"/>
                            <path fill="#EA4335" d="M20.2 10.7 16.7 8.8 13.2 12l3.5 3.2 3.5-1.7c.8-.4.8-1.4 0-1.8z"/>
                            <path fill="#4285F4" d="M3.5 3.6c.5-.5 1.2-.6 2-.2l11.2 5.4L13.2 12 3.5 3.6z"/>
                        </svg>
                        <span class="text-left leading-none">
                            <span class="block text-[10px] font-medium tracking-wide">Available on</span>
                            <span class="mt-1 block font-display text-[15px] font-semibold tracking-tight">Google Play</span>
                        </span>
                    </div>
                </div>
                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <span class="text-sm font-semibold">Open it now</span>
                    <a href="{{ route('register') }}" class="inline-flex h-12 items-center gap-2 rounded-lg bg-[#04222a] px-3 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        <x-ui.icon name="paw-print" size="sm" class="text-[#2ee6d6]" />
                        <span class="text-left leading-tight">
                            <span class="block text-[10px] text-white/70">Start a record</span>
                            <span class="block text-sm font-semibold">Create an account</span>
                        </span>
                    </a>
                    <a href="{{ route('products.index') }}" class="inline-flex h-12 items-center gap-2 rounded-lg bg-[#04222a] px-3 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        <x-ui.icon name="shopping-bag" size="sm" class="text-[#2ee6d6]" />
                        <span class="text-left leading-tight">
                            <span class="block text-[10px] text-white/70">From the same record</span>
                            <span class="block text-sm font-semibold">Shop essentials</span>
                        </span>
                    </a>
                </div>
            </div>
            <a href="{{ route('vets.index') }}" class="group relative mx-auto block w-full max-w-xs overflow-hidden rounded-fs-lg border border-white/15 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                <img src="{{ asset('images/catalog-clinician.jpg') }}" width="1200" height="800" alt="A clinician in a white coat standing outdoors" class="aspect-[4/3] w-full object-cover" loading="lazy" decoding="async">
                <span class="absolute inset-x-0 bottom-0 bg-[#02181e]/75 px-4 py-3 text-sm font-semibold">Find a veterinarian</span>
            </a>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-fs-lg border border-border bg-surface-raised shadow-fs-sm">
        <div class="h-1 bg-tide" aria-hidden="true"></div>
        <div class="flex flex-col items-start gap-6 px-5 py-10 sm:px-8">
        <h2 class="max-w-xl font-display text-3xl font-semibold leading-tight text-text">Start the record. The rest of the care follows it.</h2>
        <p class="max-w-xl text-base leading-relaxed text-text">Register as an owner, veterinarian, or shelter. The pet stays the shared object.</p>
        <div class="flex flex-wrap gap-3">
            <x-ui.button href="{{ route('register') }}">Create an account</x-ui.button>
            <x-ui.button href="{{ route('adopt.index') }}" variant="secondary">See animals waiting</x-ui.button>
        </div>
        </div>
    </div>
</section>
@endsection
