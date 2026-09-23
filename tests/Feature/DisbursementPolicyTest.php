<?php

use App\Enums\DisbursementStatus;
use App\Enums\UserRole;
use App\Models\Disbursement;
use App\Models\User;

it('lets only bendahara create a disbursement', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    expect($bendahara->can('create', Disbursement::class))->toBeTrue();
    expect($admin->can('create', Disbursement::class))->toBeFalse();
    expect($auditor->can('create', Disbursement::class))->toBeFalse();
});

it('lets only admin approve or reject a submitted disbursement', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $disbursement = Disbursement::factory()->create(['status' => DisbursementStatus::Submitted]);

    expect($admin->can('approve', $disbursement))->toBeTrue();
    expect($admin->can('reject', $disbursement))->toBeTrue();
    expect($bendahara->can('approve', $disbursement))->toBeFalse();
});

it('prevents an admin from approving a disbursement they submitted themselves', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $disbursement = Disbursement::factory()->create([
        'status' => DisbursementStatus::Submitted,
        'submitted_by' => $admin->id,
    ]);

    expect($admin->can('approve', $disbursement))->toBeFalse();
});

it('prevents approving a disbursement that is no longer submitted', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $disbursement = Disbursement::factory()->create(['status' => DisbursementStatus::Approved]);

    expect($admin->can('approve', $disbursement))->toBeFalse();
});
