<?php

namespace App\Livewire\Public;

use App\Models\Donation;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class DonationStatusCheck extends Component
{
    public string $reference_code = '';

    public ?Donation $result = null;

    public bool $searched = false;

    public function check(): void
    {
        $this->validate([
            'reference_code' => ['required', 'string'],
        ]);

        $this->result = Donation::query()
            ->with('campaign')
            ->where('reference_code', $this->reference_code)
            ->first();

        $this->searched = true;
    }

    public function render()
    {
        return view('livewire.public.donation-status-check');
    }
}
