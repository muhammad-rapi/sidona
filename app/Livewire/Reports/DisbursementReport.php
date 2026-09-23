<?php

namespace App\Livewire\Reports;

use App\Models\Campaign;
use App\Models\Disbursement;
use App\Services\ReportChecksum;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class DisbursementReport extends Component
{
    #[Url]
    public string $campaign_id = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    protected function filteredQuery()
    {
        return Disbursement::query()->with(['campaign', 'submitter'])
            ->when($this->campaign_id !== '', fn ($q) => $q->where('campaign_id', $this->campaign_id))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->from !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->to));
    }

    public function exportPdf(ReportChecksum $checksum)
    {
        $filters = [
            'campaign_id' => $this->campaign_id,
            'status' => $this->status,
            'from' => $this->from,
            'to' => $this->to,
        ];

        $html = view('pdf.disbursement-report', [
            'disbursements' => $this->filteredQuery()->latest()->get(),
            'checksumFooter' => $checksum->footerHtml(),
        ])->render();

        $export = $checksum->export($html, 'disbursements', $filters, auth()->user());

        return redirect()->route('reports.download', $export);
    }

    public function render()
    {
        return view('livewire.reports.disbursement-report', [
            'disbursements' => $this->filteredQuery()->latest()->paginate(15),
            'campaigns' => Campaign::all(),
        ]);
    }
}
