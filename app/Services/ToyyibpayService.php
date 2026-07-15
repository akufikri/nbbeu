<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ToyyibpayService
{
    protected string $secretKey;

    protected string $categoryCode;

    protected string $baseUrl;

    public function __construct()
    {
        $this->secretKey = (string) config('services.toyyibpay.secret_key');
        $this->categoryCode = (string) config('services.toyyibpay.category_code');
        $this->baseUrl = rtrim((string) config('services.toyyibpay.base_url'), '/');
    }

    /**
     * Create a Toyyibpay bill and return the bill code.
     *
     * @param  int  $amountInCents  Amount in cents (e.g. 5000 = RM50.00)
     */
    public function createBill(array $data, int $amountInCents): string
    {
        $response = Http::asForm()->post("{$this->baseUrl}/index.php/api/createBill", [
            'userSecretKey' => $this->secretKey,
            'categoryCode' => $this->categoryCode,
            'billName' => $data['bill_name'],
            'billDescription' => $data['bill_description'],
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $amountInCents,
            'billReturnUrl' => $data['return_url'],
            'billCallbackUrl' => $data['callback_url'],
            'billExternalReferenceNo' => $data['reference_no'],
            'billTo' => $data['payer_name'],
            'billEmail' => $data['payer_email'],
            'billPhone' => $data['payer_phone'],
            'billSplitPayment' => 0,
            'billPaymentChannel' => 2,
            'billChargeToCustomer' => 1,
        ]);

        $body = $response->json();

        if (! $response->successful() || empty($body[0]['BillCode'])) {
            throw new RuntimeException('Gagal membuat Toyyibpay bill: '.$response->body());
        }

        return $body[0]['BillCode'];
    }

    /**
     * Query transaction status for a bill directly from Toyyibpay (fallback,
     * used when the async callback may not have arrived).
     */
    public function getBillTransactions(string $billCode): array
    {
        $response = Http::asForm()->post("{$this->baseUrl}/index.php/api/getBillTransactions", [
            'billCode' => $billCode,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Gagal ambil status Toyyibpay bill: '.$response->body());
        }

        return $response->json() ?? [];
    }

    /**
     * True if any bill transaction is already marked paid (billpaymentStatus == 1).
     */
    public function isBillPaid(string $billCode): bool
    {
        foreach ($this->getBillTransactions($billCode) as $transaction) {
            if (($transaction['billpaymentStatus'] ?? null) === '1') {
                return true;
            }
        }

        return false;
    }

    public function billUrl(string $billCode): string
    {
        return "{$this->baseUrl}/{$billCode}";
    }
}
