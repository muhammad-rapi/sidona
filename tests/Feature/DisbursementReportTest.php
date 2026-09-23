<?php

use App\Enums\DisbursementStatus;
use App\Enums\UserRole;
use App\Livewire\Reports\DisbursementReport;
use App\Models\Disbursement;
use App\Models\ReportExport;
use App\Models\User;
use App\Services\ReportChecksum;
use Livewire\Livewire;

it('lets an auditor filter the disbursement report', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    Disbursement::factory()->create(['description' => 'Beli tenda', 'status' => DisbursementStatus::Approved]);
    Disbursement::factory()->create(['description' => 'Beli obat', 'status' => DisbursementStatus::Rejected]);

    Livewire::actingAs($auditor)
        ->test(DisbursementReport::class)
        ->set('status', 'approved')
        ->assertSee('Beli tenda')
        ->assertDontSee('Beli obat');
});

it('exports a checksummed pdf and logs the export', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    Disbursement::factory()->create();

    $component = Livewire::actingAs($auditor)->test(DisbursementReport::class)->call('exportPdf');

    $export = ReportExport::where('report_type', 'disbursements')->latest()->first();
    expect($export)->not->toBeNull();
    expect($export->user_id)->toBe($auditor->id);

    $component->assertRedirect(route('reports.download', $export));

    $response = $this->actingAs($auditor)->get(route('reports.download', $export));
    $response->assertOk();

    $result = app(ReportChecksum::class)->verify($response->streamedContent());
    expect($result['valid'])->toBeTrue();
});

it('blocks non auditors from the disbursement report', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);

    $this->actingAs($bendahara)->get(route('reports.disbursements'))->assertForbidden();
});
