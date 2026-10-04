<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Ticket $ticket, public string $kind, public ?TicketMessage $reply = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: match ($this->kind) {
            'reply' => "Balasan untuk tiket {$this->ticket->code} - SIDONA",
            'link' => "Tautan tiket {$this->ticket->code} - SIDONA",
            default => "Tiket bantuan {$this->ticket->code} sudah kami terima - SIDONA",
        });
    }

    public function content(): Content
    {
        return new Content(view: 'mail.ticket');
    }
}
