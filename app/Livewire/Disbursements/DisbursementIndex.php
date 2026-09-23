<?php

namespace App\Livewire\Disbursements;

use App\Enums\DisbursementStatus;
use App\Livewire\Concerns\HasRejectionWorkflow;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class DisbursementIndex extends Component
{
    use HasRejectionWorkflow;

    #[Url]
    public string $status = 'submitted';

    public function approve(int $disbursementId, AuditLogger $logger): void
    {
        DB::transaction(function () use ($disbursementId, $logger) {
            $disbursement = Disbursement::query()->lockForUpdate()->findOrFail($disbursementId);

            Gate::authorize('approve', $disbursement);

            $campaign = Campaign::query()->lockForUpdate()->findOrFail($disbursement->campaign_id);

            if ($disbursement->amount > $campaign->availableBalance()) {
                throw ValidationException::withMessages([
                    'approve' => 'Saldo program tidak lagi mencukupi untuk menyetujui penyaluran ini.',
                ]);
            }

            $before = $disbursement->only(['status', 'reviewed_by', 'reviewed_at']);

            $disbursement->update([
                'status' => DisbursementStatus::Approved,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            $logger->log('disbursement.approved', auth()->user(), $disbursement, $before, $disbursement->only(['status', 'reviewed_by', 'reviewed_at']));
        });

        session()->flash('status', 'Penyaluran disetujui.');
    }

    public function confirmReject(AuditLogger $logger): void
    {
        $this->validate([
            'rejectionReason' => ['required', 'string', 'min:3'],
        ]);

        DB::transaction(function () use ($logger) {
            $disbursement = Disbursement::query()->lockForUpdate()->findOrFail($this->rejectingId);

            Gate::authorize('reject', $disbursement);

            $before = $disbursement->only(['status', 'reviewed_by', 'reviewed_at', 'rejection_reason']);

            $disbursement->update([
                'status' => DisbursementStatus::Rejected,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'rejection_reason' => $this->rejectionReason,
            ]);

            $logger->log('disbursement.rejected', auth()->user(), $disbursement, $before, $disbursement->only(['status', 'reviewed_by', 'reviewed_at', 'rejection_reason']));
        });

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
