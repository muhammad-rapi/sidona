<?php

use App\Enums\DonationStatus;
use App\Enums\UserRole;
use App\Livewire\Campaigns\CampaignForm;
use App\Livewire\Disbursements\DisbursementForm;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use Livewire\Livewire;

function validCampaignForm($component)
{
    return $component
        ->set('name', 'Donasi Banjir Demak')
        ->set('description', 'Bantuan logistik untuk korban banjir.')
        ->set('target_amount', 50000000)
        ->set('bank_name', 'BCA')
        ->set('account_number', '1234567890')
        ->set('account_holder', 'Yayasan SIDONA')
        ->set('starts_on', '2026-01-01')
        ->set('ends_on', '2026-03-01');
}

it('validates every campaign field', function (string $field, mixed $value) {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    validCampaignForm(Livewire::actingAs($admin)->test(CampaignForm::class))
        ->set($field, $value)
        ->call('save')
        ->assertHasErrors($field);

    expect(Campaign::count())->toBe(0);
})->with([
    'name empty' => ['name', ''],
    'name too short' => ['name', 'ab'],
    'description empty' => ['description', ''],
    'description too short' => ['description', 'pendek'],
    'target too low' => ['target_amount', 500],
    'target absurd' => ['target_amount', 999999999999999],
    'bank empty' => ['bank_name', ''],
    'account has letters' => ['account_number', 'abc123'],
    'account too short' => ['account_number', '12'],
    'holder empty' => ['account_holder', ''],
    'start empty' => ['starts_on', ''],
    'end before start' => ['ends_on', '2025-12-01'],
]);

it('rejects a campaign name that already exists but allows keeping its own name on edit', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $existing = Campaign::factory()->create(['name' => 'Donasi Banjir Demak']);

    validCampaignForm(Livewire::actingAs($admin)->test(CampaignForm::class))
        ->call('save')
        ->assertHasErrors('name');

    validCampaignForm(Livewire::actingAs($admin)->test(CampaignForm::class, ['campaign' => $existing]))
        ->call('save')
        ->assertHasNoErrors();
});

it('validates the disbursement amount and description', function (string $field, mixed $value) {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->create(['amount' => 500000, 'status' => DonationStatus::Verified]);

    Livewire::actingAs($bendahara)
        ->test(DisbursementForm::class, ['campaign' => $campaign])
        ->set('amount', 100000)
        ->set('description', 'Pembelian sembako untuk warga')
        ->set($field, $value)
        ->call('submit')
        ->assertHasErrors($field);
})->with([
    'amount zero' => ['amount', 0],
    'amount above balance' => ['amount', 600000],
    'description empty' => ['description', ''],
    'description too short' => ['description', 'beli'],
]);
