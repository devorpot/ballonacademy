<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivationInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $link;
    public string $code;

    public function __construct(string $name, string $link, string $code)
    {
        $this->name = $name;
        $this->link = $link;
        $this->code = $code;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Completa tu registro en Academia Internacional de Globos',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.activation',           // HTML
            text: 'emails.activation_text',      // Alternativa de texto plano (opcional pero recomendado)
            with: [
                'name' => $this->name,
                'link' => $this->link,
                'code' => $this->code,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
