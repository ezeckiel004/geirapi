<?php

namespace App\Mail;

use App\Models\Intervention;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterventionStartedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $intervention;
    public $technician;

    public function __construct(Intervention $intervention, User $technician)
    {
        $this->intervention = $intervention;
        $this->technician   = $technician;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Intervention démarrée - #{$this->intervention->id}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.intervention-started',
        );
    }
}