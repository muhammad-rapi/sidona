<?php

use App\Enums\CampaignStatus;
use App\Enums\DonationStatus;
use App\Enums\UserRole;
use App\Livewire\Disbursements\DisbursementForm;
use App\Livewire\Disbursements\DisbursementIndex;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Donation;
use App\Models\User;
use App\Services\AuditLogger;
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

it('offers the submit action on the disbursement page only to bendahara with eligible programs', function () {
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Active, 'name' => 'Program Bersaldo']);
    Donation::factory()->for($campaign)->create(['amount' => 500000, 'status' => DonationStatus::Verified]);
    $empty = Campaign::factory()->create(['status' => CampaignStatus::Active, 'name' => 'Program Kosong']);

    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($bendahara)->test(DisbursementIndex::class)
        ->assertSee('Ajukan penyaluran')
        ->assertSee('Program Bersaldo')
        ->assertDontSee('Program Kosong');

    Livewire::actingAs($admin)->test(DisbursementIndex::class)
        ->assertDontSee('Ajukan penyaluran');
});

it('opens a disbursement detail with balance context and the audit trail', function () {
    $campaign = Campaign::factory()->create(['name' => 'Program Detail']);
    Donation::factory()->for($campaign)->create(['amount' => 900000, 'status' => DonationStatus::Verified]);
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $disbursement = Disbursement::factory()->for($campaign)->create([
        'amount' => 200000,
        'description' => 'Beli terpal dan selimut',
        'submitted_by' => $bendahara->id,
    ]);
    app(AuditLogger::class)->log('disbursement.submitted', $bendahara, $disbursement, [], []);

    Livewire::actingAs($bendahara)->test(DisbursementIndex::class)
        ->assertDontSee('Saldo tersedia')
        ->call('toggleDetail', $disbursement->id)
        ->assertSee('Saldo tersedia')
        ->assertSee('Beli terpal dan selimut')
        ->assertSee('disbursement.submitted')
        ->call('toggleDetail', $disbursement->id)
        ->assertDontSee('Saldo tersedia');
});
