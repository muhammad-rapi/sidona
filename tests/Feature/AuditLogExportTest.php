<?php

use App\Enums\UserRole;
use App\Livewire\Audit\ActivityLogIndex;
use App\Livewire\Audit\LoginLogIndex;
use App\Models\Campaign;
use App\Models\LoginLog;
use App\Models\ReportExport;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\ReportChecksum;
use Livewire\Livewire;

it('exports the filtered activity log to a checksummed pdf', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $campaign = Campaign::factory()->create();
    app(AuditLogger::class)->log('campaign.created', $auditor, $campaign, [], ['name' => $campaign->name]);

    $component = Livewire::actingAs($auditor)
        ->test(ActivityLogIndex::class)
        ->set('action', 'campaign.created')
        ->call('exportPdf');

    $export = ReportExport::where('report_type', 'activity_log')->latest()->first();
    expect($export)->not->toBeNull();
    expect($export->filters['action'])->toBe('campaign.created');

    $component->assertRedirect(route('reports.download', $export));

    $response = $this->actingAs($auditor)->get(route('reports.download', $export));
    $result = app(ReportChecksum::class)->verify($response->streamedContent());
    expect($result['valid'])->toBeTrue();
});

it('exports the filtered login log to a checksummed pdf', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    LoginLog::factory()->create(['status' => 'failed']);

    $component = Livewire::actingAs($auditor)
        ->test(LoginLogIndex::class)
        ->set('status', 'failed')
        ->call('exportPdf');

    $export = ReportExport::where('report_type', 'login_log')->latest()->first();
    expect($export)->not->toBeNull();
    expect($export->filters['status'])->toBe('failed');

    $component->assertRedirect(route('reports.download', $export));

    $response = $this->actingAs($auditor)->get(route('reports.download', $export));
    $result = app(ReportChecksum::class)->verify($response->streamedContent());
    expect($result['valid'])->toBeTrue();
});
