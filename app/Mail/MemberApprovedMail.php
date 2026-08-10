<?php

namespace App\Mail;

use App\Models\Certificate;
use App\Models\MemberCard;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public MemberCard $memberCard,
        public Certificate $certificate,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'NBBEU Membership Application Approved',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.member-approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
