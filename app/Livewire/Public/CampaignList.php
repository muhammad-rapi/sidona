<?php

namespace App\Livewire\Public;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.public')]
class CampaignList extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.public.campaign-list', [
            'campaigns' => Campaign::query()
                ->where('status', CampaignStatus::Active)
                ->latest()
                ->paginate(10),
        ]);
    }
}
