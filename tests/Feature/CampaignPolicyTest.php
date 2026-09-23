<?php

use App\Enums\UserRole;
use App\Models\Campaign;
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

it('lets every role view campaigns', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    expect($auditor->can('viewAny', Campaign::class))->toBeTrue();
});
