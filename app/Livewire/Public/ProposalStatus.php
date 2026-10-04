<?php

namespace App\Livewire\Public;

use App\Models\Campaign;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class ProposalStatus extends Component
{
    public Campaign $campaign;

    public function mount(string $code): void
    {
        $this->campaign = Campaign::query()->where('proposal_code', strtoupper($code))->firstOrFail();
    }

    public function render()
    {
        return view('livewire.public.proposal-status');
    }
}
