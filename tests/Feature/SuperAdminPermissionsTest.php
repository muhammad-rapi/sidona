<?php

use App\Enums\CampaignStatus;
use App\Enums\DisbursementStatus;
use App\Enums\DonationStatus;
use App\Enums\UserRole;
use App\Livewire\Audit\AnomalyDashboard;
use App\Livewire\Campaigns\CampaignIndex;
use App\Livewire\Disbursements\DisbursementIndex;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Donation;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => $this->superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]));

it('opens every staff page', function () {
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->create(['amount' => 500000, 'status' => DonationStatus::Verified]);

    foreach ([
        'dashboard', 'profile', 'users.index', 'campaigns.index', 'campaigns.create', 'donations.index', 'disbursements.index',
        'audit.integrity', 'audit.activity', 'audit.login', 'audit.anomalies',
        'reports.donations', 'reports.disbursements', 'reports.balance', 'reports.verify',
    ] as $route) {
        $this->actingAs($this->superAdmin)->get(route($route))->assertOk();
    }

    $this->actingAs($this->superAdmin)->get(route('campaigns.edit', $campaign))->assertOk();
    $this->actingAs($this->superAdmin)->get(route('disbursements.create', $campaign))->assertOk();
});

it('can review proposals, mark anomalies and decide on disbursements submitted by others', function () {
    $proposal = Campaign::factory()->create(['status' => CampaignStatus::Pending]);
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->create(['amount' => 900000, 'status' => DonationStatus::Verified]);
    $disbursement = Disbursement::factory()->for($campaign)->create(['amount' => 100000, 'status' => DisbursementStatus::Submitted, 'submitted_by' => $bendahara->id]);

    Livewire::actingAs($this->superAdmin)->test(CampaignIndex::class)->call('approve', $proposal->id);
    expect($proposal->fresh()->status)->toBe(CampaignStatus::Active);

    Livewire::actingAs($this->superAdmin)->test(DisbursementIndex::class)->call('approve', $disbursement->id);
    expect($disbursement->fresh()->status)->toBe(DisbursementStatus::Approved);

    Livewire::actingAs($this->superAdmin)->test(AnomalyDashboard::class)->assertOk();
});

it('still cannot approve its own disbursement, and the integrity guards keep applying', function () {
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->create(['amount' => 900000, 'status' => DonationStatus::Verified]);
    $own = Disbursement::factory()->for($campaign)->create(['status' => DisbursementStatus::Submitted, 'submitted_by' => $this->superAdmin->id]);

    expect($this->superAdmin->can('approve', $own))->toBeFalse();
    expect($this->superAdmin->can('delete', $campaign))->toBeFalse();
    expect($this->superAdmin->can('delete', Campaign::factory()->create()))->toBeTrue();
});
