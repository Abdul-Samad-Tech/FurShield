@extends('layouts.app')
@section('title', 'About Us')
@section('content')
<x-public.page-hero
    title="FurShield"
    copy="We built FurShield so every pet owner, veterinarian, and shelter can share one clear source of truth for care, records, and compassion."
    image="images/catalog-two-dogs.jpg"
    alt="A corgi and a small terrier running on a dirt path"
/>
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
