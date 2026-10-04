<?php

namespace App\Mail;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ProposalVerifyMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $url;

    public function __construct(public Campaign $campaign)
    {
        $this->url = URL::temporarySignedRoute('program.verify', now()->addDays(2), [
            'code' => $campaign->proposal_code,
            'hash' => sha1((string) $campaign->proposer_contact),
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Konfirmasi email pengajuan program - SIDONA');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.proposal-verify');
    }
}
