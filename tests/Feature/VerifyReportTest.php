<?php

use App\Enums\UserRole;
use App\Livewire\Reports\VerifyReport;
use App\Models\User;
use App\Services\ReportChecksum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('confirms a genuine exported report and shows who exported it', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    $checksumService = app(ReportChecksum::class);
    $export = $checksumService->export(
        '<html><body>Laporan Uji. '.$checksumService->footerHtml().'</body></html>',
        'donations',
        [],
        $auditor
    );

    $bytes = Storage::disk('local')->get("report-exports/{$export->reference}.pdf");

    Livewire::actingAs($auditor)
        ->test(VerifyReport::class)
        ->set('file', UploadedFile::fake()->createWithContent('laporan.pdf', $bytes))
        ->call('check')
        ->assertSet('result.valid', true);
});

it('reports an unrelated file as not a valid SIDONA report', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    Livewire::actingAs($auditor)
        ->test(VerifyReport::class)
        ->set('file', UploadedFile::fake()->create('random.pdf', 10))
        ->call('check')
        ->assertSet('result.valid', false);
});

it('blocks non auditors from the verify report page', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->get(route('reports.verify'))->assertForbidden();
});
