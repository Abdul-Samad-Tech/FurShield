@props([
    'tabs',
    'initial' => null,
    'label' => 'Sections',
])

@php
    $normalized = collect($tabs)->mapWithKeys(function ($tab, $id) {
        $config = is_array($tab) ? $tab : ['label' => $tab];

        return [$id => [
            'label' => $config['label'],
            'disabled' => (bool) ($config['disabled'] ?? false),
        ]];
    });
    $initialId = $initial && $normalized->has($initial) ? $initial : $normalized->keys()->first(fn ($id) => ! $normalized[$id]['disabled']);
@endphp

<div x-data='fsTabs(@json($initialId))' {{ $attributes->merge(['class' => 'text-text']) }}>
    <div
        role="tablist"
        aria-label="{{ $label }}"
        class="flex flex-wrap gap-fs-2xs border-b border-border"
        @keydown.left.prevent="onKey($event)"
        @keydown.right.prevent="onKey($event)"
        @keydown.home.prevent="onKey($event)"
        @keydown.end.prevent="onKey($event)"
    >
        @foreach ($normalized as $id => $tab)
            <button
                type="button"
                role="tab"
                id="fs-tab-{{ $id }}"
                data-tab="{{ $id }}"
                aria-controls="fs-panel-{{ $id }}"
                :aria-selected='active === @json($id) ? "true" : "false"'
                :tabindex='active === @json($id) ? 0 : -1'
                @click='select(@json($id))'
                @disabled($tab['disabled'])
                class="border-b-2 px-fs-sm py-fs-xs text-sm font-medium motion-safe:transition motion-safe:duration-fs-fast motion-safe:ease-fs-standard focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus disabled:cursor-not-allowed disabled:opacity-50"
                :class='active === @json($id) ? "border-success text-success" : "border-transparent text-text-muted hover:text-text"'
            >
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>
    {{ $slot }}
</div>
