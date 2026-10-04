<?php

use App\Enums\UserRole;
use App\Models\Donation;
use App\Models\User;

it('lets every internal role view donations', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    expect($auditor->can('viewAny', Donation::class))->toBeTrue();
});

it('has no manual verify or reject ability for any role', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $donation = Donation::factory()->create();

    expect($bendahara->can('verify', $donation))->toBeFalse();
    expect($bendahara->can('reject', $donation))->toBeFalse();
});
