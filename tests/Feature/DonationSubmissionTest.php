<?php

use App\Enums\CampaignStatus;
use App\Enums\DonationStatus;
use App\Livewire\Public\CampaignDetail;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Donation;
use Livewire\Livewire;

function activeCampaign(): Campaign
{
    return Campaign::factory()->create(['status' => CampaignStatus::Active, 'ends_on' => now()->addDays(10)]);
}

it('creates a pending donation and sends the donor to the payment page', function () {
    $campaign = activeCampaign();

    $component = Livewire::test(CampaignDetail::class, ['campaign' => $campaign])
        ->set('agree', true)
        ->set('donor_name', 'Budi Santoso')
        ->set('donor_contact', '08123456789')
        ->set('amount', 50000)
        ->set('payment_method', 'qris')
        ->call('submit')
        ->assertHasNoErrors();

    $donation = Donation::first();
    expect($donation->campaign_id)->toBe($campaign->id);
    expect($donation->status)->toBe(DonationStatus::Pending);
    expect($donation->proof_path)->toBeNull();

    $component->assertRedirect(route('donations.pay', $donation->reference_code));
});

it('rejects a donation below the minimum amount', function () {
    Livewire::test(CampaignDetail::class, ['campaign' => activeCampaign()])
        ->set('agree', true)
        ->set('donor_name', 'Budi')
        ->set('donor_contact', '08123456789')
        ->set('amount', 5000)
        ->call('submit')
        ->assertHasErrors('amount');

    expect(Donation::count())->toBe(0);
});

it('rejects an unknown payment method', function () {
    Livewire::test(CampaignDetail::class, ['campaign' => activeCampaign()])
        ->set('agree', true)
        ->set('donor_name', 'Budi')
        ->set('donor_contact', '08123456789')
        ->set('amount', 50000)
        ->set('payment_method', 'cash')
        ->call('submit')
        ->assertHasErrors('payment_method');
});

it('keeps the donor name for staff but shows an alias publicly when anonymous', function () {
    $campaign = activeCampaign();

    Livewire::test(CampaignDetail::class, ['campaign' => $campaign])
        ->set('agree', true)
        ->set('donor_name', 'Budi Rahasia')
        ->set('donor_contact', '08123456789')
        ->set('is_anonymous', true)
        ->set('amount', 50000)
        ->call('submit');

    $donation = Donation::first();
    expect($donation->donor_name)->toBe('Budi Rahasia');
    expect($donation->publicName())->toBe('Hamba Allah');
});

it('refuses to load the donation form for a completed campaign', function () {
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Completed]);

    Livewire::test(CampaignDetail::class, ['campaign' => $campaign])
        ->assertForbidden();
});

it('refuses to load the donation form for a campaign that has not started yet', function () {
    $campaign = Campaign::factory()->create([
        'status' => CampaignStatus::Active,
        'starts_on' => now()->addDays(3),
        'ends_on' => now()->addDays(30),
    ]);

    Livewire::test(CampaignDetail::class, ['campaign' => $campaign])
        ->assertForbidden();
});

it('writes an activity log entry for a submitted donation without a user', function () {
    Livewire::test(CampaignDetail::class, ['campaign' => activeCampaign()])
        ->set('agree', true)
        ->set('donor_name', 'Budi')
        ->set('donor_contact', '08123456789')
        ->set('amount', 50000)
        ->call('submit');

    $donation = Donation::first();
    expect(ActivityLog::where('action', 'donation.created')
        ->where('subject_id', $donation->id)
        ->whereNull('user_id')
        ->exists())->toBeTrue();
});

it('validates donor name, contact and amount boundaries', function (string $field, mixed $value) {
    Livewire::test(CampaignDetail::class, ['campaign' => activeCampaign()])
        ->set('agree', true)
        ->set('donor_name', 'Budi')
        ->set('donor_contact', 'budi@example.com')
        ->set('amount', 50000)
        ->set($field, $value)
        ->call('submit')
        ->assertHasErrors($field);

    expect(Donation::count())->toBe(0);
})->with([
    'name too short' => ['donor_name', 'B'],
    'name with digits' => ['donor_name', 'Budi123'],
    'name empty' => ['donor_name', ''],
    'contact is gibberish' => ['donor_contact', 'halo dunia'],
    'contact bad phone' => ['donor_contact', '12345'],
    'contact empty' => ['donor_contact', ''],
    'amount zero' => ['amount', 0],
    'amount above cap' => ['amount', 2_000_000_000],
]);

it('accepts valid email and Indonesian phone formats', function (string $contact) {
    Livewire::test(CampaignDetail::class, ['campaign' => activeCampaign()])
        ->set('agree', true)
        ->set('donor_name', "Siti Nur'aini-Putri")
        ->set('donor_contact', $contact)
        ->set('amount', 10000)
        ->call('submit')
        ->assertHasNoErrors();
})->with(['siti@example.com', '081234567890', '+6281234567890', '0812-3456-7890']);

it('refuses a donation when the campaign closed after the page loaded', function () {
    $campaign = activeCampaign();
    $component = Livewire::test(CampaignDetail::class, ['campaign' => $campaign])
        ->set('agree', true)
        ->set('donor_name', 'Budi')
        ->set('donor_contact', 'budi@example.com')
        ->set('amount', 50000);

    $campaign->update(['status' => CampaignStatus::Completed]);

    $component->call('submit')->assertForbidden();
    expect(Donation::count())->toBe(0);
});

it('requires the donor to accept the terms and privacy policy before paying', function () {
    Livewire::test(CampaignDetail::class, ['campaign' => activeCampaign()])
        ->set('donor_name', 'Budi')
        ->set('donor_contact', '08123456789')
        ->set('amount', 50000)
        ->call('submit')
        ->assertHasErrors('agree');

    expect(Donation::count())->toBe(0);
});
