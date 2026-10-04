<?php

namespace App\Livewire\Public;

use App\Enums\CampaignStatus;
use App\Enums\DonationStatus;
use App\Models\ActivityLog;
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

    /** Jenis catatan audit yang aman ditampilkan ke publik (tanpa data pribadi). */
    private const PUBLIC_LEDGER = [
        'donation.paid' => 'Donasi masuk',
        'disbursement.submitted' => 'Penyaluran diajukan',
        'disbursement.approved' => 'Penyaluran disetujui',
        'campaign.approved' => 'Program dibuka',
    ];

    public function render()
    {
        $ledger = ActivityLog::query()
            ->whereIn('action', array_keys(self::PUBLIC_LEDGER))
            ->latest('id')
            ->limit(6)
            ->get(['id', 'action', 'prev_hash', 'hash', 'created_at'])
            ->map(fn (ActivityLog $entry) => [
                'id' => $entry->id,
                'label' => self::PUBLIC_LEDGER[$entry->action],
                'at' => $entry->created_at,
                'hash' => substr($entry->hash, 0, 12),
                'prev' => substr($entry->prev_hash, 0, 6),
            ]);

        return view('livewire.public.campaign-list', [
            'ledger' => $ledger,
            'ledgerTotal' => ActivityLog::query()->count(),
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
