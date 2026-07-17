<?php

namespace App\Actions\Membership;

use App\Models\UnionDuesMandate;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Render the union dues (salary deduction) consent letter (PDF).
 * See FEATURES.md §3B, TRD.md §3.7 part A.
 */
class GenerateUnionDuesConsentLetter
{
    public function __invoke(User $user, UnionDuesMandate $mandate): string
    {
        $pdf = app('dompdf.wrapper')
            ->loadView('pdf.union-dues-consent', [
                'user' => $user,
                'mandate' => $mandate,
                'profile' => $user->memberProfile,
            ])
            ->setPaper('a4', 'portrait');

        $filePath = "union-dues/{$user->id}-{$mandate->id}.pdf";
        Storage::put($filePath, $pdf->output());

        return $filePath;
    }
}
