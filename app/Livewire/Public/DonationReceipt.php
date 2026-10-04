<?php

namespace App\Livewire\Public;

use App\Models\Donation;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class DonationReceipt extends Component
{
    public Donation $donation;

    public function mount(string $reference): void
    {
        $this->donation = Donation::query()
            ->with('campaign')
            ->where('reference_code', strtoupper($reference))
            ->firstOrFail();
    }

    public function render()
    {
        $campaign = $this->donation->campaign;
        $raised = $campaign->verifiedDonationsTotal();
        $before = $this->donation->isPaid() ? max(0, $raised - $this->donation->amount) : $raised;

        return view('livewire.public.donation-receipt', [
            'campaign' => $campaign,
            'raised' => $raised,
            'raisedBefore' => $before,
        ]);
    }
}
