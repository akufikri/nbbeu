<?php

namespace App\Jobs;

use App\Mail\RenewalReminderMail;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

/**
 * Daily reminder 30 and 7 days before renewal_expires_at.
 * Deduped via audit_logs (action renewal.reminder_30 / renewal.reminder_7)
 * so it doesn't re-send every day the job runs.
 */
class SendRenewalReminders implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $this->sendWindow(30, now()->addDays(23), now()->addDays(30));
        $this->sendWindow(7, now(), now()->addDays(7));
    }

    private function sendWindow(int $daysLeft, \DateTimeInterface $from, \DateTimeInterface $until): void
    {
        $action = "renewal.reminder_{$daysLeft}";

        $alreadyNotifiedIds = AuditLog::where('action', $action)
            ->where('subject_type', User::class)
            ->pluck('subject_id');

        User::where('status', 'approved')
            ->whereBetween('renewal_expires_at', [$from->format('Y-m-d'), $until->format('Y-m-d')])
            ->whereNotIn('id', $alreadyNotifiedIds)
            ->each(function (User $user) use ($daysLeft, $action) {
                Mail::to($user->email)->send(new RenewalReminderMail($user, $daysLeft));

                AuditLog::create([
                    'action' => $action,
                    'subject_type' => User::class,
                    'subject_id' => $user->id,
                ]);
            });
    }
}
