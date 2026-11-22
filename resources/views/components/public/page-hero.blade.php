@props([
    'title',
    'copy',
    'image' => null,
    'alt' => '',
])

<section class="relative mx-auto mt-24 grid w-[calc(100%-1rem)] max-w-[90rem] overflow-hidden rounded-fs-lg bg-[#0A2A30] text-white shadow-fs-lg sm:w-[calc(100%-1.5rem)] lg:grid-cols-2">
    @if ($image)
        <div class="relative aspect-[3/2] bg-[#02181e] lg:order-2">
            <img
                src="{{ asset($image) }}"
                alt="{{ $alt }}"
                width="1200"
                height="800"
                class="absolute inset-0 h-full w-full object-contain object-center"
                fetchpriority="high"
                decoding="async"
            >
        </div>
    @endif
    <div class="flex flex-col justify-end px-6 py-8 sm:px-10 sm:py-12">
        <h1 class="font-display text-4xl font-bold tracking-tight text-white sm:text-5xl">{{ $title }}</h1>
        <p class="mt-3 max-w-xl text-base leading-relaxed text-white/90">{{ $copy }}</p>
    </div>
</section>
