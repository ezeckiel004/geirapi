<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $client;
    public $password;

    public function __construct(User $client, string $password)
    {
        $this->client = $client;
        $this->password = $password;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenue chez Geir - Votre compte client est créé',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client-welcome',
        );
    }
}