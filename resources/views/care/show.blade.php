@extends('layouts.app')
@section('title', $article->title)
@section('content')
<article class="mx-auto max-w-3xl px-4 pb-20 pt-28 sm:px-6">
    <div class="overflow-hidden rounded-[1.75rem]" data-reveal>
        <x-card-image :src="$article->image_path" type="care" :seed="$article->id" :alt="$article->title" variant="hero" />
    </div>
    <div class="mt-8 text-xs font-semibold uppercase tracking-[0.18em] text-tide" data-reveal>{{ $article->category->label() }}</div>
    <h1 class="mt-3 font-display text-5xl font-bold" data-reveal>{{ $article->title }}</h1>
    <p class="mt-6 text-lg text-ink-mute" data-reveal>{{ $article->summary }}</p>
    <div class="prose prose-lg mt-10 max-w-none text-ink-soft" data-reveal>
        {!! nl2br(e($article->body)) !!}
    </div>
    @if ($article->video_url)
        <a href="{{ $article->video_url }}" target="_blank" class="btn-primary mt-8 inline-flex">Watch video</a>
    @endif
</article>
@endsection
