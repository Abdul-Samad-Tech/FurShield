<div class="mt-fs-lg grid gap-fs-md sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($listings as $listing)
        <a href="{{ route('adopt.show', $listing) }}" class="surface overflow-hidden transition hover:-translate-y-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">
            <x-card-image :src="$listing->photo_path" type="adoption" :seed="$listing->id" :alt="$listing->pet_name" variant="card" />
            <div class="p-5">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="font-display text-2xl font-semibold text-text">{{ $listing->pet_name }}</h2>
                    <span class="rounded-full bg-tide/10 px-2 py-1 text-xs font-semibold text-tide">{{ $listing->status->label() }}</span>
                </div>
                @if ($answers)
                    @php
                        $matched = collect($deck)->firstWhere('id', $listing->id);
                    @endphp
                    <p class="mt-fs-2xs inline-flex rounded-fs-xs bg-success-soft px-fs-2xs py-fs-3xs text-xs font-medium text-text">{{ $matched['match'] ?? 0 }}% match</p>
                @endif
                <p class="mt-2 text-sm text-ink-mute">{{ $listing->species }} · {{ $listing->breed }} · {{ $listing->shelter->shelter_name ?? $listing->shelter->name }}</p>
            </div>
        </a>
    @empty
        <div class="sm:col-span-2 lg:col-span-3">
            <x-ui.empty-state title="No pets to adopt" icon="paw-print" description="Nothing matches that search. Clear the filters or check again after a shelter adds a listing.">
                <x-slot:action>
                    <x-ui.button :href="route('adopt.index')">Clear filters</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </div>
    @endforelse
</div>
@if ($listings->hasPages())
    <div class="mt-fs-md">{{ $listings->links() }}</div>
@endif
