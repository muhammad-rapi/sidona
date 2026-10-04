<?php

namespace App\Livewire\Tickets;

use App\Enums\TicketStatus;
use App\Mail\TicketMail;
use App\Models\ActivityLog;
use App\Models\Ticket;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Tiket bantuan'])]
class TicketShow extends Component
{
    public Ticket $ticket;

    public string $body = '';

    public string $newStatus = 'waiting';

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    private function authorizeHandling(): void
    {
        abort_unless(auth()->user()->canManageCampaigns(), 403);
    }

    public function reply(AuditLogger $logger): void
    {
        $this->authorizeHandling();

        $data = $this->validate([
            'body' => ['required', 'string', 'min:3', 'max:3000'],
            'newStatus' => ['required', 'in:open,waiting,closed'],
        ], ['body.required' => 'Tulis balasan dulu.']);

        $message = $this->ticket->messages()->create([
            'user_id' => auth()->id(),
            'author_name' => auth()->user()->name,
            'is_staff' => true,
            'body' => trim($data['body']),
        ]);

        $before = $this->ticket->status;
        $this->ticket->update([
            'status' => $data['newStatus'],
            'last_activity_at' => now(),
            'assigned_to' => $this->ticket->assigned_to ?? auth()->id(),
        ]);

        $logger->log('ticket.replied', auth()->user(), $this->ticket, [], ['by' => 'staff', 'status' => $data['newStatus']]);

        if ($before->value !== $data['newStatus']) {
            $logger->log('ticket.status_changed', auth()->user(), $this->ticket, ['status' => $before->value], ['status' => $data['newStatus']]);
        }

        Mail::to($this->ticket->email)->send(new TicketMail($this->ticket, 'reply', $message));

        $this->reset('body');
        session()->flash('status', 'Balasan terkirim ke '.$this->ticket->email.'.');
    }

    public function setStatus(string $status, AuditLogger $logger): void
    {
        $this->authorizeHandling();
        abort_unless(in_array($status, array_column(TicketStatus::cases(), 'value'), true), 422);

        $before = $this->ticket->status->value;

        if ($before === $status) {
            return;
        }

        $this->ticket->update(['status' => $status, 'last_activity_at' => now()]);
        $logger->log('ticket.status_changed', auth()->user(), $this->ticket, ['status' => $before], ['status' => $status]);
    }

    public function assignToMe(AuditLogger $logger): void
    {
        $this->authorizeHandling();

        $before = $this->ticket->assigned_to;
        $this->ticket->update(['assigned_to' => $before === auth()->id() ? null : auth()->id()]);
        $logger->log('ticket.assigned', auth()->user(), $this->ticket, ['assigned_to' => $before], ['assigned_to' => $this->ticket->assigned_to]);
    }

    public function render()
    {
        return view('livewire.tickets.ticket-show', [
            'messages' => $this->ticket->messages()->get(),
            'canHandle' => auth()->user()->canManageCampaigns(),
            'trail' => ActivityLog::query()->with('user')->where('subject_type', Ticket::class)->where('subject_id', $this->ticket->id)->orderBy('id')->get(),
        ]);
    }
}
