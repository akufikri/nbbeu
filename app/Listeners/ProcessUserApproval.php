<?php

namespace App\Listeners;

use App\Actions\Membership\GenerateCertificate;
use App\Actions\Membership\GenerateMemberCard;
use App\Actions\Membership\GenerateMemberNumber;
use App\Events\UserApproved;
use App\Mail\MemberApprovedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Steps run in order inside a single queued job (rather than 4 separate
 * queued listeners) so the sequence — member number → card → certificate → email
 * — is guaranteed, since Laravel doesn't order independently queued listeners.
 */
class ProcessUserApproval implements ShouldQueue
{
    public function __construct(
        private GenerateMemberNumber $generateMemberNumber,
        private GenerateMemberCard $generateMemberCard,
        private GenerateCertificate $generateCertificate,
    ) {}

    public function handle(UserApproved $event): void
    {
        $user = $event->user;

        ($this->generateMemberNumber)($user);
        $memberCard = ($this->generateMemberCard)($user);
        $certificate = ($this->generateCertificate)($user);

        Mail::to($user->email)->send(new MemberApprovedMail($user, $memberCard, $certificate));
    }
}
