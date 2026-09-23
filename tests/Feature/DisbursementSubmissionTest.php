<?php

use App\Enums\CampaignStatus;
use App\Enums\DonationStatus;
use App\Enums\UserRole;
use App\Livewire\Disbursements\DisbursementForm;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Donation;
use App\Models\User;
use Livewire\Livewire;

it('lets a bendahara submit a disbursement within the available balance', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->create(['amount' => 1_000_000, 'status' => DonationStatus::Verified]);

    Livewire::actingAs($bendahara)
        ->test(DisbursementForm::class, ['campaign' => $campaign])
        ->set('amount', 500000)
        ->set('description', 'Pembelian tenda pengungsian')
        ->call('submit')
        ->assertRedirect(route('disbursements.index'));

    $disbursement = Disbursement::first();
    expect($disbursement->campaign_id)->toBe($campaign->id);
    expect($disbursement->submitted_by)->toBe($bendahara->id);
    expect(ActivityLog::where('action', 'disbursement.submitted')->where('subject_id', $disbursement->id)->exists())->toBeTrue();
});

it('rejects a disbursement that exceeds the available balance', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->create(['amount' => 100000, 'status' => DonationStatus::Verified]);

    Livewire::actingAs($bendahara)
        ->test(DisbursementForm::class, ['campaign' => $campaign])
        ->set('amount', 500000)
        ->set('description', 'Pembelian tenda')
        ->call('submit')
        ->assertHasErrors('amount');

    expect(Disbursement::count())->toBe(0);
});

it('refuses to load the form for a campaign with no available balance', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $campaign = Campaign::factory()->create();

    Livewire::actingAs($bendahara)
        ->test(DisbursementForm::class, ['campaign' => $campaign])
        ->assertForbidden();
});

it('blocks an admin from opening the disbursement creation route', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create();

    $this->actingAs($admin)->get(route('disbursements.create', $campaign))->assertForbidden();
});

it('refuses to load the form for a completed campaign even with a positive balance', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Completed]);
    Donation::factory()->for($campaign)->create(['amount' => 1_000_000, 'status' => DonationStatus::Verified]);

    Livewire::actingAs($bendahara)
        ->test(DisbursementForm::class, ['campaign' => $campaign])
        ->assertForbidden();
});
