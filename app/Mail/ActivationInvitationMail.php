<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivationInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $link,
        public string $code
    ) {}

    public function build()
    {
        return $this->subject('Completa tu registro en Academia Internacional de Globos')
            ->text('emails.activation_invitation_plain'); // plantilla de texto plano
    }
}
