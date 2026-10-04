<?php

namespace App\Livewire\Public;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public', ['title' => 'Percakapan bantuan — SIDONA'])]
class TicketThread extends Component
{
    public Ticket $ticket;

    public string $body = '';

    public function mount(string $token): void
    {
        $this->ticket = Ticket::query()->where('token', $token)->firstOrFail();
    }

    public function reply(AuditLogger $logger): void
    {
        $key = 'ticket-reply:'.$this->ticket->id;

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('body', 'Terlalu banyak balasan dalam waktu singkat. Coba lagi nanti.');

            return;
        }

        $data = $this->validate(['body' => ['required', 'string', 'min:3', 'max:3000']], [
            'body.required' => 'Tulis balasan Anda dulu.',
            'body.min' => 'Balasan terlalu pendek.',
        ]);
        RateLimiter::hit($key, 3600);

        $this->ticket->messages()->create(['author_name' => $this->ticket->name, 'is_staff' => false, 'body' => trim($data['body'])]);

        $before = $this->ticket->status;
        $this->ticket->update(['status' => TicketStatus::Open, 'last_activity_at' => now()]);

        $logger->log('ticket.replied', null, $this->ticket, [], ['by' => 'requester', 'reopened' => $before === TicketStatus::Closed]);

        $this->reset('body');
    }

    public function render()
    {
        $this->ticket->refresh();

        return view('livewire.public.ticket-thread', ['messages' => $this->ticket->messages()->get()]);
    }
}
