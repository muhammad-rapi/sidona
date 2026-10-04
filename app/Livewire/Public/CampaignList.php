<?php

namespace App\Livewire\Public;

use App\Enums\CampaignStatus;
use App\Enums\DonationStatus;
use App\Models\Campaign;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.public')]
class CampaignList extends Component
{
    use WithPagination;

    #[Url(as: 'cari')]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.public.campaign-list', [
            'campaigns' => Campaign::query()
                ->with('pic')
                ->where('status', CampaignStatus::Active)
                ->when(trim($this->search) !== '', fn ($q) => $q->where(fn ($q) => $q
                    ->where('name', 'like', '%'.trim($this->search).'%')
                    ->orWhere('description', 'like', '%'.trim($this->search).'%')))
                ->withSum(['donations as raised_sum' => fn ($q) => $q->where('status', DonationStatus::Verified)], 'amount')
                ->withCount(['donations as donor_count' => fn ($q) => $q->where('status', DonationStatus::Verified)])
                ->latest()
                ->paginate(10),
        ]);
    }
}
