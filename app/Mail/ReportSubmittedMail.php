<?php

namespace App\Mail;

use App\Models\Intervention;
use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $report;
    public $intervention;
    public $recipient; // 'admin' ou 'client'

    public function __construct(Report $report, Intervention $intervention, string $recipient)
    {
        $this->report       = $report;
        $this->intervention = $intervention;
        $this->recipient    = $recipient;
    }

    public function envelope(): Envelope
    {
        $subject = $this->recipient === 'admin'
            ? "Nouveau rapport soumis - #{$this->intervention->id}"
            : "Votre rapport d'intervention est disponible - #{$this->intervention->id}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.report-submitted',
        );
    }
}