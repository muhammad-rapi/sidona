<?php

use App\Enums\DonationStatus;
use App\Enums\UserRole;
use App\Livewire\Reports\DonationReport;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\ReportExport;
use App\Models\User;
use App\Services\ReportChecksum;
use Livewire\Livewire;

it('lets an auditor filter the donation report', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->create(['donor_name' => 'Budi', 'status' => DonationStatus::Verified]);
    Donation::factory()->for($campaign)->create(['donor_name' => 'Siti', 'status' => DonationStatus::Rejected]);

    Livewire::actingAs($auditor)
        ->test(DonationReport::class)
        ->set('status', 'verified')
        ->assertSee('Budi')
        ->assertDontSee('Siti');
});

it('exports a checksummed pdf, logs the export, and the file verifies on download', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    Donation::factory()->create();

    $component = Livewire::actingAs($auditor)->test(DonationReport::class)->call('exportPdf');

    $export = ReportExport::where('report_type', 'donations')->latest()->first();
    expect($export)->not->toBeNull();
    expect($export->user_id)->toBe($auditor->id);

    $component->assertRedirect(route('reports.download', $export));

    $response = $this->actingAs($auditor)->get(route('reports.download', $export));
    $response->assertOk();

    $result = app(ReportChecksum::class)->verify($response->streamedContent());
    expect($result['valid'])->toBeTrue();
    expect($result['checksum'])->toBe($export->checksum);
});

it('blocks non auditors from the donation report', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);

    $this->actingAs($bendahara)->get(route('reports.donations'))->assertForbidden();
});
