<?php

namespace App\Http\Controllers\Membership;

use App\Events\PaymentConfirmed;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\ToyyibpayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ToyyibpayWebhookController extends Controller
{
    /**
     * Browser redirect target after paying (UX only — never trusted for
     * status updates, that's what callback() + the fallback job are for).
     */
    public function return(Payment $payment): RedirectResponse
    {
        return redirect()->route('registration.status', ['email' => $payment->user->email]);
    }

    /**
     * Server-to-server webhook. Toyyibpay's callback payload has no
     * verifiable signature, so we never trust its `status` field directly —
     * instead we re-query the bill status straight from Toyyibpay's API
     * using the billcode, which is the actual source of truth.
     */
    public function callback(Request $request, ToyyibpayService $toyyibpay): Response
    {
        $billCode = $request->input('billcode') ?? $request->input('billCode');

        if (! $billCode) {
            return response('missing billcode', 422);
        }

        $payment = Payment::where('toyyibpay_bill_code', $billCode)->first();

        if (! $payment) {
            return response('bill not found', 404);
        }

        if ($payment->status !== 'paid' && $toyyibpay->isBillPaid($billCode)) {
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            event(new PaymentConfirmed($payment));
        }

        return response('OK', 200);
    }
}
