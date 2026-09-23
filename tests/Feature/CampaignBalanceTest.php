<?php

use App\Enums\DisbursementStatus;
use App\Enums\DonationStatus;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Donation;

it('computes available balance as verified donations minus approved disbursements', function () {
    $campaign = Campaign::factory()->create();

    Donation::factory()->for($campaign)->create(['amount' => 1_000_000, 'status' => DonationStatus::Verified]);
    Donation::factory()->for($campaign)->create(['amount' => 500_000, 'status' => DonationStatus::Verified]);
    Donation::factory()->for($campaign)->create(['amount' => 9_000_000, 'status' => DonationStatus::Pending]);
    Donation::factory()->for($campaign)->create(['amount' => 9_000_000, 'status' => DonationStatus::Rejected]);

    Disbursement::factory()->for($campaign)->create(['amount' => 300_000, 'status' => DisbursementStatus::Approved]);
    Disbursement::factory()->for($campaign)->create(['amount' => 9_000_000, 'status' => DisbursementStatus::Submitted]);
    Disbursement::factory()->for($campaign)->create(['amount' => 9_000_000, 'status' => DisbursementStatus::Rejected]);

    expect($campaign->availableBalance())->toBe(1_200_000);
});
