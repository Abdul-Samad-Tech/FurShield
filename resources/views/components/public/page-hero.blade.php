@props([
    'title',
    'copy',
    'image' => null,
    'alt' => '',
])

<section class="relative mx-auto mt-24 h-56 w-[calc(100%-1rem)] max-w-[90rem] overflow-hidden rounded-fs-lg bg-[#0A2A30] shadow-fs-lg sm:h-64 sm:w-[calc(100%-1.5rem)]">
    @if ($image)
        <img
            src="{{ asset($image) }}"
            alt="{{ $alt }}"
            width="1600"
            height="640"
            class="absolute inset-0 h-full w-full object-cover"
            fetchpriority="high"
            decoding="async"
        >
    @endif
    <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(2,24,30,0.88)_0%,rgba(2,24,30,0.55)_48%,rgba(2,24,30,0.2)_100%)]"></div>
    <div class="relative flex h-full max-w-3xl flex-col justify-end px-6 pb-8 sm:px-10">
        <h1 class="font-display text-4xl font-bold tracking-tight text-white sm:text-5xl">{{ $title }}</h1>
        <p class="mt-2 max-w-2xl text-base leading-relaxed text-white/90">{{ $copy }}</p>
    </div>
</section>
