<?php

use App\Enums\UserRole;
use App\Livewire\Audit\VerifyIntegrity;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\AuditLogger;
use Livewire\Livewire;

it('reports the chain as valid for an auditor when nothing has been tampered with', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    app(AuditLogger::class)->log('campaign.created', $auditor, null, [], ['name' => 'Test']);

    Livewire::actingAs($auditor)
        ->test(VerifyIntegrity::class)
        ->call('verify')
        ->assertSet('result.valid', true);
});

it('reports which row was tampered with', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    app(AuditLogger::class)->log('campaign.created', $auditor, null, [], ['name' => 'Test']);
    $entry = ActivityLog::first();
    $entry->forceFill(['action' => 'campaign.deleted'])->saveQuietly();

    Livewire::actingAs($auditor)
        ->test(VerifyIntegrity::class)
        ->call('verify')
        ->assertSet('result.valid', false)
        ->assertSet('result.tampered_at', $entry->id);
});

it('blocks non auditors from the integrity page', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->get(route('audit.integrity'))->assertForbidden();
});
