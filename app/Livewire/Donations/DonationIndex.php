<?php

namespace App\Livewire\Donations;

use App\Enums\DonationStatus;
use App\Models\Donation;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class DonationIndex extends Component
{
    #[Url]
    public string $status = 'pending';

    public ?int $rejectingId = null;

    public string $rejectionReason = '';

    public function verify(int $donationId, AuditLogger $logger): void
    {
        $donation = Donation::findOrFail($donationId);

        Gate::authorize('verify', $donation);

        $before = $donation->only(['status', 'verified_by', 'verified_at']);

        $donation->update([
            'status' => DonationStatus::Verified,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $logger->log('donation.verified', auth()->user(), $donation, $before, $donation->only(['status', 'verified_by', 'verified_at']));

        session()->flash('status', 'Donasi diverifikasi.');
    }

    public function startReject(int $donationId): void
    {
        $this->rejectingId = $donationId;
        $this->rejectionReason = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingId = null;
        $this->rejectionReason = '';
    }

    public function confirmReject(AuditLogger $logger): void
    {
        $donation = Donation::findOrFail($this->rejectingId);

        Gate::authorize('reject', $donation);

        $this->validate([
            'rejectionReason' => ['required', 'string', 'min:3'],
        ]);

        $before = $donation->only(['status', 'verified_by', 'verified_at', 'rejection_reason']);

        $donation->update([
            'status' => DonationStatus::Rejected,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'rejection_reason' => $this->rejectionReason,
        ]);

        $logger->log('donation.rejected', auth()->user(), $donation, $before, $donation->only(['status', 'verified_by', 'verified_at', 'rejection_reason']));

        $this->rejectingId = null;
        $this->rejectionReason = '';

        session()->flash('status', 'Donasi ditolak.');
    }

    public function render()
    {
        $query = Donation::query()->with('campaign')->latest();

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return view('livewire.donations.donation-index', [
            'donations' => $query->paginate(15),
        ]);
    }
}
