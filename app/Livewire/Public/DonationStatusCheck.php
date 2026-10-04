<?php

namespace App\Livewire\Public;

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

        $donation = Donation::query()
            ->where('reference_code', strtoupper(trim($this->reference_code)))
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
