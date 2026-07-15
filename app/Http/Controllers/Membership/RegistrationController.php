<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegistrationRequest;
use App\Models\Payment;
use App\Models\User;
use App\Services\ToyyibpayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('membership.register');
    }

    public function store(StoreRegistrationRequest $request, ToyyibpayService $toyyibpay): RedirectResponse
    {
        $data = $request->validated();

        // If a pending application already exists for this email, reuse that
        // row (not a new duplicate) — lets the user retry payment without refilling the form.
        $user = User::where('email', $data['email'])->where('status', 'pending')->first();

        if ($user) {
            $user->fill($data)->save();
        } else {
            $user = User::create($data);
            $user->assignRole('member');
        }

        $amount = (int) config('services.toyyibpay.registration_amount');

        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => $amount / 100,
            'purpose' => 'registration',
            'status' => 'pending',
        ]);

        $billCode = $toyyibpay->createBill([
            'bill_name' => 'NBBEU Membership Registration',
            'bill_description' => "NBBEU membership registration - {$user->name}",
            'return_url' => route('registration.return', $payment),
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
