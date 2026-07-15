<?php

namespace App\Actions\Membership;

use App\Models\MemberCard;
use App\Models\User;
use Endroid\QrCode\Builder\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Render the member card (PDF) + qr_token for public verification.
 * See DATABASE.md §4, ROADMAP.md Phase 3, TRD.md §3.3-3.4.
 */
class GenerateMemberCard
{
    public function __invoke(User $user): MemberCard
    {
        $qrToken = Str::random(40);

        $memberCard = MemberCard::updateOrCreate(
            ['user_id' => $user->id],
            [
                'card_number' => $user->member_no,
                'qr_token' => $qrToken,
                'issued_at' => now()->toDateString(),
                'expires_at' => $user->renewal_expires_at ?? now()->addYear(),
            ]
        );

        $qrDataUri = (new Builder(
            data: route('verify.card', $memberCard->qr_token),
            size: 220,
            margin: 0,
        ))->build()->getDataUri();

        $pdf = app('dompdf.wrapper')
            ->loadView('pdf.member-card', [
                'user' => $user,
                'qrDataUri' => $qrDataUri,
                'memberCard' => $memberCard,
            ])
            ->setPaper([0, 0, 340, 214]);

        $filePath = "cards/{$user->member_no}.pdf";
        Storage::put($filePath, $pdf->output());

        $memberCard->forceFill(['file_path' => $filePath])->save();

        return $memberCard;
    }
}
