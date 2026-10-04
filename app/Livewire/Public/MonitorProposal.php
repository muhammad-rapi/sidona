<?php

namespace App\Livewire\Public;

use App\Enums\DisbursementStatus;
use App\Enums\DonationStatus;
use App\Models\Campaign;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public', ['title' => 'Pantau program — SIDONA'])]
class MonitorProposal extends Component
{
    public Campaign $campaign;

    public function mount(string $token): void
    {
        $this->campaign = Campaign::query()->where('monitor_token', $token)->firstOrFail();
    }

    public function render()
    {
        $donations = $this->campaign->donations()->where('status', DonationStatus::Verified);

        return view('livewire.public.monitor-proposal', [
            'raised' => $this->campaign->verifiedDonationsTotal(),
            'donorCount' => (clone $donations)->count(),
            'recent' => (clone $donations)->latest('paid_at')->latest('id')->limit(10)->get(),
            'disbursements' => $this->campaign->disbursements()->where('status', DisbursementStatus::Approved)->latest('reviewed_at')->get(),
            'disbursed' => $this->campaign->approvedDisbursementsTotal(),
            'balance' => $this->campaign->availableBalance(),
        ]);
    }
}
