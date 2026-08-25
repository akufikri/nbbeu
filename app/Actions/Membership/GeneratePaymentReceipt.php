<?php

namespace App\Actions\Membership;

use App\Models\Payment;
use Illuminate\Http\Response;

class GeneratePaymentReceipt
{
    public function __invoke(Payment $payment): Response
    {
        $payment->loadMissing('user');

        $pdf = app('dompdf.wrapper')
            ->loadView('pdf.payment-receipt', compact('payment'))
            ->setPaper('a5', 'portrait');

        $filename = 'Receipt-'.$payment->id.'.pdf';

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
