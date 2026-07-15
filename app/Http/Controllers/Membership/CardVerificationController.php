<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Models\MemberCard;
use Illuminate\View\View;

class CardVerificationController extends Controller
{
    /**
     * Public QR verification — deliberately returns only minimal data
     * (name, member no., status). No email/phone/other personal data exposed.
     */
    public function show(string $qrToken): View
    {
        $memberCard = MemberCard::with('user')
            ->where('qr_token', $qrToken)
            ->first();

        if (! $memberCard || $memberCard->user->status !== 'approved') {
            return view('membership.verify', ['result' => null]);
        }

        $isExpired = $memberCard->expires_at->isPast();

        return view('membership.verify', [
            'result' => [
                'name' => $memberCard->user->name,
                'member_no' => $memberCard->user->member_no,
                'expired' => $isExpired,
            ],
        ]);
    }
}
