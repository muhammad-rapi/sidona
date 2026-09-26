<?php

namespace App\Livewire\Donations;

use App\Enums\DonationStatus;
use App\Livewire\Concerns\HasRejectionWorkflow;
use App\Mail\DonationVerifiedMail;
use App\Models\Donation;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class DonationIndex extends Component
{
    use HasRejectionWorkflow;
    use WithPagination;

    #[Url]
    public string $status = 'pending';

    public function verify(int $donationId, AuditLogger $logger): void
    {
        $donation = DB::transaction(function () use ($donationId, $logger) {
            $donation = Donation::query()->lockForUpdate()->findOrFail($donationId);

            Gate::authorize('verify', $donation);

            $before = $donation->only(['status', 'verified_by', 'verified_at']);

            $donation->update([
                'status' => DonationStatus::Verified,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            $logger->log('donation.verified', auth()->user(), $donation, $before, $donation->only(['status', 'verified_by', 'verified_at']));

            return $donation;
        });

        if (filter_var($donation->donor_contact, FILTER_VALIDATE_EMAIL)) {
            Mail::to($donation->donor_contact)->send(new DonationVerifiedMail($donation->load('campaign')));
        }

        session()->flash('status', 'Donasi diverifikasi.');
    }

    public function confirmReject(AuditLogger $logger): void
    {
        $this->validate([
            'rejectionReason' => ['required', 'string', 'min:3'],
        ]);

        DB::transaction(function () use ($logger) {
            $donation = Donation::query()->lockForUpdate()->findOrFail($this->rejectingId);

            Gate::authorize('reject', $donation);

            $before = $donation->only(['status', 'verified_by', 'verified_at', 'rejection_reason']);

            $donation->update([
                'status' => DonationStatus::Rejected,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'rejection_reason' => $this->rejectionReason,
            ]);

            $logger->log('donation.rejected', auth()->user(), $donation, $before, $donation->only(['status', 'verified_by', 'verified_at', 'rejection_reason']));
        });

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
