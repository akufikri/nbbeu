<?php

namespace App\Actions\Membership;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Render the membership certificate (PDF).
 * See DATABASE.md §5, ROADMAP.md Phase 3, TRD.md §3.3.
 */
class GenerateCertificate
{
    public function __invoke(User $user): Certificate
    {
        $certNumber = "CERT-{$user->member_no}";

        $pdf = app('dompdf.wrapper')
            ->loadView('pdf.certificate', [
                'user' => $user,
                'certNumber' => $certNumber,
            ])
            ->setPaper('a4', 'landscape');

        $filePath = "certificates/{$user->member_no}.pdf";
        Storage::put($filePath, $pdf->output());

        return Certificate::updateOrCreate(
            ['user_id' => $user->id],
            [
                'cert_number' => $certNumber,
                'file_path' => $filePath,
                'issued_at' => now()->toDateString(),
            ]
        );
    }
}
