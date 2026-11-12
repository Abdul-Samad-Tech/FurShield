@extends('layouts.app')
@section('title', $card->name ?? 'Pet tag')
@section('content')
<div class="mx-auto max-w-lg px-4 pb-16 pt-28">
    @if ($card->lost)
        <div role="alert" class="rounded-fs-lg bg-danger px-fs-md py-fs-sm text-text-inverse">
            <p class="font-display text-2xl font-bold">This pet is lost</p>
            @if ($card->lostNote)
                <p class="mt-fs-2xs text-base">{{ $card->lostNote }}</p>
            @else
                <p class="mt-fs-2xs text-base">The owner is looking for this pet.</p>
            @endif
        </div>
    @endif

    <x-ui.card @class(['mt-fs-md' => $card->lost])>
        @if ($card->hasDetails())
            @if ($card->photoUrl)
                <img
                    src="{{ $card->photoUrl }}"
                    alt="{{ $card->photoAlt }}"
                    @if ($card->photoWidth) width="{{ $card->photoWidth }}" @endif
                    @if ($card->photoHeight) height="{{ $card->photoHeight }}" @endif
                    class="mb-fs-md aspect-square w-full rounded-fs-lg object-cover"
                >
            @endif

            <p class="text-sm font-medium text-text">FurShield pet tag</p>
            <h1 class="mt-fs-2xs font-display text-4xl font-bold text-text">{{ $card->name ?? 'A pet' }}</h1>

            @if ($card->species || $card->breed)
                <p class="mt-fs-2xs text-text">{{ collect([$card->species, $card->breed])->filter()->implode(' · ') }}</p>
            @endif

            @if ($card->allergies || $card->medications)
                <div class="mt-fs-md grid gap-fs-xs">
                    <h2 class="font-display text-xl font-semibold text-text">Critical alerts</h2>
                    @if ($card->allergies)
                        <p class="text-text"><span class="font-medium">Allergies.</span> {{ $card->allergies }}</p>
                    @endif
                    @if ($card->medications)
                        <p class="text-text"><span class="font-medium">Medications.</span> {{ $card->medications }}</p>
                    @endif
                </div>
            @endif

            @if ($card->callUrl || $card->whatsappUrl)
                <div class="mt-fs-md flex flex-wrap gap-fs-2xs">
                    @if ($card->callUrl)
                        <x-ui.button :href="$card->callUrl" aria-label="Call the owner">
                            <x-ui.icon name="phone" />
                            Call
                        </x-ui.button>
                    @endif
                    @if ($card->whatsappUrl)
                        <x-ui.button :href="$card->whatsappUrl" variant="secondary" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp the owner">
                            <x-ui.icon name="message-circle" />
                            WhatsApp
                        </x-ui.button>
                    @endif
                </div>
            @endif
        @else
            <h1 class="sr-only">Pet tag</h1>
            <x-ui.empty-state
                title="This tag is private"
                icon="shield"
                description="The owner has not shared a name, photo, or contact details on this tag."
            />
        @endif
    </x-ui.card>

    @if ($card->lost)
        <x-ui.card class="mt-fs-md">
            <h2 class="font-display text-2xl font-semibold text-text">I found this pet</h2>
            <p class="mt-fs-2xs text-sm text-text">Tell the owner where you are. A location is optional, and the browser asks before sharing it.</p>

            <form method="POST" action="{{ route('pets.public.found', $token) }}" class="mt-fs-md grid gap-fs-sm" x-data="petFound">
                @csrf
                <x-ui.input
                    label="Your name"
                    name="finder_name"
                    :value="old('finder_name')"
                    :error="$errors->first('finder_name')"
                    autocomplete="name"
                    maxlength="80"
                />
                <x-ui.textarea
                    label="What should the owner know?"
                    name="message"
                    :error="$errors->first('message')"
                    hint="A street, a shop, or what the pet is doing."
                    required
                    maxlength="1000"
                >{!! old('message') !!}</x-ui.textarea>

                <input type="hidden" name="latitude" x-bind:value="latitude">
                <input type="hidden" name="longitude" x-bind:value="longitude">

                <div class="flex flex-wrap items-center gap-fs-sm">
                    <x-ui.button type="button" variant="secondary" x-on:click="shareLocation()" x-bind:disabled="locating">
                        <span x-text="locating ? 'Finding location…' : 'Use my location'">Use my location</span>
                    </x-ui.button>
                    <p class="text-sm text-text" role="status" aria-live="polite" x-text="locationNote"></p>
                </div>

                @if ($errors->has('latitude') || $errors->has('longitude'))
                    <p class="text-sm text-danger" role="alert">{{ $errors->first('latitude') ?: $errors->first('longitude') }}</p>
                @endif

                <x-ui.button type="submit">Send to the owner</x-ui.button>
            </form>
        </x-ui.card>
    @endif
</div>
@endsection
