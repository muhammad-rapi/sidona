<?php

use App\Enums\CampaignStatus;
use App\Enums\UserRole;
use App\Livewire\Campaigns\CampaignIndex;
use App\Livewire\Public\CampaignSubmit;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

function fillProposal($component)
{
    return $component
        ->set('name', 'Bantu Renovasi Musala Warga')
        ->set('description', 'Musala di kampung kami rusak parah setelah hujan deras dan butuh perbaikan atap serta lantai segera.')
        ->set('target_amount', 15000000)
        ->set('duration_days', 30)
        ->set('proposer_name', 'Siti Aminah')
        ->set('proposer_contact', '081234567890')
        ->set('bank_name', 'BRI')
        ->set('account_number', '1234567890123')
        ->set('account_holder', 'Siti Aminah');
}

beforeEach(fn () => RateLimiter::clear('campaign-submit:127.0.0.1'));

it('lets a guest submit a program proposal that stays hidden until approved', function () {
    fillProposal(Livewire::test(CampaignSubmit::class))->call('submit')->assertHasNoErrors()->assertSet('submitted', true);

    $campaign = Campaign::first();
    expect($campaign->status)->toBe(CampaignStatus::Pending);
    expect(ActivityLog::where('action', 'campaign.proposed')->whereNull('user_id')->count())->toBe(1);

    $this->get(route('program.index'))->assertDontSee('Bantu Renovasi Musala Warga');
    $this->get(route('program.show', $campaign))->assertForbidden();
});

it('validates the proposal fields', function (string $field, mixed $value) {
    fillProposal(Livewire::test(CampaignSubmit::class))->set($field, $value)->call('submit')->assertHasErrors($field);

    expect(Campaign::count())->toBe(0);
})->with([
    'short title' => ['name', 'abc'],
    'short story' => ['description', 'terlalu pendek'],
    'tiny target' => ['target_amount', 5000],
    'bad duration' => ['duration_days', 365],
    'bad contact' => ['proposer_contact', 'halo'],
    'bad name' => ['proposer_name', 'Siti123'],
    'account letters' => ['account_number', 'abcde'],
    'no bank' => ['bank_name', ''],
    'bank not in list' => ['bank_name', 'Bank Abal-Abal'],
]);

it('silently drops honeypot submissions and rate limits repeated ones', function () {
    fillProposal(Livewire::test(CampaignSubmit::class))->set('website', 'spam.example')->call('submit');
    expect(Campaign::count())->toBe(0);

    foreach (range(1, 3) as $i) {
        fillProposal(Livewire::test(CampaignSubmit::class))->call('submit');
    }
    fillProposal(Livewire::test(CampaignSubmit::class))->call('submit')->assertHasErrors('name');
    expect(Campaign::count())->toBe(3);
});

it('lets only an admin approve a proposal and starts the run from today', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $campaign = Campaign::factory()->create([
        'status' => CampaignStatus::Pending,
        'starts_on' => now()->subDays(5)->toDateString(),
        'ends_on' => now()->addDays(25)->toDateString(),
    ]);

    Livewire::actingAs($bendahara)->test(CampaignIndex::class)->call('approve', $campaign->id)->assertForbidden();

    Livewire::actingAs($admin)->test(CampaignIndex::class)->call('approve', $campaign->id);

    $campaign->refresh();
    expect($campaign->status)->toBe(CampaignStatus::Active);
    expect($campaign->starts_on->isToday())->toBeTrue();
    expect($campaign->ends_on->toDateString())->toBe(now()->addDays(30)->toDateString());
    expect($campaign->reviewed_by)->toBe($admin->id);
    expect(ActivityLog::where('action', 'campaign.approved')->count())->toBe(1);
    $this->get(route('program.show', $campaign))->assertOk();
});

it('rejects a proposal with a required reason and cannot review it twice', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Pending]);

    Livewire::actingAs($admin)->test(CampaignIndex::class)
        ->call('startReject', $campaign->id)
        ->set('rejectionReason', '')
        ->call('confirmReject')
        ->assertHasErrors('rejectionReason')
        ->set('rejectionReason', 'Data rekening tidak bisa diverifikasi')
        ->call('confirmReject');

    $campaign->refresh();
    expect($campaign->status)->toBe(CampaignStatus::Rejected);
    expect($campaign->rejection_reason)->toBe('Data rekening tidak bisa diverifikasi');

    Livewire::actingAs($admin)->test(CampaignIndex::class)->call('approve', $campaign->id)->assertForbidden();
});

it('tells admins about proposals waiting for review on the dashboard', function () {
    Campaign::factory()->create(['status' => CampaignStatus::Pending]);

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('dashboard'))->assertSee('pengajuan program baru menunggu tinjauan');
    $this->actingAs(User::factory()->create(['role' => UserRole::Auditor]))
        ->get(route('dashboard'))->assertDontSee('pengajuan program baru menunggu tinjauan');
});
