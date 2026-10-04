<?php

use App\Livewire\Public\DonationStatusCheck;
use App\Models\Donation;
use Livewire\Livewire;

it('sends the visitor to the receipt for a known reference code', function () {
    $donation = Donation::factory()->create();

    Livewire::test(DonationStatusCheck::class)
        ->set('reference_code', $donation->reference_code)
        ->call('check')
        ->assertRedirect(route('donations.receipt', $donation->reference_code));
});

it('finds a donation regardless of the reference code casing', function () {
    $donation = Donation::factory()->create();

    Livewire::test(DonationStatusCheck::class)
        ->set('reference_code', strtolower($donation->reference_code))
        ->call('check')
        ->assertRedirect(route('donations.receipt', $donation->reference_code));
});

it('shows a not found message for an unknown reference code', function () {
    Livewire::test(DonationStatusCheck::class)
        ->set('reference_code', 'DON-TIDAKADA')
        ->call('check')
        ->assertSee('tidak ditemukan');
});
