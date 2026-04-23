<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TechnicianWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $technician;
    public $password;

    public function __construct(User $technician, string $password)
    {
        $this->technician = $technician;
        $this->password   = $password;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenue chez Geir - Votre compte technicien est créé',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.technician-welcome',
        );
    }
}