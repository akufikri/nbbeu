<?php

namespace App\Jobs;

use App\Events\PaymentConfirmed;
use App\Models\Payment;
use App\Services\ToyyibpayService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Fallback for when Toyyibpay's callback never arrives: periodically
 * re-check pending payments directly against Toyyibpay's API.
 */
class CheckStuckToyyibpayPayments implements ShouldQueue
{
    use Queueable;

    public function handle(ToyyibpayService $toyyibpay): void
    {
        Payment::query()
            ->where('status', 'pending')
            ->whereNotNull('toyyibpay_bill_code')
            ->where('created_at', '<=', now()->subHour())
            ->each(function (Payment $payment) use ($toyyibpay) {
                try {
                    if ($toyyibpay->isBillPaid($payment->toyyibpay_bill_code)) {
                        $payment->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);

                        event(new PaymentConfirmed($payment));
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to check fallback Toyyibpay status', [
                        'payment_id' => $payment->id,
                        'bill_code' => $payment->toyyibpay_bill_code,
                        'error' => $e->getMessage(),
                    ]);
                }
            });
    }
}
