<?php

namespace App\Livewire\Reports;

use App\Enums\DonationStatus;
use App\Livewire\Concerns\HasDonationDetail;
use App\Models\Campaign;
use App\Models\Donation;
use App\Services\ReportChecksum;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class DonationReport extends Component
{
    use HasDonationDetail;
    use WithPagination;

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
        return Donation::query()->with('campaign')
            ->when($this->campaign_id !== '', fn ($q) => $q->where('campaign_id', $this->campaign_id))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->from !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->to));
    }

    protected function verifiedTotalsPerCampaign()
    {
        return Donation::query()
            ->with('campaign')
            ->where('status', DonationStatus::Verified)
            ->get()
            ->groupBy('campaign_id')
            ->map(fn ($donations) => [
                'campaign' => $donations->first()->campaign,
                'total' => $donations->sum('amount'),
            ])
            ->values();
    }

    public function exportPdf(ReportChecksum $checksum)
    {
        $filters = [
            'campaign_id' => $this->campaign_id,
            'status' => $this->status,
            'from' => $this->from,
            'to' => $this->to,
        ];

        $html = view('pdf.donation-report', [
            'donations' => $this->filteredQuery()->latest()->get(),
            'totals' => $this->verifiedTotalsPerCampaign(),
            'checksumFooter' => $checksum->footerHtml(),
        ])->render();

        $export = $checksum->export($html, 'donations', $filters, auth()->user());

        return redirect()->route('reports.download', $export);
    }

    public function render()
    {
        return view('livewire.reports.donation-report', [
            'donations' => $this->filteredQuery()->latest()->paginate(15),
            'trail' => $this->detailTrail(),
            'campaigns' => Campaign::all(),
        ]);
    }
}
