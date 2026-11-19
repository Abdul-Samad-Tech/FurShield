@extends('layouts.app')
@section('title', 'Care Tips')
@section('content')
<x-public.page-hero
    title="Care library"
    copy="Articles and practical guidance for feeding, hygiene, exercise, and training."
    image="images/catalog-treat.jpg"
    alt="A golden dog being bathed"
/>
<section class="mx-auto max-w-7xl px-4 pb-20 pt-8 sm:px-6 lg:px-8">
    <form method="GET" class="surface mt-8 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3">
        <input name="q" value="{{ request('q') }}" placeholder="Search tips" class="field">
        <select name="category" class="field">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->value }}" @selected(request('category') === $category->value)>{{ $category->label() }}</option>
            @endforeach
        </select>
        <button class="btn-primary">Search</button>
    </form>
    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($articles as $i => $article)
            <a href="{{ route('care.show', $article) }}" data-reveal class="surface overflow-hidden transition hover:-translate-y-1">
                <x-card-image :src="$article->image_path" type="care" :seed="$article->id" :alt="$article->title" variant="card" />
                <div class="p-6">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-tide">{{ $article->category->label() }}</div>
                    <h2 class="mt-3 font-display text-2xl font-semibold">{{ $article->title }}</h2>
                    <p class="mt-3 text-sm text-ink-mute">{{ $article->summary }}</p>
                </div>
            </a>
        @endforeach
    </div>
    <div class="mt-8">{{ $articles->links() }}</div>
</section>
@endsection
