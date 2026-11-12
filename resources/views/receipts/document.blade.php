@component('mail.shell', [
    'title' => 'Order receipt',
    'preheader' => 'FurShield receipt '.($order->payment?->reference ?? 'Order '.$order->id).'.',
])
    @include('receipts.partials.sheet')
    <p style="margin:24px 0 0;">
        <a href="{{ route('owner.orders.show', $order) }}" style="display:inline-block;background:#0E7C74;color:#F3FAF8;text-decoration:none;padding:12px 18px;border-radius:12px;font-size:14px;font-weight:600;">View receipt on FurShield</a>
    </p>
@endcomponent
