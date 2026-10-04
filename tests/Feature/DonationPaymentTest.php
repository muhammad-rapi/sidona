<?php

use App\Enums\DonationStatus;
use App\Livewire\Public\DonationPay;
use App\Livewire\Public\DonationReceipt;
use App\Mail\DonationPaidMail;
use App\Models\ActivityLog;
use App\Models\Donation;
use App\Services\DonationPayment;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('marks a pending donation as paid automatically without any staff action', function () {
    $donation = Donation::factory()->create(['status' => DonationStatus::Pending]);

    Livewire::test(DonationPay::class, ['reference' => $donation->reference_code])
        ->call('simulatePayment')
        ->assertRedirect(route('donations.receipt', $donation->reference_code));

    $donation->refresh();
    expect($donation->status)->toBe(DonationStatus::Verified);
    expect($donation->paid_at)->not->toBeNull();
    expect($donation->verified_by)->toBeNull();
    expect(ActivityLog::where('action', 'donation.paid')->where('subject_id', $donation->id)->whereNull('user_id')->exists())->toBeTrue();
});

it('emails the donor a receipt when the contact is an email address', function () {
    Mail::fake();
    $donation = Donation::factory()->create(['donor_contact' => 'budi@example.com']);

    app(DonationPayment::class)->confirm($donation);

    Mail::assertSent(DonationPaidMail::class, fn ($mail) => $mail->hasTo('budi@example.com'));
});

it('does not email when the contact is a phone number', function () {
    Mail::fake();
    $donation = Donation::factory()->create(['donor_contact' => '08123456789']);

    app(DonationPayment::class)->confirm($donation);

    Mail::assertNothingSent();
});

it('confirms a donation only once', function () {
    $donation = Donation::factory()->create();

    app(DonationPayment::class)->confirm($donation);
    app(DonationPayment::class)->confirm($donation);

    expect(ActivityLog::where('action', 'donation.paid')->where('subject_id', $donation->id)->count())->toBe(1);
});

it('redirects the payment page to the receipt once the donation is paid', function () {
    $donation = Donation::factory()->create(['status' => DonationStatus::Verified]);

    Livewire::test(DonationPay::class, ['reference' => $donation->reference_code])
        ->assertRedirect(route('donations.receipt', $donation->reference_code));
});

it('shows the receipt for a paid donation and the way to pay for a pending one', function () {
    $paid = Donation::factory()->create(['status' => DonationStatus::Verified, 'paid_at' => now()]);
    $pending = Donation::factory()->create();

    Livewire::test(DonationReceipt::class, ['reference' => $paid->reference_code])
        ->assertSee($paid->reference_code)
        ->assertSee('LUNAS');

    Livewire::test(DonationReceipt::class, ['reference' => $pending->reference_code])
        ->assertSee('Menunggu pembayaran')
        ->assertSee('BELUM LUNAS');
});

it('returns 404 for an unknown reference code', function () {
    $this->get(route('donations.receipt', 'DON-TIDAKADA'))->assertNotFound();
});
