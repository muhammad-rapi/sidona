<?php

use App\Enums\DisbursementStatus;
use App\Enums\UserRole;
use App\Livewire\Audit\AnomalyDashboard;
use App\Models\ActivityLog;
use App\Models\AnomalyReview;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Donation;
use App\Models\LoginLog;
use App\Models\User;
use App\Services\AnomalyDetector;

it('flags a donation far above its campaign average', function () {
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->count(5)->create(['amount' => 100_000]);
    $outlier = Donation::factory()->for($campaign)->create(['amount' => 50_000_000]);

    $flagged = app(AnomalyDetector::class)->extremeDonations();

    expect($flagged->pluck('id'))->toContain($outlier->id);
});

it('flags three or more failed logins within 15 minutes for the same email', function () {
    $base = now();
    LoginLog::factory()->create(['email' => 'x@test.com', 'status' => 'failed', 'created_at' => $base]);
    LoginLog::factory()->create(['email' => 'x@test.com', 'status' => 'failed', 'created_at' => $base->copy()->addMinutes(5)]);
    LoginLog::factory()->create(['email' => 'x@test.com', 'status' => 'failed', 'created_at' => $base->copy()->addMinutes(10)]);

    $flagged = app(AnomalyDetector::class)->failedLoginStreaks();

    expect($flagged->pluck('email'))->toContain('x@test.com');
});

it('does not flag a failed login streak broken by a success or spread past 15 minutes', function () {
    $base = now();
    LoginLog::factory()->create(['email' => 'y@test.com', 'status' => 'failed', 'created_at' => $base]);
    LoginLog::factory()->create(['email' => 'y@test.com', 'status' => 'success', 'created_at' => $base->copy()->addMinutes(2)]);
    LoginLog::factory()->create(['email' => 'y@test.com', 'status' => 'failed', 'created_at' => $base->copy()->addMinutes(4)]);
    LoginLog::factory()->create(['email' => 'y@test.com', 'status' => 'failed', 'created_at' => $base->copy()->addMinutes(6)]);

    $flagged = app(AnomalyDetector::class)->failedLoginStreaks();

    expect($flagged->pluck('email'))->not->toContain('y@test.com');
});

it('flags a disbursement approved less than a minute after submission', function () {
    $fast = Disbursement::factory()->create([
        'status' => DisbursementStatus::Approved,
        'created_at' => now(),
        'reviewed_at' => now()->addSeconds(30),
    ]);
    Disbursement::factory()->create([
        'status' => DisbursementStatus::Approved,
        'created_at' => now(),
        'reviewed_at' => now()->addMinutes(10),
    ]);

    $flagged = app(AnomalyDetector::class)->fastApprovedDisbursements();

    expect($flagged->pluck('id'))->toContain($fast->id);
});

it('blocks non auditors from the anomaly dashboard', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);

    $this->actingAs($bendahara)->get(route('audit.anomalies'))->assertForbidden();
});

it('lets an auditor mark a flagged anomaly as checked and logs it once', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->count(5)->create(['amount' => 100_000]);
    $outlier = Donation::factory()->for($campaign)->create(['amount' => 50_000_000]);

    $component = Livewire\Livewire::actingAs($auditor)->test(AnomalyDashboard::class)
        ->call('markChecked', 'donation', $outlier->id)
        ->call('markChecked', 'donation', $outlier->id)
        ->assertSee('Diperiksa');

    expect(AnomalyReview::count())->toBe(1);
    expect(ActivityLog::where('action', 'anomaly.reviewed')->count())->toBe(1);
});

it('refuses to check something that is not a flagged anomaly or by a non-auditor', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $donation = Donation::factory()->create();

    Livewire\Livewire::actingAs($auditor)->test(AnomalyDashboard::class)
        ->call('markChecked', 'donation', $donation->id)
        ->assertStatus(422);

    Livewire\Livewire::actingAs($bendahara)->test(AnomalyDashboard::class)
        ->call('markChecked', 'donation', $donation->id)
        ->assertForbidden();
});

it('opens a detail panel for each anomaly type with a check action inside it', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->count(5)->create(['amount' => 100_000]);
    $outlier = Donation::factory()->for($campaign)->create(['amount' => 50_000_000, 'donor_name' => 'Donatur Besar']);

    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $fast = Disbursement::factory()->for($campaign)->create([
        'status' => DisbursementStatus::Approved,
        'submitted_by' => $bendahara->id,
        'reviewed_by' => $admin->id,
        'reviewed_at' => now(),
        'description' => 'Pembelian tenda darurat',
    ]);

    foreach (['failed', 'failed', 'failed'] as $i => $status) {
        LoginLog::factory()->create(['email' => 'x@sidona.test', 'status' => $status, 'created_at' => now()->subMinutes(3 - $i)]);
    }
    $login = app(AnomalyDetector::class)->failedLoginStreaks()->first();

    $test = Livewire::actingAs($auditor)->test(AnomalyDashboard::class)
        ->assertDontSee('Tandai diperiksa');

    $test->call('toggleDetail', 'donation', $outlier->id)
        ->assertSee('Donatur Besar')->assertSee('Rata-rata program')->assertSee('Tandai diperiksa');
    $test->call('toggleDetail', 'login', $login->id)
        ->assertSee('Alamat IP')->assertSee('Percobaan terakhir');
    $test->call('toggleDetail', 'disbursement', $fast->id)
        ->assertSee('Pembelian tenda darurat')->assertSee('Selisih waktu');
    $test->call('toggleDetail', 'disbursement', $fast->id)->assertDontSee('Selisih waktu');
});
