<?php

namespace App\Livewire;

use App\Enums\CampaignStatus;
use App\Enums\DisbursementStatus;
use App\Enums\DonationStatus;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Donation;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
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
            'recentDonations' => Donation::query()->with('campaign')
                ->where('status', DonationStatus::Verified)
                ->latest('paid_at')->latest('id')->limit(8)->get(),
        ]);
    }
}
