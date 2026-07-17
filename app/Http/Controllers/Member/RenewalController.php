<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\ToyyibpayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RenewalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('member.renewal', [
            'user' => $user,
            'daysUntilExpiry' => $user->renewal_expires_at ? (int) now()->diffInDays($user->renewal_expires_at, false) : null,
            'renewalDue' => $user->renewal_expires_at && $user->renewal_expires_at->lte(now()->addDays(30)),
        ]);
    }

    public function store(Request $request, ToyyibpayService $toyyibpay): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->status === 'approved', 403, 'Only approved members can renew.');

        $amount = (int) config('services.toyyibpay.renewal_amount');

        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => $amount / 100,
            'purpose' => 'renewal',
            'status' => 'pending',
        ]);

        $billCode = $toyyibpay->createBill([
            'bill_name' => 'NBBEU Membership Renewal',
            'bill_description' => "NBBEU membership renewal - {$user->name}",
            'return_url' => route('dashboard'),
            'callback_url' => route('registration.callback'),
            'reference_no' => (string) $payment->id,
            'payer_name' => $user->name,
            'payer_email' => $user->email,
            'payer_phone' => $user->phone,
        ], $amount);

        $payment->update(['toyyibpay_bill_code' => $billCode]);

        return redirect()->away($toyyibpay->billUrl($billCode));
    }
}
