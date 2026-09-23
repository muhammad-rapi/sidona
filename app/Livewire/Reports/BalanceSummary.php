<?php

namespace App\Livewire\Reports;

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
            'campaigns' => Campaign::all(),
        ]);
    }
}
