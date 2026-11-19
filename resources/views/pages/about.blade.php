@extends('layouts.app')
@section('title', 'About Us')
@section('content')
<section class="relative mx-auto mt-24 w-[calc(100%-1rem)] max-w-[90rem] overflow-hidden rounded-fs-lg border border-white/10 bg-[#02181e] pb-16 text-white shadow-fs-lg sm:w-[calc(100%-1.5rem)]">
    <img src="{{ asset('images/catalog-two-dogs.jpg') }}" alt="" width="1200" height="801" class="absolute inset-0 h-full w-full object-cover opacity-40" decoding="async">
    <div class="relative z-10 h-1 bg-tide" aria-hidden="true"></div>
    <div class="relative z-10 mx-auto max-w-4xl px-4 pb-4 pt-12 sm:px-6">
        <div class="font-display text-6xl font-extrabold" data-reveal>FurShield</div>
        <p class="mt-6 max-w-2xl text-lg text-white/80" data-reveal>
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
