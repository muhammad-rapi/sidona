<?php

use App\Livewire\Public\DonationStatusCheck;
use App\Models\Donation;
use Livewire\Livewire;

it('shows the donation status for a known reference code', function () {
    $donation = Donation::factory()->create();

    Livewire::test(DonationStatusCheck::class)
        ->set('reference_code', $donation->reference_code)
        ->call('check')
        ->assertSee($donation->reference_code)
        ->assertSee($donation->status->label());
});

it('finds a donation regardless of the reference code casing', function () {
    $donation = Donation::factory()->create();

    Livewire::test(DonationStatusCheck::class)
        ->set('reference_code', strtolower($donation->reference_code))
        ->call('check')
        ->assertSee($donation->reference_code)
        ->assertSee($donation->status->label());
});

it('shows a not found message for an unknown reference code', function () {
    Livewire::test(DonationStatusCheck::class)
        ->set('reference_code', 'DON-TIDAKADA')
        ->call('check')
        ->assertSee('tidak ditemukan');
});
