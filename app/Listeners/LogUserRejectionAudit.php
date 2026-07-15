<?php

namespace App\Listeners;

use App\Events\UserRejected;
use App\Models\AuditLog;

class LogUserRejectionAudit
{
    public function handle(UserRejected $event): void
    {
        AuditLog::create([
            'user_id' => $event->rejectedBy->id,
            'action' => 'member.rejected',
            'subject_type' => $event->user::class,
            'subject_id' => $event->user->id,
            'meta' => ['reason' => $event->user->rejection_reason],
        ]);
    }
}
