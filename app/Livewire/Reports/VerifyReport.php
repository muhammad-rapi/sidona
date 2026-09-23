<?php

namespace App\Livewire\Reports;

use App\Models\ReportExport;
use App\Services\ReportChecksum;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class VerifyReport extends Component
{
    use WithFileUploads;

    public $file;

    public ?array $result = null;

    public function check(ReportChecksum $checksum): void
    {
        $this->validate([
            'file' => ['required', 'file'],
        ]);

        $contents = file_get_contents($this->file->getRealPath());

        $this->result = $checksum->verify($contents);

        if ($this->result['valid']) {
            $export = ReportExport::where('checksum', $this->result['checksum'])->with('user')->first();
            $this->result['export'] = $export;
        }
    }

    public function render()
    {
        return view('livewire.reports.verify-report');
    }
}
