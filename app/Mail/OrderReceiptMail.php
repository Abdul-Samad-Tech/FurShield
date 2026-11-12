<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $reference = $this->order->payment?->reference ?? 'Order '.$this->order->id;

        return new Envelope(
            subject: 'Your FurShield receipt '.$reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'receipts.document',
        );
    }
}
