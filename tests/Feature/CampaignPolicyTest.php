<?php

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Donation;
use App\Models\User;

it('lets bendahara and admin create campaigns but not auditor', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    expect($bendahara->can('create', Campaign::class))->toBeTrue();
    expect($admin->can('create', Campaign::class))->toBeTrue();
    expect($auditor->can('create', Campaign::class))->toBeFalse();
});

it('only lets admin delete campaigns', function () {
    $campaign = Campaign::factory()->create();
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    expect($bendahara->can('delete', $campaign))->toBeFalse();
    expect($admin->can('delete', $campaign))->toBeTrue();
});

it('prevents deleting a campaign that has donations', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->create();

    expect($admin->can('delete', $campaign))->toBeFalse();
});

it('prevents deleting a campaign that has disbursements', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create();
    Disbursement::factory()->for($campaign)->create();

    expect($admin->can('delete', $campaign))->toBeFalse();
});

it('lets every role view campaigns', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    expect($auditor->can('viewAny', Campaign::class))->toBeTrue();
});
