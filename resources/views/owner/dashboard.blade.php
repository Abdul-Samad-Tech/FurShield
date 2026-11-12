@extends('layouts.dashboard')

@section('title', 'Owner Dashboard')

@section('content')
<div x-data="{ ready: false }" x-init="ready = true">
    <div x-show="!ready" class="grid gap-fs-sm md:grid-cols-3" role="status" aria-label="Loading dashboard">
        <x-ui.skeleton variant="card" />
        <x-ui.skeleton variant="card" />
        <x-ui.skeleton variant="card" />
    </div>

    <div x-show="ready" x-cloak x-data="{ pet: {{ $pets->first()->id ?? 'null' }} }">
        <x-ui.error-summary />

        <div data-reveal>
            <h1 class="max-w-[20ch] text-balance font-display text-4xl font-bold text-text">Welcome back, {{ auth()->user()->name }}</h1>
            <p class="mt-fs-2xs text-text-muted">The next visit, the next due date, and the cart, in one place.</p>
        </div>

        <div class="mt-fs-md grid gap-fs-sm lg:grid-cols-12">
            <x-ui.card class="lg:col-span-7" data-reveal>
                <p class="text-sm font-medium text-text-muted">Next best action</p>
                @unless ($nextAction['tone'] === 'calm')
                    <div class="mt-fs-sm">
                        <x-ui.status-pill :variant="$nextAction['tone'] === 'danger' ? 'danger' : 'warning'">
                            {{ $nextAction['tone'] === 'danger' ? 'Overdue' : 'Due soon' }}
                        </x-ui.status-pill>
                    </div>
                @endunless
                <h2 class="mt-fs-sm font-display text-3xl font-semibold text-text">{{ $nextAction['title'] }}</h2>
                <p class="mt-fs-xs max-w-xl text-text">{{ $nextAction['body'] }}</p>
                <div class="mt-fs-md">
                    <x-ui.button :href="$nextAction['href']">{{ $nextAction['label'] }}</x-ui.button>
                </div>
            </x-ui.card>

            <div class="grid gap-fs-sm sm:grid-cols-2 lg:col-span-5 lg:grid-cols-2">
                <x-ui.stat-card label="Pets" :value="$pets->count()" icon="paw-print" :count="true" />
                <x-ui.stat-card label="Due in 30 days" :value="$dueCount" icon="heart-pulse" :count="true" />
                <x-ui.stat-card class="sm:col-span-2" label="Placed orders" :value="$placedCount" icon="shopping-bag" :count="true" :delta="$orderDelta">
                    <x-slot:sparkline>
                        <x-ui.sparkline :values="$sparkline" label="Placed order totals" />
                    </x-slot:sparkline>
                </x-ui.stat-card>
            </div>

            <x-ui.card class="lg:col-span-7" data-reveal="right">
                <div class="flex flex-wrap items-center justify-between gap-fs-sm">
                    <h2 class="font-display text-2xl font-semibold text-text">Care radar</h2>
                    <a href="{{ route('owner.pets.index') }}" class="text-sm font-medium text-success focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">All pets</a>
                </div>

                @if ($pets->isEmpty())
                    <x-ui.empty-state icon="paw-print" title="No pets yet" description="Add a pet to see due and overdue care here.">
                        <x-slot:action>
                            <x-ui.button :href="route('owner.pets.create')" size="sm">Add pet</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                @else
                    <div class="mt-fs-sm flex flex-wrap gap-fs-2xs" role="group" aria-label="Choose a pet">
                        @foreach ($pets as $pet)
                            <button
                                type="button"
                                @click="pet = {{ $pet->id }}"
                                :aria-pressed="(pet === {{ $pet->id }}).toString()"
                                class="inline-flex min-h-11 items-center gap-fs-2xs rounded-fs-xl px-fs-sm py-fs-2xs text-sm font-medium motion-safe:transition motion-safe:duration-fs-fast motion-safe:ease-fs-standard focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus"
                                :class="pet === {{ $pet->id }} ? 'bg-tide-deep text-text-inverse' : 'border border-border-strong bg-surface-raised text-text'"
                            >
                                {{ $pet->name }}
                            </button>
                        @endforeach
                    </div>

                    @foreach ($pets as $pet)
                        <div @if (! $loop->first) x-cloak @endif x-show="pet === {{ $pet->id }}" class="mt-fs-md">
                            @php $care = $pet->careStatus(); @endphp
                            <div class="flex flex-wrap items-center gap-fs-2xs">
                                <h3 class="font-display text-xl font-semibold text-text">{{ $pet->name }}</h3>
                                <x-ui.status-pill :variant="$care->variant()">{{ $care->label() }}</x-ui.status-pill>
                            </div>
                            @if (empty($radar[$pet->id]))
                                <x-ui.empty-state icon="circle-check" title="Nothing due" description="No overdue care, and nothing due in the next 30 days." />
                            @else
                                <ul class="mt-fs-sm divide-y divide-border">
                                    @foreach ($radar[$pet->id] as $item)
                                        <li class="flex flex-wrap items-center justify-between gap-fs-sm py-fs-sm">
                                            <div>
                                                <p class="font-medium text-text">{{ $item['title'] }}</p>
                                                <p class="text-sm text-text-muted">{{ ucfirst($item['phrase']) }} · {{ $item['due']->timezone(config('app.timezone'))->format('M j, Y') }}</p>
                                            </div>
                                            <x-ui.status-pill :variant="$item['variant']">{{ $item['label'] }}</x-ui.status-pill>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                @endif
            </x-ui.card>

            <x-ui.card class="lg:col-span-5" data-reveal="left">
                <div class="flex items-center justify-between gap-fs-sm">
                    <h2 class="font-display text-2xl font-semibold text-text">Upcoming appointments</h2>
                    <x-ui.button :href="route('owner.appointments.create')" variant="ghost" size="sm">Book</x-ui.button>
                </div>
                @if ($upcoming->isEmpty())
                    <x-ui.empty-state icon="calendar" title="No visits booked" description="Request a time when a reminder comes due." />
                @else
                    <ol class="relative mt-fs-md space-y-fs-sm border-l border-border pl-fs-md">
                        @foreach ($upcoming as $appt)
                            <li>
                                <span class="absolute -left-1.5 mt-1.5 size-3 rounded-full bg-tide" aria-hidden="true"></span>
                                <p class="font-medium text-text">{{ $appt->pet->name }} with {{ $appt->vet->name }}</p>
                                <p class="text-sm text-text-muted">{{ $appt->appointment_time->timezone(config('app.timezone'))->format('M j, g:i A') }}</p>
                                <x-ui.status-pill class="mt-fs-2xs" variant="neutral">{{ $appt->status->label() }}</x-ui.status-pill>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-ui.card>

            <section class="lg:col-span-12" aria-labelledby="quick-actions-heading">
                <h2 id="quick-actions-heading" class="font-display text-2xl font-semibold text-text">Quick actions</h2>
                <div class="mt-fs-sm grid gap-fs-sm sm:grid-cols-2 lg:grid-cols-4">
                    <x-ui.button class="w-full" :href="route('owner.appointments.create')" variant="secondary">
                        <x-ui.icon name="stethoscope" size="sm" /> Book vet
                    </x-ui.button>
                    @foreach ($pets as $pet)
                        @if ($loop->first)
                            <x-ui.button class="w-full" :href="route('owner.pets.health', $pet)" variant="secondary" x-show="pet === {{ $pet->id }}">
                                <x-ui.icon name="plus" size="sm" /> Add record
                            </x-ui.button>
                        @else
                            <x-ui.button class="w-full" :href="route('owner.pets.health', $pet)" variant="secondary" x-cloak x-show="pet === {{ $pet->id }}">
                                <x-ui.icon name="plus" size="sm" /> Add record
                            </x-ui.button>
                        @endif
                    @endforeach
                    @if ($pets->isEmpty())
                        <x-ui.button class="w-full" :href="route('owner.pets.create')" variant="secondary">
                            <x-ui.icon name="plus" size="sm" /> Add record
                        </x-ui.button>
                    @endif
                    <x-ui.button class="w-full" :href="route('products.index')" variant="secondary">
                        <x-ui.icon name="shopping-bag" size="sm" /> Reorder
                    </x-ui.button>
                    <x-ui.button class="w-full" :href="route('triage')" variant="danger">
                        <x-ui.icon name="triangle-alert" size="sm" /> Emergency
                    </x-ui.button>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
