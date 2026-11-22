@extends('layouts.app')
@section('title', 'About Us')
@section('content')
<section class="relative mx-auto mt-24 grid w-[calc(100%-1rem)] max-w-[90rem] overflow-hidden rounded-fs-lg border border-white/10 bg-[#02181e] text-white shadow-fs-lg sm:w-[calc(100%-1.5rem)] lg:grid-cols-2">
    <div class="relative aspect-[3/2] bg-[#02181e] lg:order-2">
        <img src="{{ asset('images/catalog-two-dogs.jpg') }}" alt="" width="1200" height="801" class="absolute inset-0 h-full w-full object-contain object-center" decoding="async">
    </div>
    <div class="relative z-10 flex flex-col justify-end px-6 py-10 sm:px-10">
        <div class="font-display text-5xl font-extrabold sm:text-6xl" data-reveal>FurShield</div>
        <p class="mt-6 max-w-xl text-lg text-white/80" data-reveal>
            We built FurShield so every pet owner, veterinarian, and shelter can share one clear source of truth for care, records, and compassion.
        </p>
    </div>
</section>
<section class="mx-auto max-w-5xl px-4 py-20 sm:px-6">
    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ([['Care first','Health timelines and reminders keep families ready.'],['Connected roles','Owners, vets, and shelters collaborate without chaos.'],['Thoughtful commerce','Browse essentials without forced payment friction.']] as $i => [$title, $copy])
            <div class="surface p-6" data-reveal>
                <h2 class="font-display text-2xl font-semibold">{{ $title }}</h2>
                <p class="mt-3 text-ink-mute">{{ $copy }}</p>
            </div>
        @endforeach
    </div>
</section>
@endsection
