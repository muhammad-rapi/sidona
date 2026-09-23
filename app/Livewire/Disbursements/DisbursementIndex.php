<?php

namespace App\Livewire\Disbursements;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class DisbursementIndex extends Component
{
    public function render()
    {
        return view('livewire.disbursements.disbursement-index');
    }
}
