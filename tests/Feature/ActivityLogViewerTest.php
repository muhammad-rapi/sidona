<?php

use App\Enums\UserRole;
use App\Livewire\Audit\ActivityLogIndex;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\User;
use App\Services\AuditLogger;
use Livewire\Livewire;

it('lets an auditor filter the activity log by action', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $campaign = Campaign::factory()->create();
    app(AuditLogger::class)->log('campaign.created', $auditor, $campaign, [], ['name' => $campaign->name]);
    app(AuditLogger::class)->log('campaign.deleted', $auditor, $campaign, ['name' => $campaign->name], []);

    Livewire::actingAs($auditor)
        ->test(ActivityLogIndex::class)
        ->set('action', 'campaign.deleted')
        ->assertSee('Program dihapus')
        ->assertDontSee('Program dibuat');
});

it('shows human readable before/after values instead of raw json when a row is expanded', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $campaign = Campaign::factory()->create();
    app(AuditLogger::class)->log('campaign.updated', $auditor, $campaign, ['name' => 'Lama'], ['name' => 'Baru']);
    $entry = ActivityLog::first();

    Livewire::actingAs($auditor)
        ->test(ActivityLogIndex::class)
        ->call('toggle', $entry->id)
        ->assertSee('Lama')
        ->assertSee('Baru')
        ->assertDontSee('{"name":"Lama"}', false);
});

it('blocks non auditors from the activity log viewer', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);

    $this->actingAs($bendahara)->get(route('audit.activity'))->assertForbidden();
});

it('shows readable event names, the subject and the hash chain link', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $campaign = Campaign::factory()->create(['name' => 'Program Contoh']);
    $log = app(AuditLogger::class)->log('campaign.updated', $auditor, $campaign, [], []);

    Livewire::actingAs($auditor)->test(ActivityLogIndex::class)
        ->assertSee('Program diubah')
        ->assertSee('Program: Program Contoh')
        ->assertSee(substr($log->hash, 0, 10))
        ->assertSee(substr($log->prev_hash, 0, 6));
});
