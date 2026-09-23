<?php

use App\Enums\UserRole;
use App\Models\User;

it('casts the role attribute to the UserRole enum', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);

    expect($user->fresh()->role)->toBe(UserRole::Admin);
});

it('exposes role helper methods', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    expect($admin->isAdmin())->toBeTrue();
    expect($bendahara->isBendahara())->toBeTrue();
    expect($auditor->isAuditor())->toBeTrue();
    expect($admin->isBendahara())->toBeFalse();
});

it('defaults new users to the auditor role', function () {
    $user = User::factory()->create();

    expect($user->fresh()->role)->toBe(UserRole::Auditor);
});
