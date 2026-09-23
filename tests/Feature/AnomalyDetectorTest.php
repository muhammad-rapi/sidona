<?php

use App\Enums\DisbursementStatus;
use App\Enums\UserRole;
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
