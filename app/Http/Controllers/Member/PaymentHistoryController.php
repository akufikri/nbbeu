<?php

namespace App\Http\Controllers\Member;

use App\Actions\Membership\GeneratePaymentReceipt;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PaymentHistoryController extends Controller
{
    public function index(Request $request): View
    {
        return view('member.payments', [
            'payments' => $request->user()->payments()->latest('id')->paginate(15),
        ]);
    }

    public function receipt(Request $request, Payment $payment, GeneratePaymentReceipt $generate): Response
    {
        abort_unless($payment->user_id === $request->user()->id && $payment->status === 'paid', 403);

        return $generate($payment);
    }
}
