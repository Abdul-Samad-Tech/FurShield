@props([
    'title',
    'copy',
    'image',
    'alt' => '',
])

<section class="relative mx-auto mt-24 h-72 w-[calc(100%-1rem)] max-w-[90rem] overflow-hidden rounded-fs-lg bg-[#02181e] shadow-fs-lg sm:h-96 sm:w-[calc(100%-1.5rem)] lg:h-[28rem]">
    <img
        src="{{ asset($image) }}"
        alt="{{ $alt }}"
        width="1200"
        height="800"
        class="absolute inset-0 h-full w-full object-cover object-center"
        fetchpriority="high"
        decoding="async"
    >
    <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(2,24,30,0.88)_0%,rgba(2,24,30,0.55)_48%,rgba(2,24,30,0.2)_100%)]"></div>
    <div class="relative flex h-full max-w-3xl flex-col justify-end px-6 pb-8 sm:px-10 sm:pb-10">
        <h1 class="font-display text-4xl font-bold tracking-tight text-white sm:text-5xl">{{ $title }}</h1>
        <p class="mt-3 max-w-2xl text-base leading-relaxed text-white/90">{{ $copy }}</p>
    </div>
</section>
