<?php

use App\Enums\DisbursementStatus;
use App\Enums\DonationStatus;
use App\Enums\UserRole;
use App\Livewire\Reports\BalanceSummary;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Donation;
use App\Models\ReportExport;
use App\Models\User;
use App\Services\ReportChecksum;
use Livewire\Livewire;

it('shows each campaign balance figures for an auditor', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $campaign = Campaign::factory()->create(['name' => 'Donasi Uji']);
    Donation::factory()->for($campaign)->create(['amount' => 1_000_000, 'status' => DonationStatus::Verified]);
    Disbursement::factory()->for($campaign)->create(['amount' => 200_000, 'status' => DisbursementStatus::Approved]);

    Livewire::actingAs($auditor)
        ->test(BalanceSummary::class)
        ->assertSee('Donasi Uji')
        ->assertSee('800.000');
});

it('exports a checksummed pdf and logs the export', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    Campaign::factory()->create();

    $component = Livewire::actingAs($auditor)->test(BalanceSummary::class)->call('exportPdf');

    $export = ReportExport::where('report_type', 'balance_summary')->latest()->first();
    expect($export)->not->toBeNull();

    $component->assertRedirect(route('reports.download', $export));

    $response = $this->actingAs($auditor)->get(route('reports.download', $export));
    $result = app(ReportChecksum::class)->verify($response->streamedContent());
    expect($result['valid'])->toBeTrue();
});

it('blocks non auditors from the balance summary report', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);

    $this->actingAs($bendahara)->get(route('reports.balance'))->assertForbidden();
});
