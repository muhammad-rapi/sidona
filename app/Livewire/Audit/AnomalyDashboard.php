<?php

namespace App\Livewire\Audit;

use App\Services\AnomalyDetector;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AnomalyDashboard extends Component
{
    public function render(AnomalyDetector $detector)
    {
        return view('livewire.audit.anomaly-dashboard', [
            'extremeDonations' => $detector->extremeDonations(),
            'failedLoginStreaks' => $detector->failedLoginStreaks(),
            'fastApprovedDisbursements' => $detector->fastApprovedDisbursements(),
        ]);
    }
}
