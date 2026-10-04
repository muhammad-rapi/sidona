<?php

namespace App\Livewire;

use App\Enums\CampaignStatus;
use App\Enums\DisbursementStatus;
use App\Enums\DonationStatus;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Donation;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    /**
     * Hal yang menunggu keputusan manusia, yang tertua lebih dulu.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function decisionQueue(): Collection
    {
        $proposals = Campaign::query()->where('status', CampaignStatus::Pending)->get()
            ->map(fn (Campaign $c) => [
                'kind' => 'Pengajuan program',
                'title' => $c->name,
                'meta' => 'oleh '.$c->proposer_name.($c->proposerNeedsVerification() ? ', email belum dikonfirmasi' : ''),
                'amount' => $c->target_amount,
                'amount_label' => 'target',
                'at' => $c->created_at,
                'url' => route('campaigns.index'),
            ]);

        $disbursements = Disbursement::query()->with(['campaign', 'submitter'])
            ->where('status', DisbursementStatus::Submitted)->get()
            ->map(fn (Disbursement $d) => [
                'kind' => 'Penyaluran dana',
                'title' => $d->campaign->name,
                'meta' => 'diajukan '.$d->submitter->name,
                'amount' => $d->amount,
                'amount_label' => 'diminta',
                'at' => $d->created_at,
                'url' => route('disbursements.index'),
            ]);

        return $proposals->concat($disbursements)->sortBy('at')->values()->take(8);
    }

    public function render()
    {
        $paid = Donation::query()->where('status', DonationStatus::Verified);
        $approvedOut = (int) Disbursement::query()->where('status', DisbursementStatus::Approved)->sum('amount');
        $totalIn = (int) (clone $paid)->sum('amount');

        return view('livewire.dashboard', [
            'totalIn' => $totalIn,
            'totalOut' => $approvedOut,
            'balance' => $totalIn - $approvedOut,
            'todayCount' => (clone $paid)->whereDate('paid_at', today())->count(),
            'todayTotal' => (int) (clone $paid)->whereDate('paid_at', today())->sum('amount'),
            'activeCampaigns' => Campaign::where('status', CampaignStatus::Active)->count(),
            'pendingProposals' => Campaign::where('status', CampaignStatus::Pending)->count(),
            'pendingDisbursements' => Disbursement::where('status', DisbursementStatus::Submitted)->count(),
            'queue' => $this->decisionQueue(),
            'runningCampaigns' => Campaign::query()
                ->where('status', CampaignStatus::Active)
                ->withSum(['donations as raised_sum' => fn ($q) => $q->where('status', DonationStatus::Verified)], 'amount')
                ->with('pic')
                ->orderByDesc('raised_sum')
                ->limit(6)
                ->get(),
            'recentDonations' => Donation::query()->with('campaign')
                ->where('status', DonationStatus::Verified)
                ->latest('paid_at')->latest('id')->limit(8)->get(),
        ]);
    }
}
