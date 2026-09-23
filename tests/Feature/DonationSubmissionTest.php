<?php

use App\Enums\CampaignStatus;
use App\Enums\DonationStatus;
use App\Livewire\Public\CampaignDetail;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('lets a guest submit a donation with valid data and a proof file', function () {
    Storage::fake('public');
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Active, 'ends_on' => now()->addDays(10)]);

    Livewire::test(CampaignDetail::class, ['campaign' => $campaign])
        ->set('donor_name', 'Budi Santoso')
        ->set('donor_contact', '08123456789')
        ->set('amount', 50000)
        ->set('proof', UploadedFile::fake()->create('bukti.jpg', 500, 'image/jpeg'))
        ->call('submit')
        ->assertHasNoErrors();

    $donation = Donation::first();
    expect($donation->campaign_id)->toBe($campaign->id);
    expect($donation->status)->toBe(DonationStatus::Pending);
    Storage::disk('public')->assertExists($donation->proof_path);
});

it('rejects a donation below the minimum amount', function () {
    Storage::fake('public');
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Active, 'ends_on' => now()->addDays(10)]);

    Livewire::test(CampaignDetail::class, ['campaign' => $campaign])
        ->set('donor_name', 'Budi')
        ->set('donor_contact', '08123456789')
        ->set('amount', 5000)
        ->set('proof', UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg'))
        ->call('submit')
        ->assertHasErrors('amount');

    expect(Donation::count())->toBe(0);
});

it('rejects a proof file that is not jpg, png or pdf', function () {
    Storage::fake('public');
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Active, 'ends_on' => now()->addDays(10)]);

    Livewire::test(CampaignDetail::class, ['campaign' => $campaign])
        ->set('donor_name', 'Budi')
        ->set('donor_contact', '08123456789')
        ->set('amount', 50000)
        ->set('proof', UploadedFile::fake()->create('bukti.txt', 100))
        ->call('submit')
        ->assertHasErrors('proof');
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
    Storage::fake('public');
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Active, 'ends_on' => now()->addDays(10)]);

    Livewire::test(CampaignDetail::class, ['campaign' => $campaign])
        ->set('donor_name', 'Budi')
        ->set('donor_contact', '08123456789')
        ->set('amount', 50000)
        ->set('proof', UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg'))
        ->call('submit');

    $donation = Donation::first();
    expect(ActivityLog::where('action', 'donation.created')
        ->where('subject_id', $donation->id)
        ->whereNull('user_id')
        ->exists())->toBeTrue();
});
