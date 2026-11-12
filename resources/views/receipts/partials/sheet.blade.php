@php
    $reference = $order->payment?->reference ?? 'Order '.$order->id;
    $placedAt = $order->order_date?->timezone(config('app.timezone'))->format('M j, Y g:i A');
@endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;color:#102027;font-family:Outfit,'Segoe UI',sans-serif;">
    <tr>
        <td style="padding:0 0 16px;">
            <p style="margin:0;font-size:12px;letter-spacing:0.04em;text-transform:uppercase;color:#5B737C;">Receipt</p>
            <p style="margin:4px 0 0;font-family:Syne,'Segoe UI',sans-serif;font-size:24px;font-weight:700;color:#102027;">{{ $reference }}</p>
            <p style="margin:4px 0 0;font-size:13px;color:#5B737C;">Order #{{ $order->id }}@if ($placedAt) · {{ $placedAt }}@endif</p>
        </td>
    </tr>
    <tr>
        <td>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                <thead>
                    <tr>
                        <th scope="col" align="left" style="padding:8px 0;border-bottom:1px solid #D7EEE8;font-size:12px;font-weight:600;color:#5B737C;">Item</th>
                        <th scope="col" align="right" style="padding:8px 0;border-bottom:1px solid #D7EEE8;font-size:12px;font-weight:600;color:#5B737C;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($order->items as $item)
                        <tr>
                            <td style="padding:12px 12px 12px 0;border-bottom:1px solid #D7EEE8;font-size:14px;color:#102027;">
                                {{ $item->product?->name ?? 'Removed product' }}
                                <span style="display:block;margin-top:2px;font-size:12px;color:#5B737C;">{{ $item->quantity }} × {{ \App\Support\Furshield::money($item->price_each) }}</span>
                            </td>
                            <td align="right" style="padding:12px 0;border-bottom:1px solid #D7EEE8;font-size:14px;font-weight:600;color:#102027;white-space:nowrap;">{{ \App\Support\Furshield::money($item->price_each * $item->quantity) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="padding:12px 0;font-size:14px;color:#5B737C;">This order has no items.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding:16px 0 0;">
            <p style="margin:0;font-family:Syne,'Segoe UI',sans-serif;font-size:22px;font-weight:700;color:#102027;">Total {{ \App\Support\Furshield::money($order->total_amount) }}</p>
        </td>
    </tr>
    <tr>
        <td style="padding:20px 0 0;">
            <p style="margin:0 0 4px;font-size:12px;font-weight:600;letter-spacing:0.04em;text-transform:uppercase;color:#5B737C;">Payment</p>
            @if ($order->payment)
                <p style="margin:0;font-size:14px;line-height:1.5;color:#102027;">{{ $order->payment->method->label() }}@if ($order->payment->card_last4) · {{ $order->payment->card_brand }} ending {{ $order->payment->card_last4 }}@endif</p>
                <p style="margin:2px 0 0;font-size:14px;color:#102027;">{{ $order->payment->status->label() }} · {{ $order->payment->reference }}</p>
            @else
                <p style="margin:0;font-size:14px;color:#5B737C;">No payment record is attached.</p>
            @endif
        </td>
    </tr>
    <tr>
        <td style="padding:16px 0 0;">
            <p style="margin:0 0 4px;font-size:12px;font-weight:600;letter-spacing:0.04em;text-transform:uppercase;color:#5B737C;">Receive</p>
            <p style="margin:0;font-size:14px;line-height:1.5;color:#102027;">{{ $order->fulfillment?->label() ?? 'Not recorded' }}</p>
            @if ($order->fulfillment === \App\Enums\FulfillmentMethod::Delivery)
                <p style="margin:2px 0 0;font-size:14px;line-height:1.5;color:#102027;">{{ $order->address_line }}<br>{{ $order->city }}</p>
            @endif
        </td>
    </tr>
    <tr>
        <td style="padding:16px 0 0;">
            <p style="margin:0 0 4px;font-size:12px;font-weight:600;letter-spacing:0.04em;text-transform:uppercase;color:#5B737C;">Contact</p>
            <p style="margin:0;font-size:14px;line-height:1.5;color:#102027;">{{ $order->contact_name }}<br>{{ $order->contact_phone }}<br>{{ $order->contact_email }}</p>
        </td>
    </tr>
    @if ($order->notes)
        <tr>
            <td style="padding:16px 0 0;">
                <p style="margin:0 0 4px;font-size:12px;font-weight:600;letter-spacing:0.04em;text-transform:uppercase;color:#5B737C;">Note</p>
                <p style="margin:0;font-size:14px;line-height:1.5;color:#102027;">{!! nl2br(e($order->notes)) !!}</p>
            </td>
        </tr>
    @endif
</table>
