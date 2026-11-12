@extends('layouts.app')
@section('title', 'Adopt')
@section('content')
<section class="mx-auto max-w-7xl px-4 pb-20 pt-28 sm:px-6 lg:px-8">
    <div data-reveal>
        <h1 class="font-display text-5xl font-bold text-text">Find a forever friend</h1>
        <p class="section-copy">Browse adoptable pets from shelters and tell them you're interested. There is no adoption fee and no transport in FurShield.</p>
    </div>

    <form method="GET" class="surface mt-8 grid gap-3 p-4 md:grid-cols-3">
        @if ($answers)
            <input type="hidden" name="home" value="{{ $answers['home'] }}">
            <input type="hidden" name="kids" value="{{ $answers['kids'] }}">
            <input type="hidden" name="pets" value="{{ $answers['pets'] }}">
            <input type="hidden" name="experience" value="{{ $answers['experience'] }}">
            <input type="hidden" name="activity" value="{{ $answers['activity'] }}">
        @endif
        <div>
            <label for="adopt-search" class="text-sm font-medium text-text">Search</label>
            <input id="adopt-search" name="q" value="{{ request('q') }}" placeholder="Name or breed" class="field mt-fs-3xs">
        </div>
        <div>
            <label for="adopt-species" class="text-sm font-medium text-text">Species</label>
            <input id="adopt-species" name="species" value="{{ request('species') }}" placeholder="Dog, cat…" class="field mt-fs-3xs">
        </div>
        <div class="flex items-end">
            <x-ui.button type="submit" class="w-full">Filter</x-ui.button>
        </div>
    </form>

    <form method="GET" class="mt-fs-md rounded-fs-lg border border-border bg-surface-raised p-fs-md" aria-labelledby="match-heading">
        @if (request()->filled('q'))
            <input type="hidden" name="q" value="{{ request('q') }}">
        @endif
        @if (request()->filled('species'))
            <input type="hidden" name="species" value="{{ request('species') }}">
        @endif
        <div class="flex flex-wrap items-end justify-between gap-fs-sm">
            <div>
                <h2 id="match-heading" class="font-display text-2xl font-semibold text-text">Find my match</h2>
                <p class="mt-fs-3xs max-w-2xl text-sm text-text">Five questions sort the deck. The percentage comes from each listing's species, age, and description. It is a guide, not a promise about the pet.</p>
            </div>
            @if ($answers)
                <a href="{{ route('adopt.index', request()->only(['q', 'species'])) }}" class="text-sm font-medium text-text underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">Clear match</a>
            @endif
        </div>
        <div class="mt-fs-sm grid gap-fs-sm lg:grid-cols-5">
            <fieldset>
                <legend class="text-sm font-medium text-text">Home size</legend>
                <div class="mt-fs-2xs grid gap-fs-3xs">
                    <label class="flex min-h-11 items-center gap-fs-2xs text-sm text-text"><input type="radio" name="home" value="apartment" class="size-4 accent-tide" @checked(($answers['home'] ?? '') === 'apartment') required> Apartment</label>
                    <label class="flex min-h-11 items-center gap-fs-2xs text-sm text-text"><input type="radio" name="home" value="house" class="size-4 accent-tide" @checked(($answers['home'] ?? '') === 'house')> House</label>
                    <label class="flex min-h-11 items-center gap-fs-2xs text-sm text-text"><input type="radio" name="home" value="either" class="size-4 accent-tide" @checked(($answers['home'] ?? '') === 'either')> Either</label>
                </div>
            </fieldset>
            <fieldset>
                <legend class="text-sm font-medium text-text">Kids at home</legend>
                <div class="mt-fs-2xs grid gap-fs-3xs">
                    <label class="flex min-h-11 items-center gap-fs-2xs text-sm text-text"><input type="radio" name="kids" value="yes" class="size-4 accent-tide" @checked(($answers['kids'] ?? '') === 'yes') required> Yes</label>
                    <label class="flex min-h-11 items-center gap-fs-2xs text-sm text-text"><input type="radio" name="kids" value="no" class="size-4 accent-tide" @checked(($answers['kids'] ?? '') === 'no')> No</label>
                    <label class="flex min-h-11 items-center gap-fs-2xs text-sm text-text"><input type="radio" name="kids" value="either" class="size-4 accent-tide" @checked(($answers['kids'] ?? '') === 'either')> Doesn't matter</label>
                </div>
            </fieldset>
            <fieldset>
                <legend class="text-sm font-medium text-text">Other pets</legend>
                <div class="mt-fs-2xs grid gap-fs-3xs">
                    <label class="flex min-h-11 items-center gap-fs-2xs text-sm text-text"><input type="radio" name="pets" value="yes" class="size-4 accent-tide" @checked(($answers['pets'] ?? '') === 'yes') required> Yes</label>
                    <label class="flex min-h-11 items-center gap-fs-2xs text-sm text-text"><input type="radio" name="pets" value="no" class="size-4 accent-tide" @checked(($answers['pets'] ?? '') === 'no')> No</label>
                    <label class="flex min-h-11 items-center gap-fs-2xs text-sm text-text"><input type="radio" name="pets" value="either" class="size-4 accent-tide" @checked(($answers['pets'] ?? '') === 'either')> Doesn't matter</label>
                </div>
            </fieldset>
            <fieldset>
                <legend class="text-sm font-medium text-text">Experience</legend>
                <div class="mt-fs-2xs grid gap-fs-3xs">
                    <label class="flex min-h-11 items-center gap-fs-2xs text-sm text-text"><input type="radio" name="experience" value="first" class="size-4 accent-tide" @checked(($answers['experience'] ?? '') === 'first') required> First pet</label>
                    <label class="flex min-h-11 items-center gap-fs-2xs text-sm text-text"><input type="radio" name="experience" value="experienced" class="size-4 accent-tide" @checked(($answers['experience'] ?? '') === 'experienced')> I've had pets</label>
                    <label class="flex min-h-11 items-center gap-fs-2xs text-sm text-text"><input type="radio" name="experience" value="either" class="size-4 accent-tide" @checked(($answers['experience'] ?? '') === 'either')> Either</label>
                </div>
            </fieldset>
            <fieldset>
                <legend class="text-sm font-medium text-text">Activity</legend>
                <div class="mt-fs-2xs grid gap-fs-3xs">
                    <label class="flex min-h-11 items-center gap-fs-2xs text-sm text-text"><input type="radio" name="activity" value="quiet" class="size-4 accent-tide" @checked(($answers['activity'] ?? '') === 'quiet') required> Quiet</label>
                    <label class="flex min-h-11 items-center gap-fs-2xs text-sm text-text"><input type="radio" name="activity" value="moderate" class="size-4 accent-tide" @checked(($answers['activity'] ?? '') === 'moderate')> Moderate</label>
                    <label class="flex min-h-11 items-center gap-fs-2xs text-sm text-text"><input type="radio" name="activity" value="high" class="size-4 accent-tide" @checked(($answers['activity'] ?? '') === 'high')> Very active</label>
                </div>
            </fieldset>
        </div>
        <x-ui.button type="submit" class="mt-fs-sm">Sort the deck</x-ui.button>
    </form>

    <div
        class="mt-fs-lg"
        x-data="fsAdopt"
        x-cloak
        data-can-interest="{{ auth()->check() && auth()->user()->isOwner() ? '1' : '0' }}"
        data-guest="{{ auth()->check() ? '0' : '1' }}"
        data-login-url="{{ route('login') }}"
    >
        <script type="application/json" id="adopt-deck">@json($deck)</script>
        <div class="flex flex-wrap items-center justify-between gap-fs-sm">
            <div class="inline-flex rounded-fs-md border border-border bg-surface-raised p-fs-3xs" role="group" aria-label="Adoption view">
                <button type="button" class="min-h-11 rounded-fs-sm px-fs-sm text-sm font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" :class="view === 'deck' ? 'bg-success-soft text-text' : 'text-text-muted'" :aria-pressed="view === 'deck' ? 'true' : 'false'" @click="setView('deck')">Deck</button>
                <button type="button" class="min-h-11 rounded-fs-sm px-fs-sm text-sm font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" :class="view === 'grid' ? 'bg-success-soft text-text' : 'text-text-muted'" :aria-pressed="view === 'grid' ? 'true' : 'false'" @click="setView('grid')">Grid</button>
            </div>
        </div>
        @if ($answers)
            <p class="mt-fs-2xs text-sm text-text">Closest matches are first.</p>
        @endif
        <p x-show="deckError" x-cloak class="mt-fs-sm rounded-fs-md border border-danger/30 bg-danger-soft px-fs-sm py-fs-xs text-sm text-text" role="alert" x-text="deckError"></p>

        <div x-show="view === 'deck'" @keydown="onKeydown($event)">
            <p class="mt-fs-sm max-w-2xl text-sm text-text">Drag a card right if you're interested, left to skip, or up for details. Left, Right, and Up do the same when this deck is focused.</p>
            <p class="mt-fs-3xs text-sm text-text" x-show="reduced" x-cloak>Reduced motion is on, so the cards stay still. Use the buttons.</p>
            <p class="sr-only" aria-live="polite" x-text="live"></p>

            <div class="relative mx-auto mt-fs-md h-[32rem] max-w-md" x-show="remaining.length > 0">
                <template x-for="(card, index) in stack()" :key="card.id">
                    <article
                        class="absolute inset-x-0 top-0 flex h-[30rem] flex-col overflow-hidden rounded-fs-lg border border-border bg-surface-raised shadow-fs-md"
                        :class="index === 0 ? (reduced ? 'z-30' : 'z-30 cursor-grab touch-none') : (index === 1 ? 'pointer-events-none z-20 translate-y-3 scale-[0.96]' : 'pointer-events-none z-10 translate-y-6 scale-[0.92]')"
                        :data-deck-top="index === 0 ? 'true' : false"
                        @pointerdown="index === 0 && onPointerDown($event)"
                        @pointermove="index === 0 && onPointerMove($event)"
                        @pointerup="index === 0 && onPointerUp($event)"
                        @pointercancel="index === 0 && onPointerUp($event)"
                        :tabindex="index === 0 ? 0 : -1"
                        :aria-label="index === 0 ? card.name + ' adoption card' : null"
                    >
                        <div class="relative h-52 shrink-0">
                            <img :src="card.image" :alt="card.name" class="h-full w-full object-cover" draggable="false">
                            <span data-stamp="right" class="absolute left-fs-sm top-fs-sm rounded-fs-xs bg-success-soft px-fs-2xs py-fs-3xs text-sm font-medium text-text opacity-0" aria-hidden="true">Interested</span>
                            <span data-stamp="left" class="absolute right-fs-sm top-fs-sm rounded-fs-xs bg-danger-soft px-fs-2xs py-fs-3xs text-sm font-medium text-text opacity-0" aria-hidden="true">Skip</span>
                            <span data-stamp="up" class="absolute left-1/2 top-fs-sm -translate-x-1/2 rounded-fs-xs bg-warning-soft px-fs-2xs py-fs-3xs text-sm font-medium text-text opacity-0" aria-hidden="true">Details</span>
                        </div>
                        <div class="flex flex-1 flex-col gap-fs-2xs p-fs-sm">
                            <div class="flex items-start justify-between gap-fs-sm">
                                <h2 class="font-display text-3xl font-semibold text-text" x-text="card.name"></h2>
                                <span class="shrink-0 rounded-fs-xs bg-surface-sunken px-fs-2xs py-fs-3xs text-xs font-medium text-text" x-text="card.status"></span>
                            </div>
                            <p class="text-sm text-text" x-show="card.match !== null" x-text="card.match + '% match'"></p>
                            <p class="text-sm text-text-muted" x-text="[card.species, card.breed, card.age === null ? '' : card.age + ' years'].filter(Boolean).join(' · ')"></p>
                            <p class="text-sm text-text" x-text="card.shelter"></p>
                            <p class="line-clamp-3 text-sm text-text" x-text="card.description"></p>
                        </div>
                    </article>
                </template>
            </div>

            <div x-show="listings.length === 0" x-cloak class="mt-fs-md">
                <x-ui.empty-state title="No pets to adopt" icon="paw-print" description="Nothing matches that search. Clear the filters or check again after a shelter adds a listing." />
            </div>

            <div x-show="listings.length > 0 && remaining.length === 0" x-cloak class="mt-fs-md">
                <x-ui.empty-state title="You've seen this page" icon="paw-print" description="Undo the last card, switch to the grid, or open the next page of listings." />
            </div>

            <div class="mx-auto mt-fs-sm flex max-w-md flex-wrap items-center justify-center gap-fs-2xs" x-show="listings.length > 0">
                <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-fs-md border border-border-strong bg-surface-raised px-fs-md text-sm font-medium text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="act('left')">Skip</button>
                <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-fs-md border border-border-strong bg-surface-raised px-fs-md text-sm font-medium text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="act('up')">Details</button>
                <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-fs-md bg-success px-fs-md text-sm font-medium text-text-inverse focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="act('right')">Interested</button>
                <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-fs-md px-fs-md text-sm font-medium text-text underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus disabled:cursor-not-allowed disabled:opacity-50" @click="undo()" x-bind:disabled="history.length === 0">Undo</button>
            </div>
        </div>

        <div x-show="view === 'grid'" x-cloak>
            @include('adoption.partials.grid')
        </div>

        <div
            x-show="sheet !== ''"
            x-cloak
            class="fixed inset-0 z-[70] flex items-end justify-center"
            @keydown.escape.window="if (sheet !== '' && !$store.palette.open) closeSheet()"
        >
            <div class="absolute inset-0 bg-text/40" @click="closeSheet()"></div>
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="adopt-sheet-title"
                tabindex="-1"
                x-ref="sheetDialog"
                class="relative max-h-[85vh] w-full max-w-lg overflow-y-auto rounded-t-fs-lg border border-border bg-surface-raised p-fs-md text-text shadow-fs-lg focus:outline-none"
                @keydown.tab="trap($event)"
            >
                <div class="mx-auto mb-fs-sm h-1.5 w-12 rounded-fs-xl bg-border-strong" aria-hidden="true"></div>
                <div class="flex items-start justify-between gap-fs-sm">
                    <h2 id="adopt-sheet-title" class="font-display text-2xl font-semibold" x-text="sheetCard ? sheetCard.name : 'Listing'"></h2>
                    <button type="button" class="grid size-11 place-items-center rounded-fs-sm text-text hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="closeSheet()" aria-label="Close">
                        <x-ui.icon name="x" />
                    </button>
                </div>
                <template x-if="sheetCard && sheet === 'details'">
                    <div class="mt-fs-sm">
                        <img :src="sheetCard.image" :alt="sheetCard.name" class="h-48 w-full rounded-fs-md object-cover">
                        <p class="mt-fs-sm text-sm text-text-muted" x-text="[sheetCard.species, sheetCard.breed, sheetCard.gender, sheetCard.age === null ? '' : sheetCard.age + ' years'].filter(Boolean).join(' · ')"></p>
                        <p class="mt-fs-2xs text-sm text-text" x-text="sheetCard.health"></p>
                        <p class="mt-fs-sm text-sm text-text" x-text="sheetCard.description"></p>
                        <p class="mt-fs-sm text-sm text-text" x-text="sheetCard.shelter + (sheetCard.city ? ' · ' + sheetCard.city : '')"></p>
                        <a :href="sheetCard.url" class="mt-fs-md inline-flex min-h-11 items-center justify-center rounded-fs-md border border-border-strong px-fs-md text-sm font-medium text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">Full listing</a>
                    </div>
                </template>
                <template x-if="sheetCard && sheet === 'interest'">
                    <div class="mt-fs-sm">
                        <p class="text-sm text-text">Tell the shelter a little about your home. Sending interest does not reserve the pet.</p>
                        <p x-show="sheetError" x-cloak class="mt-fs-sm rounded-fs-md border border-danger/30 bg-danger-soft px-fs-sm py-fs-xs text-sm text-text" role="alert" x-text="sheetError"></p>
                        <template x-if="canInterest">
                            <form method="POST" :action="sheetCard.interest_url" class="mt-fs-sm grid gap-fs-sm" @submit.prevent="submitInterest($event)">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div>
                                    <label for="adopt-interest-message" class="text-sm font-medium text-text">Message to the shelter</label>
                                    <textarea id="adopt-interest-message" name="message" rows="4" maxlength="1000" class="mt-fs-3xs min-h-28 w-full rounded-fs-md border border-border-strong bg-surface px-fs-sm py-fs-xs text-sm text-text focus:border-focus focus:outline-none focus:ring-2 focus:ring-focus/40" placeholder="Optional"></textarea>
                                </div>
                                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-fs-md bg-success px-fs-md text-sm font-medium text-text-inverse focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus disabled:opacity-60" x-bind:disabled="sending" x-text="sending ? 'Sending…' : 'Send interest'"></button>
                            </form>
                        </template>
                        <template x-if="!canInterest && guest">
                            <p class="mt-fs-sm text-sm text-text">Log in as a pet owner to tell the shelter you're interested. <a :href="loginUrl" class="font-medium underline">Log in</a></p>
                        </template>
                        <template x-if="!canInterest && !guest">
                            <p class="mt-fs-sm text-sm text-text">Only pet owner accounts can send interest.</p>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <noscript>
        @include('adoption.partials.grid')
    </noscript>
</section>
@endsection
