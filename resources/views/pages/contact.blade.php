@extends('layouts.app')
@section('title', 'Contact')
@section('content')
<section class="mx-auto max-w-6xl px-4 pb-20 pt-28 sm:px-6 lg:px-8">
    <div data-reveal>
        <h1 class="font-display text-5xl font-bold">Contact FurShield</h1>
        <p class="section-copy">Reach the team or find us on the map. Static contact details for evaluation and support.</p>
    </div>
    <div class="mt-10 grid gap-8 lg:grid-cols-2">
        <div class="surface space-y-4 p-8" data-reveal="right">
            <div><div class="text-sm text-ink-mute">Email</div><div class="font-medium">hello@furshield.test</div></div>
            <div><div class="text-sm text-ink-mute">Phone</div><div class="font-medium">{{ \App\Support\Furshield::supportPhone() }}</div></div>
            <div><div class="text-sm text-ink-mute">Address</div><div class="font-medium">FurShield HQ, Clifton Block 5, Karachi, Pakistan</div></div>
            <div><div class="text-sm text-ink-mute">Hours</div><div class="font-medium">Mon–Sat · 9:00 AM – 6:00 PM</div></div>
        </div>
        <div class="surface overflow-hidden" data-reveal="left">
            <iframe
                title="FurShield location map"
                class="h-[360px] w-full border-0"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.openstreetmap.org/export/embed.html?bbox=67.015%2C24.805%2C67.045%2C24.825&layer=mapnik&marker=24.815%2C67.030">
            </iframe>
        </div>
    </div>
</section>
@endsection
