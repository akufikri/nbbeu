<?php

namespace App\Listeners;

use App\Events\UserApproved;
use App\Models\AuditLog;

class LogUserApprovalAudit
{
    public function handle(UserApproved $event): void
    {
        AuditLog::create([
            'user_id' => $event->approvedBy->id,
            'action' => 'member.approved',
            'subject_type' => $event->user::class,
            'subject_id' => $event->user->id,
        ]);
    }
}
