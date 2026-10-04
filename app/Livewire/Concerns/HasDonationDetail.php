<?php

namespace App\Livewire\Concerns;

use App\Models\ActivityLog;
use App\Models\Donation;
use Illuminate\Support\Collection;

trait HasDonationDetail
{
    public ?int $detailId = null;

    public function toggleDetail(int $donationId): void
    {
        $this->detailId = $this->detailId === $donationId ? null : $donationId;
    }

    /**
     * @return Collection<int, ActivityLog>
     */
    protected function detailTrail(): Collection
    {
        if ($this->detailId === null) {
            return collect();
        }

        return ActivityLog::query()
            ->with('user')
            ->where('subject_type', Donation::class)
            ->where('subject_id', $this->detailId)
            ->orderBy('id')
            ->get();
    }
}
