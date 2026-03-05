<?php

namespace App\Mail;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class IncidentAnalyzedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Incident $incident) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[{$this->incident->severity}] {$this->incident->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.incident-analyzed',
        );
    }
}