@extends('layouts.app')

@section('title', 'UI')

@section('content')
@php
    $portrait = 'data:image/svg+xml,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" fill="#0E7C74"/><circle cx="32" cy="24" r="10" fill="#F3FAF8"/><path d="M14 56c3-12 33-12 36 0" fill="#F3FAF8"/></svg>');
    $icons = ['paw-print', 'heart', 'heart-pulse', 'stethoscope', 'calendar', 'shield', 'house', 'bone', 'cat', 'dog', 'bird', 'activity', 'bell', 'check', 'circle-check', 'circle-alert', 'triangle-alert', 'info', 'inbox', 'loader', 'plus', 'pencil', 'trash-2', 'user', 'shopping-bag', 'sparkles', 'trending-up', 'trending-down', 'chevron-left', 'chevron-down', 'chevron-right', 'x'];
@endphp

<div
    class="mx-auto max-w-6xl space-y-fs-2xl px-fs-sm py-fs-xl"
    x-data="{
        dark: false,
        ping(message, variant) {
            $dispatch('fs-toast', { message, variant })
        }
    }"
>
    <header class="flex flex-wrap items-end justify-between gap-fs-sm">
        <div>
            <p class="text-sm font-medium text-success">FurShield</p>
            <h1 class="font-display text-4xl font-semibold tracking-tight text-text">Component library</h1>
            <p class="mt-fs-2xs max-w-2xl text-sm text-text-muted">Every component, in the states a pet record actually needs. Local only.</p>
        </div>
        <x-ui.button type="button" variant="secondary" @click="dark = !dark; document.documentElement.classList.toggle('dark', dark)">
            <span x-text="dark ? 'Use light mode' : 'Use dark mode'"></span>
        </x-ui.button>
    </header>

    <section class="space-y-fs-md" aria-labelledby="buttons-heading">
        <h2 id="buttons-heading" class="font-display text-2xl font-semibold text-text">Buttons</h2>
        @foreach (['primary' => 'Primary', 'secondary' => 'Secondary', 'ghost' => 'Ghost', 'danger' => 'Danger'] as $variant => $label)
            <div class="space-y-fs-xs">
                <h3 class="text-sm font-medium text-text">{{ $label }}</h3>
                <div class="flex flex-wrap items-center gap-fs-xs">
                    @foreach (['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large'] as $size => $sizeLabel)
                        <x-ui.button :variant="$variant" :size="$size">{{ $sizeLabel }}</x-ui.button>
                        <x-ui.button :variant="$variant" :size="$size" disabled>{{ $sizeLabel }} disabled</x-ui.button>
                        <x-ui.button :variant="$variant" :size="$size" loading>{{ $sizeLabel }} loading</x-ui.button>
                    @endforeach
                </div>
            </div>
        @endforeach
        <div class="flex flex-wrap items-center gap-fs-xs">
            <x-ui.button href="{{ route('home') }}" variant="primary">Link button</x-ui.button>
            <x-ui.button type="button" variant="ghost" size="sm" aria-label="Add pet">
                <x-ui.icon name="plus" />
            </x-ui.button>
        </div>
    </section>

    <section class="space-y-fs-md" aria-labelledby="cards-heading">
        <h2 id="cards-heading" class="font-display text-2xl font-semibold text-text">Cards</h2>
        <p class="text-sm text-text-muted">A card is one surface. Do not put a card inside a card.</p>
        <div class="grid gap-fs-sm lg:grid-cols-2">
            <x-ui.card>
                <x-slot:header>
                    <h3 class="font-display text-xl font-semibold">Medical history</h3>
                </x-slot:header>
                <p class="text-sm text-text-muted">Notes, vaccines, and visit summaries live here.</p>
                <x-slot:footer>
                    <x-ui.button type="button" size="sm" variant="ghost">View timeline</x-ui.button>
                </x-slot:footer>
            </x-ui.card>
            <x-ui.card>
                <h3 class="font-display text-xl font-semibold">Care plan</h3>
                <p class="mt-fs-2xs text-sm text-text-muted">A second card sits beside the first. It does not sit inside it.</p>
            </x-ui.card>
        </div>
    </section>

    <section class="space-y-fs-sm" aria-labelledby="status-heading">
        <h2 id="status-heading" class="font-display text-2xl font-semibold text-text">Badge and status</h2>
        <div class="flex flex-wrap gap-fs-xs">
            <x-ui.badge>Neutral</x-ui.badge>
            <x-ui.badge variant="tide">Tide</x-ui.badge>
            <x-ui.badge variant="bloom">Bloom</x-ui.badge>
        </div>
        <div class="flex flex-wrap gap-fs-xs">
            <x-ui.status-pill variant="success">Confirmed</x-ui.status-pill>
            <x-ui.status-pill variant="warning">Waiting</x-ui.status-pill>
            <x-ui.status-pill variant="danger">Cancelled</x-ui.status-pill>
            <x-ui.status-pill>Draft</x-ui.status-pill>
        </div>
    </section>

    <section class="grid gap-fs-md lg:grid-cols-3" aria-labelledby="fields-heading">
        <h2 id="fields-heading" class="font-display text-2xl font-semibold text-text lg:col-span-3">Fields</h2>
        <x-ui.input label="Pet name" name="pet_name" placeholder="Miso" hint="The name you use at home." autocomplete="off" />
        <x-ui.input label="Weight" name="weight" type="text" value="" error="Enter a weight in kilograms." required autocomplete="off" />
        <x-ui.input label="Microchip" name="microchip" value="981000004567890" disabled autocomplete="off" />

        <x-ui.select label="Species" name="species" hint="Used to match care advice.">
            <option value="">Choose</option>
            <option value="dog">Dog</option>
            <option value="cat">Cat</option>
        </x-ui.select>
        <x-ui.select label="Clinic" name="clinic" error="Choose a clinic.">
            <option value="">Choose</option>
            <option>Harbour Clinic</option>
        </x-ui.select>
        <x-ui.select label="Breed" name="breed" disabled>
            <option>Domestic shorthair</option>
        </x-ui.select>

        <x-ui.textarea label="Notes" name="notes" hint="Visible to you and the clinic.">Ate breakfast. Energy is good.</x-ui.textarea>
        <x-ui.textarea label="Allergies" name="allergies" error="Add the allergy, or write none." required></x-ui.textarea>
        <x-ui.textarea label="Insurance" name="insurance" disabled>Policy on file.</x-ui.textarea>
    </section>

    <section class="space-y-fs-sm" aria-labelledby="stats-heading">
        <h2 id="stats-heading" class="font-display text-2xl font-semibold text-text">Stat cards</h2>
        <div class="grid gap-fs-sm md:grid-cols-3">
            <x-ui.stat-card label="Visits" value="12" icon="stethoscope" delta="+2 this month" trend="up">
                <x-slot:sparkline>
                    <svg viewBox="0 0 120 36" class="h-9 w-full" fill="none" aria-hidden="true">
                        <polyline points="0,28 20,22 40,24 60,12 80,16 100,8 120,10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </x-slot:sparkline>
            </x-ui.stat-card>
            <x-ui.stat-card label="Open tasks" value="3" icon="calendar" delta="−1 this week" trend="down" />
            <x-ui.stat-card label="Records" value="8" delta="No change" />
        </div>
    </section>

    <section class="grid gap-fs-md lg:grid-cols-2" aria-labelledby="empty-heading">
        <h2 id="empty-heading" class="font-display text-2xl font-semibold text-text lg:col-span-2">Empty, loading, and avatar</h2>
        <x-ui.empty-state icon="paw-print" title="No pets yet" description="Add a pet to keep health, visits, and adoption notes in one record.">
            <x-slot:action>
                <x-ui.button type="button">Add a pet</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
        <x-ui.empty-state title="No visits" description="Book a visit when you are ready." />
        <div class="grid gap-fs-sm">
            <x-ui.skeleton />
            <x-ui.skeleton variant="avatar" />
            <x-ui.skeleton variant="card" />
        </div>
        <div class="flex flex-wrap items-center gap-fs-sm">
            <x-ui.avatar name="Miso Park" size="sm" />
            <x-ui.avatar name="Miso Park" :src="$portrait" />
            <x-ui.avatar name="Harbour Clinic" src="/images/missing-avatar.png" size="lg" />
        </div>
    </section>

    <section class="space-y-fs-sm" aria-labelledby="overlay-heading">
        <h2 id="overlay-heading" class="font-display text-2xl font-semibold text-text">Modal, confirm, toast, and tooltip</h2>
        <div class="flex flex-wrap items-center gap-fs-xs">
            <x-ui.modal name="preview" title="Visit note">
                <x-slot:trigger>
                    <x-ui.button type="button">Open modal</x-ui.button>
                </x-slot:trigger>
                <p class="text-sm text-text-muted">A short note stays with the pet record. Close with Escape, the close button, or the backdrop.</p>
            </x-ui.modal>

            <x-ui.confirm-dialog name="discard-pet" title="Delete this pet profile?" message="This removes the pet profile from your records." confirm="Delete pet">
                <x-slot:trigger>
                    <x-ui.button type="button" variant="danger">Delete</x-ui.button>
                </x-slot:trigger>
            </x-ui.confirm-dialog>

            <x-ui.confirm-dialog name="save-note" title="Save this note?" message="The note is added to the pet record." confirm="Save note" variant="primary">
                <x-slot:trigger>
                    <x-ui.button type="button" variant="secondary">Save note</x-ui.button>
                </x-slot:trigger>
            </x-ui.confirm-dialog>

            <x-ui.tooltip text="Shows the health timeline">
                <x-ui.button type="button" variant="ghost">Timeline</x-ui.button>
            </x-ui.tooltip>
        </div>
        <div class="flex flex-wrap gap-fs-xs">
            <x-ui.button type="button" variant="secondary" @click="ping('Pet record saved.', 'success')">Success toast</x-ui.button>
            <x-ui.button type="button" variant="secondary" @click="ping('The clinic has not confirmed this visit.', 'warning')">Warning toast</x-ui.button>
            <x-ui.button type="button" variant="secondary" @click="ping('The record could not be saved.', 'danger')">Danger toast</x-ui.button>
        </div>
    </section>

    <section class="space-y-fs-sm" aria-labelledby="tabs-heading">
        <h2 id="tabs-heading" class="font-display text-2xl font-semibold text-text">Tabs</h2>
        <x-ui.tabs label="Pet record" initial="care" :tabs="['care' => 'Care', 'visits' => 'Visits', 'archive' => ['label' => 'Archive', 'disabled' => true]]">
            <x-ui.tab id="care">Care notes stay with the pet.</x-ui.tab>
            <x-ui.tab id="visits">Visits show the clinic, the vet, and the date.</x-ui.tab>
            <x-ui.tab id="archive">Archived records stay out of the daily list.</x-ui.tab>
        </x-ui.tabs>
    </section>

    <section class="space-y-fs-sm" aria-labelledby="icons-heading">
        <h2 id="icons-heading" class="font-display text-2xl font-semibold text-text">Icons</h2>
        <div class="flex flex-wrap gap-fs-xs">
            @foreach ($icons as $icon)
                <span class="grid size-11 place-items-center rounded-fs-sm border border-border bg-surface-raised text-text" title="{{ $icon }}">
                    <x-ui.icon :name="$icon" />
                    <span class="sr-only">{{ $icon }}</span>
                </span>
            @endforeach
        </div>
    </section>

    <section class="space-y-fs-sm" aria-labelledby="legacy-heading">
        <h2 id="legacy-heading" class="font-display text-2xl font-semibold text-text">Legacy classes</h2>
        <p class="max-w-2xl text-sm text-text-muted">`.btn-primary`, `.btn-secondary`, `.btn-ghost`, `.surface`, and `.field` still style the current screens.</p>
        <div class="rounded-fs-lg bg-hero-mesh p-fs-md">
            <div class="flex flex-wrap gap-fs-xs">
                <button type="button" class="btn-primary">Primary</button>
                <button type="button" class="btn-secondary">Secondary</button>
                <button type="button" class="btn-ghost">Ghost</button>
            </div>
        </div>
        <div class="surface p-fs-md">
            <label class="text-sm font-medium text-text" for="legacy-field">Legacy field</label>
            <input id="legacy-field" class="field mt-fs-2xs" placeholder="Still the old field class">
        </div>
    </section>
</div>
@endsection
