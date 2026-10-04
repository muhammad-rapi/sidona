<?php

namespace App\Livewire\Donations;

use App\Enums\DonationStatus;
use App\Livewire\Concerns\HasDonationDetail;
use App\Models\Donation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

    /**
     * Total donasi berhasil per hari (zona Jakarta) untuk hari-hari yang tampil di halaman ini.
     *
     * @param  Collection<int, Donation>  $donations
     * @return array<string, array{count: int, sum: int}>
     */
    private function dayTotals($donations): array
    {
        $totals = [];

        foreach ($donations->map(fn (Donation $d) => ($d->paid_at ?? $d->created_at)->timezone('Asia/Jakarta')->toDateString())->unique() as $date) {
            $start = Carbon::parse($date, 'Asia/Jakarta')->startOfDay()->utc();
            $end = Carbon::parse($date, 'Asia/Jakarta')->endOfDay()->utc();

            $paid = Donation::query()->where('status', DonationStatus::Verified)->whereBetween('paid_at', [$start, $end]);
            $totals[$date] = ['count' => (clone $paid)->count(), 'sum' => (int) $paid->sum('amount')];
        }

        return $totals;
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

        $page = $query->paginate(15);

        return view('livewire.donations.donation-index', [
            'dayTotals' => $this->dayTotals($page->getCollection()),
            'donations' => $page,
            'trail' => $this->detailTrail(),
            'paidTotal' => (int) Donation::where('status', DonationStatus::Verified)->sum('amount'),
            'pendingCount' => Donation::where('status', DonationStatus::Pending)->count(),
        ]);
    }
}
