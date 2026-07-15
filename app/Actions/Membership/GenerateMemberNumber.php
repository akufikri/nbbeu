<?php

namespace App\Actions\Membership;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Format: NBBEU-{year}-{4 digit running number}, reset every year.
 * See DATABASE.md §9 and ROADMAP.md §"Decision Order Recommendation".
 */
class GenerateMemberNumber
{
    public function __invoke(User $user): string
    {
        $year = now()->year;
        $prefix = "NBBEU-{$year}-";

        return DB::transaction(function () use ($user, $prefix) {
            $lastNumber = User::where('member_no', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderByDesc('member_no')
                ->value('member_no');

            $nextSequence = $lastNumber
                ? ((int) substr($lastNumber, strlen($prefix))) + 1
                : 1;

            $memberNo = $prefix.str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);

            $user->forceFill(['member_no' => $memberNo])->save();

            return $memberNo;
        });
    }
}
