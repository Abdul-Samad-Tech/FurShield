<?php

namespace App\Services;

use App\Mail\OrderReceiptMail;
use App\Mail\WelcomeMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class GmailNoticeService
{
    public function welcome(User $user, bool $joined): void
    {
        if (! filled($user->email)) {
            return;
        }

        $this->deliver(new WelcomeMail($user, $joined), $user->email);
    }

    public function orderReceipt(Order $order): void
    {
        $order->loadMissing(['items.product', 'payment', 'owner']);

        $to = filled($order->contact_email) ? $order->contact_email : $order->owner?->email;

        if (! filled($to)) {
            return;
        }

        $this->deliver(new OrderReceiptMail($order), (string) $to);
    }

    private function deliver(Mailable $mail, string $to): void
    {
        try {
            Mail::to($to)->send($mail);
        } catch (Throwable $exception) {
            Log::warning('FurShield could not send mail.', [
                'mail' => $mail::class,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
