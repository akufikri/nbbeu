<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RenewalReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public int $daysLeft) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your NBBEU Membership Expires in {$this->daysLeft} Days",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.renewal-reminder',
        );
    }
}
