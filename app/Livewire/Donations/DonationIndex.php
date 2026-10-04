<?php

namespace App\Livewire\Donations;

use App\Enums\DonationStatus;
use App\Livewire\Concerns\HasDonationDetail;
use App\Models\Donation;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class DonationIndex extends Component
{
    use HasDonationDetail;
    use WithPagination;

    #[Url]
    public string $status = 'all';

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
        $query = Donation::query()->with('campaign')->latest();

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(fn ($q) => $q
                ->where('donor_name', 'like', $term)
                ->orWhere('reference_code', 'like', $term)
                ->orWhere('donor_contact', 'like', $term));
        }

        return view('livewire.donations.donation-index', [
            'donations' => $query->paginate(15),
            'trail' => $this->detailTrail(),
            'paidTotal' => (int) Donation::where('status', DonationStatus::Verified)->sum('amount'),
            'pendingCount' => Donation::where('status', DonationStatus::Pending)->count(),
        ]);
    }
}
