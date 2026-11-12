@php
    $variant = $variant ?? 'sidebar';
    $phrase = match ($item['badge_key']) {
        'cart' => $item['badge'].' in cart',
        'alerts' => $item['badge'].' unread',
        'appointments' => $item['badge'].' pending',
        default => '',
    };
    $stacked = $variant === 'tabs';
    $badgeClass = match ($item['badge_key']) {
        'alerts' => 'bg-danger text-text-inverse',
        'appointments' => 'bg-warning-soft text-text',
        'cart' => 'bg-success text-text-inverse',
        default => 'bg-success-soft text-text',
    };
    $isCart = $item['badge_key'] === 'cart';
@endphp

<a
    href="{{ $item['url'] }}"
    @if ($isCart) @click.prevent="$store.cart.show()" aria-controls="mini-cart" @endif
    @if ($item['active']) aria-current="page" @endif
    class="{{ $stacked
        ? 'relative flex min-h-11 w-full flex-col items-center justify-center gap-fs-3xs px-fs-3xs text-[0.7rem] font-medium'
        : 'flex min-h-11 items-center gap-fs-sm rounded-fs-sm px-fs-sm py-fs-2xs text-sm font-medium' }} {{ $item['active']
        ? 'bg-success-soft text-text'
        : 'text-text-muted hover:bg-surface-sunken hover:text-text' }} focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus"
>
    <span class="relative">
        <x-ui.icon :name="$item['icon']" :size="$stacked ? 'sm' : 'md'" />
        @if ($stacked && $isCart)
            <span data-cart-badge @class([
                'absolute -right-2 -top-1 grid min-w-4 place-items-center rounded-fs-xl px-fs-3xs text-[0.65rem] leading-4',
                $badgeClass,
                'hidden' => $item['badge'] < 1,
            ]) aria-hidden="true">{{ $item['badge'] }}</span>
        @elseif ($stacked && $item['badge'] > 0)
            <span class="absolute -right-2 -top-1 grid min-w-4 place-items-center rounded-fs-xl px-fs-3xs text-[0.65rem] leading-4 {{ $badgeClass }}" aria-hidden="true">{{ $item['badge'] }}</span>
        @endif
    </span>
    <span class="truncate">{{ $item['label'] }}</span>
    @if (! $stacked && $isCart)
        <span data-cart-badge @class([
            'ml-auto rounded-fs-xl px-fs-2xs py-fs-3xs text-xs font-semibold',
            $badgeClass,
            'hidden' => $item['badge'] < 1,
        ]) aria-hidden="true">{{ $item['badge'] }}</span>
    @elseif (! $stacked && $item['badge'] > 0)
        <span class="ml-auto rounded-fs-xl px-fs-2xs py-fs-3xs text-xs font-semibold {{ $badgeClass }}" aria-hidden="true">{{ $item['badge'] }}</span>
    @endif
    @if ($isCart)
        <span class="sr-only" data-cart-phrase>@if ($item['badge'] > 0), {{ $phrase }}@endif</span>
    @elseif ($item['badge'] > 0 && $phrase !== '')
        <span class="sr-only">, {{ $phrase }}</span>
    @endif
</a>
