<?php

namespace App\Livewire\Audit;

use App\Models\ActivityLog;
use App\Services\AuditLogger;
use App\Support\ActivityLabels;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class VerifyIntegrity extends Component
{
    public ?array $result = null;

    public function verify(AuditLogger $logger): void
    {
        $result = $logger->verifyChain();
        $result['checked'] = ActivityLog::query()->count();
        $result['at'] = now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s');

        $culprit = $result['tampered_at'] ? ActivityLog::query()->with('user')->find($result['tampered_at']) : null;
        $result['culprit'] = $culprit ? [
            'label' => ActivityLabels::label($culprit->action),
            'at' => $culprit->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i:s'),
            'by' => $culprit->user?->name ?? 'sistem',
        ] : null;

        $this->result = $result;
    }

    public function render()
    {
        return view('livewire.audit.verify-integrity', [
            'total' => ActivityLog::query()->count(),
            'first' => ActivityLog::query()->orderBy('id')->first(['id', 'created_at']),
            'recent' => ActivityLog::query()->latest('id')->limit(5)->get(['id', 'action', 'prev_hash', 'hash', 'created_at']),
        ]);
    }
}
