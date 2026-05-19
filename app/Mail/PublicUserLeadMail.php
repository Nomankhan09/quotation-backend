<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PublicUserLeadMail extends Mailable
{
    use Queueable, SerializesModels;
    public $publicUser;

    public function __construct($publicUser)
    {
        $this->publicUser = $publicUser;
    }

    public function build()
    {
        return $this->subject(
            'New Manual Signup Lead'
        )->view(
            'emails.public-user-lead'
        );
    }
}
