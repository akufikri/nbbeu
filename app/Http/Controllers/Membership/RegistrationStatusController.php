<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ToyyibpayService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationStatusController extends Controller
{
    public function show(Request $request, ToyyibpayService $toyyibpay): View
    {
        $request->validate(['email' => ['nullable', 'email']]);

        $user = null;
        $payment = null;
        $billUrl = null;

        if ($email = $request->query('email')) {
            $user = User::where('email', $email)->latest('id')->first();
            $payment = $user?->payments()->latest('id')->first();

            if ($payment && $payment->status !== 'paid' && $payment->toyyibpay_bill_code) {
                $billUrl = $toyyibpay->billUrl($payment->toyyibpay_bill_code);
            }
        }

        return view('membership.status', [
            'email' => $email ?? null,
            'user' => $user,
            'payment' => $payment,
            'billUrl' => $billUrl,
        ]);
    }
}
