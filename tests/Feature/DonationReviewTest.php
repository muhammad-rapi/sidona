<?php

use App\Enums\DonationStatus;
use App\Enums\UserRole;
use App\Livewire\Donations\DonationIndex;
use App\Mail\DonationVerifiedMail;
use App\Models\ActivityLog;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
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

it('emails the donor when verified with an email contact', function () {
    Mail::fake();

    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $donation = Donation::factory()->create([
        'status' => DonationStatus::Pending,
        'donor_contact' => 'budi@example.com',
    ]);

    Livewire::actingAs($bendahara)
        ->test(DonationIndex::class)
        ->call('verify', $donation->id);

    Mail::assertSent(DonationVerifiedMail::class, function ($mail) use ($donation) {
        return $mail->hasTo('budi@example.com')
            && $mail->donation->id === $donation->id;
    });
});

it('does not email the donor when the contact is a phone number', function () {
    Mail::fake();

    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $donation = Donation::factory()->create([
        'status' => DonationStatus::Pending,
        'donor_contact' => '081234567890',
    ]);

    Livewire::actingAs($bendahara)
        ->test(DonationIndex::class)
        ->call('verify', $donation->id);

    Mail::assertNothingSent();
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
