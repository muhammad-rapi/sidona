<?php

namespace App\Livewire\Audit;

use App\Models\LoginLog;
use App\Services\ReportChecksum;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LoginLogIndex extends Component
{
    #[Url]
    public string $status = '';

    #[Url]
    public string $email = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    protected function filteredQuery()
    {
        return LoginLog::query()->latest()
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->email !== '', fn ($q) => $q->where('email', 'like', "%{$this->email}%"))
            ->when($this->from !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->to));
    }

    public function exportPdf(ReportChecksum $checksum)
    {
        $filters = [
            'status' => $this->status,
            'email' => $this->email,
            'from' => $this->from,
            'to' => $this->to,
        ];

        $html = view('pdf.login-log-report', [
            'entries' => $this->filteredQuery()->get(),
            'checksumFooter' => $checksum->footerHtml(),
        ])->render();

        $export = $checksum->export($html, 'login_log', $filters, auth()->user());

        return redirect()->route('reports.download', $export);
    }

    public function render()
    {
        return view('livewire.audit.login-log-index', [
            'entries' => $this->filteredQuery()->paginate(15),
        ]);
    }
}
