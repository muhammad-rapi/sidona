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

it('summarises the device from the user agent', function () {
    $ua = fn (string $agent) => LoginLog::factory()->make(['user_agent' => $agent])->deviceLabel();

    expect($ua('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36'))->toBe('Chrome di macOS');
    expect($ua('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'))->toBe('Safari di iOS');
    expect($ua('Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Chrome/120.0 Mobile Safari/537.36'))->toBe('Chrome di Android');
    expect($ua('Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0'))->toBe('Firefox di Windows');
    expect(LoginLog::factory()->make(['user_agent' => null])->deviceLabel())->toBe('Tidak diketahui');
});

it('summarises today and flags accounts with consecutive failed logins', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    foreach ([3, 2, 1] as $minutesAgo) {
        LoginLog::factory()->create(['email' => 'korban@test.com', 'status' => 'failed', 'created_at' => now()->subMinutes($minutesAgo)]);
    }
    LoginLog::factory()->create(['email' => 'aman@test.com', 'status' => 'success', 'created_at' => now()]);

    Livewire::actingAs($auditor)->test(LoginLogIndex::class)
        ->assertSee('Hari ini 4 percobaan masuk, 3 gagal')
        ->assertSee('1 akun menunjukkan pola gagal berturut-turut')
        ->assertSee('Pola gagal berturut-turut');
});

it('shows the account name and role for successful logins', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $staff = User::factory()->create(['role' => UserRole::Bendahara, 'name' => 'Bendahara Contoh', 'email' => 'bend@test.com']);
    LoginLog::factory()->create(['email' => 'bend@test.com', 'user_id' => $staff->id, 'status' => 'success']);

    Livewire::actingAs($auditor)->test(LoginLogIndex::class)
        ->assertSee('Bendahara Contoh')->assertSee('Bendahara');
});
