<?php

namespace App\Livewire\Concerns;

trait HasRejectionWorkflow
{
    public ?int $rejectingId = null;

    public string $rejectionReason = '';

    public function startReject(int $id): void
    {
        $this->rejectingId = $id;
        $this->rejectionReason = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingId = null;
        $this->rejectionReason = '';
    }
}
