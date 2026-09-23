<?php

namespace App\Livewire\Audit;

use App\Models\ActivityLog;
use App\Services\ReportChecksum;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ActivityLogIndex extends Component
{
    #[Url]
    public string $action = '';

    #[Url]
    public string $user_id = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    public ?int $expandedId = null;

    public function toggle(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    protected function filteredQuery()
    {
        return ActivityLog::query()->with('user')->latest()
            ->when($this->action !== '', fn ($q) => $q->where('action', 'like', "%{$this->action}%"))
            ->when($this->user_id !== '', fn ($q) => $q->where('user_id', $this->user_id))
            ->when($this->from !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->to));
    }

    public function exportPdf(ReportChecksum $checksum)
    {
        $filters = [
            'action' => $this->action,
            'user_id' => $this->user_id,
            'from' => $this->from,
            'to' => $this->to,
        ];

        $html = view('pdf.activity-log-report', [
            'entries' => $this->filteredQuery()->get(),
            'checksumFooter' => $checksum->footerHtml(),
        ])->render();

        $export = $checksum->export($html, 'activity_log', $filters, auth()->user());

        return redirect()->route('reports.download', $export);
    }

    public function render()
    {
        return view('livewire.audit.activity-log-index', [
            'entries' => $this->filteredQuery()->paginate(15),
        ]);
    }
}
