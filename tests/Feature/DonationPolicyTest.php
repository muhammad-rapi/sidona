<?php

use App\Enums\DonationStatus;
use App\Enums\UserRole;
use App\Models\Donation;
use App\Models\User;

it('lets every internal role view donations', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    expect($auditor->can('viewAny', Donation::class))->toBeTrue();
});

it('lets only bendahara verify or reject a pending donation', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $donation = Donation::factory()->create(['status' => DonationStatus::Pending]);

    expect($bendahara->can('verify', $donation))->toBeTrue();
    expect($bendahara->can('reject', $donation))->toBeTrue();
    expect($admin->can('verify', $donation))->toBeFalse();
    expect($auditor->can('verify', $donation))->toBeFalse();
});

it('prevents verifying or rejecting a donation that is no longer pending', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $donation = Donation::factory()->create(['status' => DonationStatus::Verified]);

    expect($bendahara->can('verify', $donation))->toBeFalse();
    expect($bendahara->can('reject', $donation))->toBeFalse();
});
