<?php

namespace App\Livewire\Audit;

use App\Services\AuditLogger;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class VerifyIntegrity extends Component
{
    public ?array $result = null;

    public function verify(AuditLogger $logger): void
    {
        $this->result = $logger->verifyChain();
    }

    public function render()
    {
        return view('livewire.audit.verify-integrity');
    }
}
