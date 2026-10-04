<?php

namespace App\Livewire\Public;

use App\Models\Campaign;
use App\Models\Donation;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class DonationStatusCheck extends Component
{
    public string $reference_code = '';

    public bool $notFound = false;

    public function check()
    {
        $this->validate([
            'reference_code' => ['required', 'string'],
        ]);

        $code = strtoupper(trim($this->reference_code));

        if (str_starts_with($code, 'PRG-')) {
            $proposal = Campaign::query()->where('proposal_code', $code)->first();

            if ($proposal) {
                return $this->redirectRoute('program.proposal', $proposal->proposal_code, navigate: true);
            }

            $this->notFound = true;

            return null;
        }

        $donation = Donation::query()
            ->where('reference_code', $code)
            ->first();

        if (! $donation) {
            $this->notFound = true;

            return null;
        }

        return $this->redirectRoute('donations.receipt', $donation->reference_code, navigate: true);
    }

    public function render()
    {
        return view('livewire.public.donation-status-check');
    }
}
