<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'role:admin'])
        ->get('/_test/admin-only', fn () => 'ok');
});

it('allows a user with the required role', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->get('/_test/admin-only')->assertOk();
});

it('blocks a user without the required role', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    $this->actingAs($auditor)->get('/_test/admin-only')->assertForbidden();
});
