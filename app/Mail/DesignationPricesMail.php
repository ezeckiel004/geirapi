<?php

namespace App\Mail;

use App\Models\Report;
use App\Models\Intervention;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DesignationPricesMail extends Mailable
{
    use Queueable, SerializesModels;

    public $report;
    public $intervention;
    public $defectiveItems;
    public $totalPrice;

    public function __construct(Report $report, Intervention $intervention)
    {
        $this->report       = $report;
        $this->intervention = $intervention;

        // Filtrer les désignations non fonctionnelles avec un prix
        $designations = $report->designations ?? [];
        $this->defectiveItems = collect($designations)
            ->filter(fn($item) => ($item['status'] ?? true) === false && !empty($item['price']))
            ->toArray();

        $this->totalPrice = collect($this->defectiveItems)
            ->sum(fn($item) => (float) ($item['price'] ?? 0));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Devis de réparation - Intervention #{$this->intervention->id}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.designation-prices',
        );
    }
}
