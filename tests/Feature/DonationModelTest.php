<?php

use App\Enums\DonationStatus;
use App\Models\Campaign;
use App\Models\Donation;

it('auto generates a unique reference code on create', function () {
    $donation = Donation::factory()->create();

    expect($donation->reference_code)->not->toBeEmpty();
    expect(Donation::where('reference_code', $donation->reference_code)->count())->toBe(1);
});

it('casts status to the DonationStatus enum and defaults to pending', function () {
    $donation = Donation::factory()->create();

    expect($donation->fresh()->status)->toBe(DonationStatus::Pending);
});

it('belongs to a campaign', function () {
    $campaign = Campaign::factory()->create();
    $donation = Donation::factory()->for($campaign)->create();

    expect($donation->campaign->id)->toBe($campaign->id);
});
