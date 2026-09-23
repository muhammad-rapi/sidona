<?php

namespace App\Services;

use App\Enums\DisbursementStatus;
use App\Models\Disbursement;
use App\Models\Donation;
use App\Models\LoginLog;
use Illuminate\Support\Collection;

class AnomalyDetector
{
    public function extremeDonations(): Collection
    {
        return Donation::query()->with('campaign')->get()
            ->groupBy('campaign_id')
            ->flatMap(function (Collection $donations) {
                if ($donations->count() < 2) {
                    return collect();
                }

                $mean = $donations->avg('amount');
                $variance = $donations->reduce(
                    fn (float $carry, Donation $donation) => $carry + ($donation->amount - $mean) ** 2,
                    0.0
                ) / $donations->count();
                $stdDev = sqrt($variance);
                $threshold = $mean + (2 * $stdDev);

                return $donations->filter(fn (Donation $donation) => $donation->amount > $threshold);
            })
            ->values();
    }

    public function failedLoginStreaks(): Collection
    {
        return LoginLog::query()
            ->orderBy('email')
            ->orderBy('created_at')
            ->get()
            ->groupBy('email')
            ->flatMap(function (Collection $logs, string $email) {
                $streak = collect();
                $flagged = collect();

                foreach ($logs as $log) {
                    if ($log->status !== 'failed') {
                        $streak = collect();

                        continue;
                    }

                    $streak->push($log);

                    if ($streak->count() >= 3 && $streak->last()->created_at->diffInMinutes($streak->first()->created_at) <= 15) {
                        $flagged->push($streak->last());
                    }
                }

                return $flagged->take(1);
            })
            ->values();
    }

    public function fastApprovedDisbursements(): Collection
    {
        return Disbursement::query()
            ->with('campaign')
            ->where('status', DisbursementStatus::Approved)
            ->whereNotNull('reviewed_at')
            ->get()
            ->filter(fn (Disbursement $disbursement) => $disbursement->created_at->diffInSeconds($disbursement->reviewed_at) < 60)
            ->values();
    }
}
