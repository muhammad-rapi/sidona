<?php

use App\Enums\DonationStatus;
use App\Enums\UserRole;
use App\Livewire\Donations\DonationIndex;
use App\Models\ActivityLog;
use App\Models\Donation;
use App\Models\User;
use Livewire\Livewire;

it('lets a bendahara verify a pending donation and logs it', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $donation = Donation::factory()->create(['status' => DonationStatus::Pending]);

    Livewire::actingAs($bendahara)
        ->test(DonationIndex::class)
        ->call('verify', $donation->id);

    $donation->refresh();
    expect($donation->status)->toBe(DonationStatus::Verified);
    expect($donation->verified_by)->toBe($bendahara->id);
    expect(ActivityLog::where('action', 'donation.verified')->where('subject_id', $donation->id)->exists())->toBeTrue();
});

it('lets a bendahara reject a pending donation with a reason', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $donation = Donation::factory()->create(['status' => DonationStatus::Pending]);

    Livewire::actingAs($bendahara)
        ->test(DonationIndex::class)
        ->call('startReject', $donation->id)
        ->set('rejectionReason', 'Bukti transfer tidak terbaca')
        ->call('confirmReject');

    $donation->refresh();
    expect($donation->status)->toBe(DonationStatus::Rejected);
    expect($donation->rejection_reason)->toBe('Bukti transfer tidak terbaca');
    expect(ActivityLog::where('action', 'donation.rejected')->where('subject_id', $donation->id)->exists())->toBeTrue();
});

it('requires a reason to reject a donation', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $donation = Donation::factory()->create(['status' => DonationStatus::Pending]);

    Livewire::actingAs($bendahara)
        ->test(DonationIndex::class)
        ->call('startReject', $donation->id)
        ->set('rejectionReason', '')
        ->call('confirmReject')
        ->assertHasErrors('rejectionReason');

    expect($donation->fresh()->status)->toBe(DonationStatus::Pending);
});

it('prevents an admin from verifying a donation', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $donation = Donation::factory()->create(['status' => DonationStatus::Pending]);

    Livewire::actingAs($admin)
        ->test(DonationIndex::class)
        ->call('verify', $donation->id)
        ->assertForbidden();

    expect($donation->fresh()->status)->toBe(DonationStatus::Pending);
});

it('prevents verifying a donation that is already verified', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $donation = Donation::factory()->create(['status' => DonationStatus::Verified]);

    Livewire::actingAs($bendahara)
        ->test(DonationIndex::class)
        ->call('verify', $donation->id)
        ->assertForbidden();
});
