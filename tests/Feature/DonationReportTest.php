<?php

use App\Enums\DonationStatus;
use App\Enums\UserRole;
use App\Livewire\Reports\DonationReport;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\ReportExport;
use App\Models\User;
use App\Services\AuditLogger;
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

it('opens a donation detail with its audit trail and closes it again', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $donation = Donation::factory()->create(['donor_contact' => 'detail@example.com']);
    app(AuditLogger::class)->log('donation.created', null, $donation, [], []);

    Livewire::actingAs($auditor)->test(DonationReport::class)
        ->assertDontSee('detail@example.com')
        ->call('toggleDetail', $donation->id)
        ->assertSee('detail@example.com')
        ->assertSee('donation.created')
        ->call('toggleDetail', $donation->id)
        ->assertDontSee('detail@example.com');
});

it('searches the donation report by donor name, contact or reference code', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $budi = Donation::factory()->create(['donor_name' => 'Budi Santoso', 'donor_contact' => 'budi@example.com']);
    Donation::factory()->create(['donor_name' => 'Siti Aminah', 'donor_contact' => '081111111111']);

    Livewire::actingAs($auditor)->test(DonationReport::class)
        ->set('search', 'budi')->assertSee('Budi Santoso')->assertDontSee('Siti Aminah')
        ->set('search', '081111111111')->assertSee('Siti Aminah')->assertDontSee('Budi Santoso')
        ->set('search', $budi->reference_code)->assertSee('Budi Santoso')->assertDontSee('Siti Aminah');
});
