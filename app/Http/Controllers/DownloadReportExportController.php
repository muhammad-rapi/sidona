<?php

namespace App\Http\Controllers;

use App\Models\ReportExport;
use Illuminate\Support\Facades\Storage;

class DownloadReportExportController extends Controller
{
    public function __invoke(ReportExport $reportExport)
    {
        $path = "report-exports/{$reportExport->reference}.pdf";

        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, "{$reportExport->reference}.pdf");
    }
}
