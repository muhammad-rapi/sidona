<?php

namespace App\Livewire\Campaigns;

use App\Enums\CampaignStatus;
use App\Enums\DisbursementStatus;
use App\Enums\DonationStatus;
use App\Livewire\Concerns\HasRejectionWorkflow;
use App\Mail\ProposalDecisionMail;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class CampaignIndex extends Component
{
    use HasRejectionWorkflow;
    use WithPagination;

    public function delete(Campaign $campaign, AuditLogger $logger): void
    {
        Gate::authorize('delete', $campaign);

        $before = $campaign->only(['name', 'description', 'target_amount', 'starts_on', 'ends_on', 'status']);
        $campaign->delete();
        $logger->log('campaign.deleted', auth()->user(), $campaign, $before, []);

        session()->flash('status', 'Program donasi dihapus.');
    }

    public ?int $detailId = null;

    public function toggleDetail(int $campaignId): void
    {
        $this->detailId = $this->detailId === $campaignId ? null : $campaignId;
    }

    public function approve(int $campaignId, AuditLogger $logger): void
    {
        $campaign = Campaign::query()->findOrFail($campaignId);
        Gate::authorize('review', $campaign);

        $before = $campaign->only(['status', 'starts_on', 'ends_on']);
        $days = max(1, (int) $campaign->starts_on->diffInDays($campaign->ends_on));

        $campaign->update([
            'status' => CampaignStatus::Active,
            'starts_on' => today(),
            'ends_on' => today()->addDays($days),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        $logger->log('campaign.approved', auth()->user(), $campaign, $before, $campaign->only(['status', 'starts_on', 'ends_on']));

        $this->notifyProposer($campaign);

        session()->flash('status', 'Program disetujui dan sekarang tayang.');
    }

    public function confirmReject(AuditLogger $logger): void
    {
        $this->validate(['rejectionReason' => ['required', 'string', 'min:3', 'max:255']]);

        $campaign = Campaign::query()->findOrFail($this->rejectingId);
        Gate::authorize('reject', $campaign);

        $before = $campaign->only(['status', 'rejection_reason']);

        $campaign->update([
            'status' => CampaignStatus::Rejected,
            'rejection_reason' => $this->rejectionReason,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $logger->log('campaign.rejected', auth()->user(), $campaign, $before, $campaign->only(['status', 'rejection_reason']));

        $this->cancelReject();
        $this->notifyProposer($campaign->refresh());
        session()->flash('status', 'Pengajuan program ditolak.');
    }

    private function notifyProposer(Campaign $campaign): void
    {
        if ($campaign->proposer_contact && filter_var($campaign->proposer_contact, FILTER_VALIDATE_EMAIL)) {
            Mail::to($campaign->proposer_contact)->send(new ProposalDecisionMail($campaign));
        }
    }

    public function render()
    {
        $trail = $this->detailId
            ? ActivityLog::query()->with('user')
                ->where('subject_type', Campaign::class)->where('subject_id', $this->detailId)
                ->orderBy('id')->get()
            : collect();

        return view('livewire.campaigns.campaign-index', [
            'trail' => $trail,
            'campaigns' => Campaign::query()
                ->with('pic')
                ->withSum(['donations as raised_sum' => fn ($q) => $q->where('status', DonationStatus::Verified)], 'amount')
                ->withSum(['disbursements as disbursed_sum' => fn ($q) => $q->where('status', DisbursementStatus::Approved)], 'amount')
                ->orderByRaw("case when status = 'pending' then 0 else 1 end")
                ->latest()
                ->paginate(10),
        ]);
    }
}
