<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; margin: 0; padding: 0; }
    .header { background: #1a3a5c; color: white; padding: 20px 24px; }
    .header h1 { margin: 0; font-size: 18px; letter-spacing: 1px; }
    .header p { margin: 4px 0 0; font-size: 10px; opacity: 0.8; }
    .receipt-badge { text-align: right; }
    .receipt-badge .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.7; }
    .receipt-badge .number { font-size: 14px; font-weight: bold; }
    .body { padding: 24px; }
    table.info { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    table.info td { padding: 5px 0; vertical-align: top; }
    table.info td:first-child { color: #666; width: 40%; }
    .divider { border: none; border-top: 1px solid #e0e0e0; margin: 16px 0; }
    .amount-box { background: #f0f7ff; border: 1px solid #c0d9f0; border-radius: 4px; padding: 16px; text-align: center; margin: 20px 0; }
    .amount-box .label { font-size: 10px; color: #666; text-transform: uppercase; letter-spacing: 1px; }
    .amount-box .amount { font-size: 28px; font-weight: bold; color: #1a3a5c; }
    .status-paid { display: inline-block; background: #dcfce7; color: #15803d; padding: 3px 10px; border-radius: 10px; font-size: 10px; font-weight: bold; }
    .footer { margin-top: 32px; font-size: 9px; color: #999; text-align: center; border-top: 1px solid #e0e0e0; padding-top: 12px; }
    .flex { display: flex; justify-content: space-between; align-items: flex-start; }
</style>
</head>
<body>
<div class="header">
    <div class="flex">
        <div>
            <h1>NBBEU</h1>
            <p>Kesatuan Pekerja Bank Negara</p>
        </div>
        <div class="receipt-badge">
            <div class="label">Resit Pembayaran</div>
            <div class="number">#{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>
    </div>
</div>

<div class="body">
    <div class="amount-box">
        <div class="label">Jumlah Pembayaran</div>
        <div class="amount">RM {{ number_format($payment->amount, 2) }}</div>
        <div style="margin-top: 8px">
            <span class="status-paid">{{ strtoupper($payment->status) }}</span>
        </div>
    </div>

    <table class="info">
        <tr>
            <td>Nama Ahli</td>
            <td><strong>{{ $payment->user->name }}</strong></td>
        </tr>
        <tr>
            <td>No. Ahli</td>
            <td>{{ $payment->user->member_no ?? '-' }}</td>
        </tr>
        <tr>
            <td>E-mel</td>
            <td>{{ $payment->user->email }}</td>
        </tr>
    </table>

    <hr class="divider">

    <table class="info">
        <tr>
            <td>Tujuan Bayaran</td>
            <td>{{ $payment->purpose }}</td>
        </tr>
        @if ($payment->toyyibpay_ref_no)
        <tr>
            <td>No. Rujukan</td>
            <td>{{ $payment->toyyibpay_ref_no }}</td>
        </tr>
        @endif
        <tr>
            <td>Tarikh Bayaran</td>
            <td>{{ $payment->paid_at?->format('d M Y, h:i A') ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tarikh Dicetak</td>
            <td>{{ now()->format('d M Y, h:i A') }}</td>
        </tr>
    </table>

    <div class="footer">
        Resit ini dijana secara automatik oleh sistem NBBEU. Untuk pertanyaan, hubungi {{ \App\Models\Setting::get('contact_email', 'nbbeu@nbbeu.org') }}.
    </div>
</div>
</body>
</html>
