<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center rounded-md border border-border-strong bg-surface-raised px-4 py-2 text-xs font-semibold uppercase tracking-widest text-text shadow-sm transition duration-150 ease-in-out hover:bg-surface-sunken focus:outline-none focus:ring-2 focus:ring-focus focus:ring-offset-2 focus:ring-offset-surface disabled:opacity-25']) }}>
    {{ $slot }}
</button>
