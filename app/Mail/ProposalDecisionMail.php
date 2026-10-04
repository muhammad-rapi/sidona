<?php

namespace App\Mail;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProposalDecisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Campaign $campaign) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->status->value === 'active'
                ? 'Program Anda sudah tayang - SIDONA'
                : 'Kabar soal pengajuan program Anda - SIDONA',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.proposal-decision');
    }
}
