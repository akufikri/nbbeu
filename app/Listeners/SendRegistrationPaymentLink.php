<?php

namespace App\Listeners;

use App\Events\UserApproved;
use App\Mail\PaymentLinkMail;
use App\Models\Payment;
use App\Services\ToyyibpayService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendRegistrationPaymentLink implements ShouldQueue
{
    public function __construct(
        private ToyyibpayService $toyyibpay,
    ) {}

    public function handle(UserApproved $event): void
    {
        $user = $event->user;

        $amount = (int) config('services.toyyibpay.registration_amount');

        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => $amount / 100,
            'purpose' => 'registration',
            'status' => 'pending',
        ]);

        $billCode = $this->toyyibpay->createBill([
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

        Mail::to($user->email)->send(new PaymentLinkMail($user, $this->toyyibpay->billUrl($billCode)));
    }
}
