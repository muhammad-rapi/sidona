<?php

use App\Enums\DisbursementStatus;
use App\Enums\DonationStatus;
use App\Enums\UserRole;
use App\Livewire\Disbursements\DisbursementIndex;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Donation;
use App\Models\User;
use Livewire\Livewire;

it('lets an admin approve a submitted disbursement and logs it', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->create(['amount' => 1_000_000, 'status' => DonationStatus::Verified]);
    $disbursement = Disbursement::factory()->for($campaign)->create(['amount' => 100_000, 'status' => DisbursementStatus::Submitted]);

    Livewire::actingAs($admin)
        ->test(DisbursementIndex::class)
        ->call('approve', $disbursement->id);

    $disbursement->refresh();
    expect($disbursement->status)->toBe(DisbursementStatus::Approved);
    expect($disbursement->reviewed_by)->toBe($admin->id);
    expect(ActivityLog::where('action', 'disbursement.approved')->where('subject_id', $disbursement->id)->exists())->toBeTrue();
});

it('lets an admin reject a submitted disbursement with a reason', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $disbursement = Disbursement::factory()->create(['status' => DisbursementStatus::Submitted]);

    Livewire::actingAs($admin)
        ->test(DisbursementIndex::class)
        ->call('startReject', $disbursement->id)
        ->set('rejectionReason', 'Keterangan penggunaan tidak jelas')
        ->call('confirmReject');

    $disbursement->refresh();
    expect($disbursement->status)->toBe(DisbursementStatus::Rejected);
    expect($disbursement->rejection_reason)->toBe('Keterangan penggunaan tidak jelas');
    expect(ActivityLog::where('action', 'disbursement.rejected')->where('subject_id', $disbursement->id)->exists())->toBeTrue();
});

it('requires a reason to reject a disbursement', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $disbursement = Disbursement::factory()->create(['status' => DisbursementStatus::Submitted]);

    Livewire::actingAs($admin)
        ->test(DisbursementIndex::class)
        ->call('startReject', $disbursement->id)
        ->set('rejectionReason', '')
        ->call('confirmReject')
        ->assertHasErrors('rejectionReason');

    expect($disbursement->fresh()->status)->toBe(DisbursementStatus::Submitted);
});

it('prevents a bendahara from approving a disbursement', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $disbursement = Disbursement::factory()->create(['status' => DisbursementStatus::Submitted]);

    Livewire::actingAs($bendahara)
        ->test(DisbursementIndex::class)
        ->call('approve', $disbursement->id)
        ->assertForbidden();

    expect($disbursement->fresh()->status)->toBe(DisbursementStatus::Submitted);
});

it('prevents an admin from approving a disbursement they submitted themselves', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $disbursement = Disbursement::factory()->create([
        'status' => DisbursementStatus::Submitted,
        'submitted_by' => $admin->id,
    ]);

    Livewire::actingAs($admin)
        ->test(DisbursementIndex::class)
        ->call('approve', $disbursement->id)
        ->assertForbidden();
});

it('prevents approving a disbursement that is no longer submitted', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $disbursement = Disbursement::factory()->create(['status' => DisbursementStatus::Approved]);

    Livewire::actingAs($admin)
        ->test(DisbursementIndex::class)
        ->call('approve', $disbursement->id)
        ->assertForbidden();
});

it('refuses to approve a disbursement whose amount no longer fits the campaign balance', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->create(['amount' => 100_000, 'status' => DonationStatus::Verified]);

    $first = Disbursement::factory()->for($campaign)->create(['amount' => 90_000, 'status' => DisbursementStatus::Submitted]);
    $second = Disbursement::factory()->for($campaign)->create(['amount' => 90_000, 'status' => DisbursementStatus::Submitted]);

    $component = Livewire::actingAs($admin)->test(DisbursementIndex::class);

    $component->call('approve', $first->id);
    expect($first->fresh()->status)->toBe(DisbursementStatus::Approved);

    $component->call('approve', $second->id);
    expect($second->fresh()->status)->toBe(DisbursementStatus::Submitted);
});
