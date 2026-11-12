<div
    x-data="commandPalette"
    x-show="$store.palette.open"
    x-cloak
    data-search-url="{{ route('search') }}"
    class="fixed inset-0 z-[80] flex items-start justify-center p-fs-sm sm:pt-24"
    @keydown.window="onKeydown($event)"
>
    <div class="absolute inset-0 bg-text/40" @click="close()"></div>
    <div
        id="command-palette"
        x-ref="dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="command-palette-title"
        class="relative w-full max-w-lg rounded-fs-lg border border-border bg-surface-raised p-fs-md text-text shadow-fs-lg"
        @keydown.tab="trap($event)"
    >
        <div class="flex items-start justify-between gap-fs-sm">
            <div>
                <h2 id="command-palette-title" class="font-display text-xl font-semibold">Search</h2>
                <p class="text-sm text-text-muted"><span x-text="shortcut"></span> opens this from anywhere</p>
            </div>
            <button type="button" class="grid size-11 place-items-center rounded-fs-sm text-text hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="close()" aria-label="Close search">
                <x-ui.icon name="x" />
            </button>
        </div>
        <label for="command-palette-input" class="mt-fs-sm block text-sm font-medium text-text">Search FurShield</label>
        <input
            id="command-palette-input"
            x-ref="input"
            x-model="query"
            @input="onInput()"
            type="search"
            role="combobox"
            autocomplete="off"
            aria-autocomplete="list"
            aria-controls="command-palette-list"
            :aria-expanded="rows().length > 0 ? 'true' : 'false'"
            :aria-activedescendant="rows().length ? 'palette-option-' + active : null"
            placeholder="Pets, vets, products, care"
            class="mt-fs-2xs block w-full rounded-fs-md border border-border-strong bg-surface-raised px-fs-sm py-fs-xs text-sm text-text focus:border-focus focus:outline-none focus:ring-2 focus:ring-focus/40"
        >
        <p x-show="loading" class="mt-fs-sm text-sm text-text" role="status">Searching…</p>
        <p x-show="error" x-cloak class="mt-fs-sm text-sm text-text" role="alert" x-text="error"></p>
        <p x-show="!loading && !error && rows().length === 0" x-cloak class="mt-fs-sm text-sm text-text">No matches.</p>
        <ul id="command-palette-list" role="listbox" aria-label="Search results" class="mt-fs-sm grid max-h-80 gap-fs-3xs overflow-y-auto">
            <template x-for="(row, index) in rows()" :key="row.id">
                <li>
                    <p x-show="index === 0 || rows()[index - 1].group !== row.group" class="px-fs-sm pt-fs-xs text-xs font-medium text-text-muted" x-text="row.group"></p>
                    <a
                        :id="'palette-option-' + index"
                        role="option"
                        :aria-selected="index === active ? 'true' : 'false'"
                        :href="row.url"
                        class="flex min-h-11 items-center justify-between gap-fs-sm rounded-fs-sm px-fs-sm py-fs-xs text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus"
                        :class="index === active ? 'bg-surface-sunken text-text' : 'text-text'"
                        @mouseenter="active = index"
                        @click.prevent="go(row)"
                    >
                        <span class="font-medium" x-text="row.label"></span>
                        <span class="shrink-0 text-text-muted" x-text="row.hint"></span>
                    </a>
                </li>
            </template>
        </ul>
    </div>
</div>
