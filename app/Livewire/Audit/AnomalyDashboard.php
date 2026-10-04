<?php

namespace App\Livewire\Audit;

use App\Models\ActivityLog;
use App\Models\AnomalyReview;
use App\Models\Donation;
use App\Models\LoginLog;
use App\Services\AnomalyDetector;
use App\Services\AuditLogger;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AnomalyDashboard extends Component
{
    public ?string $detailKey = null;

    public function toggleDetail(string $type, int $subjectId): void
    {
        $key = $type.':'.$subjectId;
        $this->detailKey = $this->detailKey === $key ? null : $key;
    }

    public function markChecked(string $type, int $subjectId, AnomalyDetector $detector, AuditLogger $logger): void
    {
        abort_unless(auth()->user()->canAudit(), 403);

        $flaggedIds = match ($type) {
            'donation' => $detector->extremeDonations()->pluck('id'),
            'login' => $detector->failedLoginStreaks()->pluck('id'),
            'disbursement' => $detector->fastApprovedDisbursements()->pluck('id'),
            default => abort(422),
        };

        abort_unless($flaggedIds->contains($subjectId), 422);

        $review = AnomalyReview::firstOrCreate(
            ['type' => $type, 'subject_id' => $subjectId],
            ['reviewed_by' => auth()->id(), 'reviewed_at' => now()],
        );

        $this->detailKey = null;

        if ($review->wasRecentlyCreated) {
            $logger->log('anomaly.reviewed', auth()->user(), $review, [], $review->only(['type', 'subject_id', 'reviewed_by']));
            session()->flash('status', 'Anomali ditandai sudah diperiksa.');
        }
    }

    /**
     * @return array{average: int, trail: Collection<int, ActivityLog>}|null
     */
    private function donationContext(): ?array
    {
        [$type, $id] = array_pad(explode(':', (string) $this->detailKey), 2, null);

        if ($type !== 'donation') {
            return null;
        }

        $donation = Donation::query()->find((int) $id);

        if (! $donation) {
            return null;
        }

        return [
            'average' => (int) Donation::query()->where('campaign_id', $donation->campaign_id)->avg('amount'),
            'count' => Donation::query()->where('campaign_id', $donation->campaign_id)->count(),
            'trail' => ActivityLog::query()->with('user')
                ->where('subject_type', Donation::class)->where('subject_id', $donation->id)
                ->orderBy('id')->get(),
        ];
    }

    private function loginAttempts(): Collection
    {
        [$type, $id] = array_pad(explode(':', (string) $this->detailKey), 2, null);

        if ($type !== 'login') {
            return collect();
        }

        $log = LoginLog::query()->find((int) $id);

        return $log
            ? LoginLog::query()->where('email', $log->email)
                ->where('created_at', '<=', $log->created_at)
                ->orderByDesc('created_at')->limit(8)->get()
            : collect();
    }

    public function render(AnomalyDetector $detector)
    {
        return view('livewire.audit.anomaly-dashboard', [
            'extremeDonations' => $detector->extremeDonations(),
            'failedLoginStreaks' => $detector->failedLoginStreaks(),
            'fastApprovedDisbursements' => $detector->fastApprovedDisbursements(),
            'donationContext' => $this->donationContext(),
            'loginAttempts' => $this->loginAttempts(),
            'reviews' => AnomalyReview::query()->with('reviewer')->get()
                ->keyBy(fn (AnomalyReview $r) => $r->type.':'.$r->subject_id),
        ]);
    }
}
