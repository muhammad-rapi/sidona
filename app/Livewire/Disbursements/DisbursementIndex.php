<?php

namespace App\Livewire\Disbursements;

use App\Enums\DisbursementStatus;
use App\Models\Disbursement;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class DisbursementIndex extends Component
{
    #[Url]
    public string $status = 'submitted';

    public ?int $rejectingId = null;

    public string $rejectionReason = '';

    public function approve(int $disbursementId, AuditLogger $logger): void
    {
        $disbursement = Disbursement::findOrFail($disbursementId);

        Gate::authorize('approve', $disbursement);

        $before = $disbursement->only(['status', 'reviewed_by', 'reviewed_at']);

        $disbursement->update([
            'status' => DisbursementStatus::Approved,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $logger->log('disbursement.approved', auth()->user(), $disbursement, $before, $disbursement->only(['status', 'reviewed_by', 'reviewed_at']));

        session()->flash('status', 'Penyaluran disetujui.');
    }

    public function startReject(int $disbursementId): void
    {
        $this->rejectingId = $disbursementId;
        $this->rejectionReason = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingId = null;
        $this->rejectionReason = '';
    }

    public function confirmReject(AuditLogger $logger): void
    {
        $disbursement = Disbursement::findOrFail($this->rejectingId);

        Gate::authorize('reject', $disbursement);

        $this->validate([
            'rejectionReason' => ['required', 'string', 'min:3'],
        ]);

        $before = $disbursement->only(['status', 'reviewed_by', 'reviewed_at', 'rejection_reason']);

        $disbursement->update([
            'status' => DisbursementStatus::Rejected,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $this->rejectionReason,
        ]);

        $logger->log('disbursement.rejected', auth()->user(), $disbursement, $before, $disbursement->only(['status', 'reviewed_by', 'reviewed_at', 'rejection_reason']));

        $this->rejectingId = null;
        $this->rejectionReason = '';

        session()->flash('status', 'Penyaluran ditolak.');
    }

    public function render()
    {
        $query = Disbursement::query()->with(['campaign', 'submitter'])->latest();

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return view('livewire.disbursements.disbursement-index', [
            'disbursements' => $query->paginate(15),
        ]);
    }
}
