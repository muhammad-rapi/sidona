<?php

namespace App\Livewire\Reports;

use App\Enums\DisbursementStatus;
use App\Enums\DonationStatus;
use App\Models\Campaign;
use App\Services\ReportChecksum;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class BalanceSummary extends Component
{
    public function exportPdf(ReportChecksum $checksum)
    {
        $html = view('pdf.balance-summary', [
            'campaigns' => Campaign::all(),
            'checksumFooter' => $checksum->footerHtml(),
        ])->render();

        $export = $checksum->export($html, 'balance_summary', [], auth()->user());

        return redirect()->route('reports.download', $export);
    }

    public function render()
    {
        return view('livewire.reports.balance-summary', [
            'campaigns' => Campaign::query()
                ->withSum(['donations as raised_sum' => fn ($q) => $q->where('status', DonationStatus::Verified)], 'amount')
                ->withSum(['disbursements as disbursed_sum' => fn ($q) => $q->where('status', DisbursementStatus::Approved)], 'amount')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
