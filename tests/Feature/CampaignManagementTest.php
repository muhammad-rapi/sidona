<?php

use App\Enums\UserRole;
use App\Livewire\Campaigns\CampaignForm;
use App\Livewire\Campaigns\CampaignIndex;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('blocks an auditor from opening the campaign creation route', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    $this->actingAs($auditor)->get(route('campaigns.create'))->assertForbidden();
});

it('lets a bendahara open the campaign creation route', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);

    $this->actingAs($bendahara)->get(route('campaigns.create'))->assertOk();
});

it('creates a campaign and writes an activity log entry', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(CampaignForm::class)
        ->set('name', 'Donasi Gempa Cianjur')
        ->set('description', 'Bantuan korban gempa')
        ->set('target_amount', 50000000)
        ->set('bank_name', 'BCA')
        ->set('account_number', '1234567890')
        ->set('account_holder', 'Yayasan SIDONA')
        ->set('starts_on', '2026-01-01')
        ->set('ends_on', '2026-03-01')
        ->call('save')
        ->assertRedirect(route('campaigns.index'));

    $campaign = Campaign::first();

    expect($campaign->name)->toBe('Donasi Gempa Cianjur');
    expect(ActivityLog::where('action', 'campaign.created')
        ->where('subject_id', $campaign->id)
        ->exists())->toBeTrue();
});

it('logs matching before/after date formats when editing a campaign without changing its dates', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create([
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-03-01',
    ]);

    Livewire::actingAs($admin)
        ->test(CampaignForm::class, ['campaign' => $campaign])
        ->set('name', 'Nama Baru')
        ->call('save');

    $log = ActivityLog::where('action', 'campaign.updated')->where('subject_id', $campaign->id)->first();

    expect($log->before['starts_on'])->toBe($log->after['starts_on']);
    expect($log->before['ends_on'])->toBe($log->after['ends_on']);
});

it('rejects a campaign whose end date is before its start date', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(CampaignForm::class)
        ->set('name', 'Donasi Tidak Valid')
        ->set('target_amount', 1000000)
        ->set('starts_on', '2026-03-01')
        ->set('ends_on', '2026-01-01')
        ->call('save')
        ->assertHasErrors('ends_on');

    expect(Campaign::count())->toBe(0);
});

it('prevents an auditor from deleting a campaign', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $campaign = Campaign::factory()->create();

    Livewire::actingAs($auditor)
        ->test(CampaignIndex::class)
        ->call('delete', $campaign)
        ->assertForbidden();

    expect(Campaign::find($campaign->id))->not->toBeNull();
});

it('lets an admin delete a campaign and logs it', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create();

    Livewire::actingAs($admin)
        ->test(CampaignIndex::class)
        ->call('delete', $campaign);

    expect(Campaign::find($campaign->id))->toBeNull();
    expect(ActivityLog::where('action', 'campaign.deleted')
        ->where('subject_id', $campaign->id)
        ->exists())->toBeTrue();
});

it('uploads gallery photos, shows them publicly and lets staff remove one', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create();

    Livewire::actingAs($admin)
        ->test(CampaignForm::class, ['campaign' => $campaign])
        ->set('gallery_uploads', [
            UploadedFile::fake()->image('a.jpg'),
            UploadedFile::fake()->image('b.png'),
        ])
        ->call('save')
        ->assertHasNoErrors();

    expect($campaign->photos()->count())->toBe(2);
    expect(ActivityLog::where('action', 'campaign.photos_added')->count())->toBe(1);

    $this->get(route('program.show', $campaign))->assertOk()->assertSee('Galeri');

    $photo = $campaign->photos()->first();
    Livewire::actingAs($admin)
        ->test(CampaignForm::class, ['campaign' => $campaign])
        ->call('removePhoto', $photo->id);

    expect($campaign->photos()->count())->toBe(1);
    Storage::disk('public')->assertMissing($photo->path);
});

it('rejects non-image gallery files and more than twelve photos', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create();

    Livewire::actingAs($admin)->test(CampaignForm::class, ['campaign' => $campaign])
        ->set('gallery_uploads', [UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
        ->call('save')
        ->assertHasErrors('gallery_uploads.0');

    Livewire::actingAs($admin)->test(CampaignForm::class, ['campaign' => $campaign])
        ->set('gallery_uploads', array_map(fn ($i) => UploadedFile::fake()->image("p{$i}.jpg"), range(1, 13)))
        ->call('save')
        ->assertHasErrors('gallery_uploads');
});
