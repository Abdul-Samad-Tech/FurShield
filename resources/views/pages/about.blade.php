@extends('layouts.app')
@section('title', 'About Us')
@section('content')
<section class="relative overflow-hidden bg-hero-mesh pb-24 pt-32 text-white">
    <div class="absolute inset-0 bg-cover bg-center opacity-30" style="background-image: url('{{ asset('images/catalog-two-dogs.jpg') }}')"></div>
    <div class="relative mx-auto max-w-4xl px-4 sm:px-6">
        <div class="font-display text-6xl font-extrabold" data-reveal>FurShield</div>
        <p class="mt-6 max-w-2xl text-lg text-white/80" data-reveal>
            We built FurShield so every pet owner, veterinarian, and shelter can share one clear source of truth for care, records, and compassion.
        </p>
    </div>
</section>
<section class="mx-auto max-w-5xl px-4 py-20 sm:px-6">
    <div class="grid gap-8 md:grid-cols-3">
        @foreach ([['Care first','Health timelines and reminders keep families ready.'],['Connected roles','Owners, vets, and shelters collaborate without chaos.'],['Thoughtful commerce','Browse essentials without forced payment friction.']] as $i => [$title, $copy])
            <div class="surface p-6" data-reveal>
                <h2 class="font-display text-2xl font-semibold">{{ $title }}</h2>
                <p class="mt-3 text-ink-mute">{{ $copy }}</p>
            </div>
        @endforeach
    </div>
</section>
@endsection
