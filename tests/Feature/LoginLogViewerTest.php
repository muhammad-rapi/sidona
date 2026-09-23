<?php

use App\Enums\UserRole;
use App\Livewire\Audit\LoginLogIndex;
use App\Models\LoginLog;
use App\Models\User;
use Livewire\Livewire;

it('lets an auditor filter login logs by status', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    LoginLog::factory()->create(['email' => 'a@test.com', 'status' => 'success']);
    LoginLog::factory()->create(['email' => 'b@test.com', 'status' => 'failed']);

    Livewire::actingAs($auditor)
        ->test(LoginLogIndex::class)
        ->set('status', 'failed')
        ->assertSee('b@test.com')
        ->assertDontSee('a@test.com');
});

it('blocks non auditors from the login log viewer', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->get(route('audit.login'))->assertForbidden();
});
