<?php

namespace App\Mail;

use App\Models\Certificate;
use App\Models\MemberCard;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
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

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromStorage($this->memberCard->file_path)
                ->as("Member-Card-{$this->user->member_no}.pdf"),
            Attachment::fromStorage($this->certificate->file_path)
                ->as("Certificate-{$this->user->member_no}.pdf"),
        ];
    }
}
