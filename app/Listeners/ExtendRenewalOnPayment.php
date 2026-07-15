<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use Illuminate\Support\Carbon;

class ExtendRenewalOnPayment
{
    public function handle(PaymentConfirmed $event): void
    {
        $payment = $event->payment;

        if ($payment->purpose !== 'renewal') {
            return;
        }

        $user = $payment->user;
        $base = $user->renewal_expires_at && $user->renewal_expires_at->isFuture()
            ? Carbon::parse($user->renewal_expires_at)
            : now();

        $user->forceFill([
            'renewal_expires_at' => $base->addYear(),
        ])->save();
    }
}
