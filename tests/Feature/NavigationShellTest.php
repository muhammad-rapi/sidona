<?php

use App\Enums\CampaignStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\User;

it('does not link to the staff login anywhere on the public site', function () {
    foreach (['/program', '/donasi/cek'] as $path) {
        $this->get($path)->assertOk()->assertDontSee(route('login'), false);
    }
});

it('still serves the staff login at its own address', function () {
    $this->get(route('login'))->assertOk()->assertSee('Masuk');
});

it('shows a sidebar for every staff role and a navbar for guests', function () {
    $this->get('/program')->assertSee('aria-label="Navigasi utama"', false)->assertDontSee('aria-label="Menu panel"', false);

    foreach (UserRole::cases() as $role) {
        $this->actingAs(User::factory()->create(['role' => $role]))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('aria-label="Menu panel"', false)
            ->assertDontSee('aria-label="Navigasi utama"', false);
    }
});

it('shows audit and report links only to auditors', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Bendahara]))
        ->get(route('dashboard'))
        ->assertDontSee('Log Aktivitas');

    $this->actingAs(User::factory()->create(['role' => UserRole::Auditor]))
        ->get(route('dashboard'))
        ->assertSee('Log Aktivitas')
        ->assertSee('Laporan Donasi');
});

it('lists items waiting for a decision on the dashboard, oldest first', function () {
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Pending, 'name' => 'Pengajuan Menunggu', 'proposer_name' => 'Rina']);
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->get(route('dashboard'))
        ->assertSee('Menunggu keputusan')
        ->assertSee('Pengajuan Menunggu')
        ->assertSee('Program berjalan');
});
