<?php

namespace App\Livewire\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Tiket bantuan'])]
class TicketIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $status = 'active';

    #[Url]
    public string $search = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $tickets = Ticket::query()->with(['assignee', 'messages'])
            ->when($this->status === 'active', fn ($q) => $q->whereIn('status', [TicketStatus::Open, TicketStatus::Waiting]))
            ->when(in_array($this->status, ['open', 'waiting', 'closed'], true), fn ($q) => $q->where('status', $this->status))
            ->when(trim($this->search) !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('subject', 'like', '%'.trim($this->search).'%')
                ->orWhere('code', 'like', '%'.trim($this->search).'%')
                ->orWhere('name', 'like', '%'.trim($this->search).'%')
                ->orWhere('email', 'like', '%'.trim($this->search).'%')))
            ->orderByRaw("case status when 'open' then 0 when 'waiting' then 1 else 2 end")
            ->orderBy('last_activity_at')
            ->paginate(15);

        return view('livewire.tickets.ticket-index', ['tickets' => $tickets]);
    }
}
