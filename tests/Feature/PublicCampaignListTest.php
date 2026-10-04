<?php

use App\Enums\CampaignStatus;
use App\Livewire\Public\CampaignList;
use App\Models\Campaign;
use App\Models\Donation;
use App\Services\AuditLogger;
use App\Support\Rupiah;

it('lets a guest view only active campaigns', function () {
    Campaign::factory()->create(['name' => 'Donasi Aktif', 'status' => CampaignStatus::Active]);
    Campaign::factory()->create(['name' => 'Donasi Selesai', 'status' => CampaignStatus::Completed]);

    $response = $this->get('/program');

    $response->assertOk();
    $response->assertSee('Donasi Aktif');
    $response->assertDontSee('Donasi Selesai');
});

it('searches active programs by name or description', function () {
    Campaign::factory()->create(['name' => 'Donasi Gempa Cianjur', 'description' => 'Tenda untuk pengungsi', 'status' => CampaignStatus::Active]);
    Campaign::factory()->create(['name' => 'Donasi Beasiswa', 'description' => 'Biaya kuliah mahasiswa', 'status' => CampaignStatus::Active]);

    Livewire\Livewire::test(CampaignList::class)
        ->set('search', 'gempa')
        ->assertSee('Donasi Gempa Cianjur')
        ->assertDontSee('Donasi Beasiswa')
        ->set('search', 'kuliah')
        ->assertSee('Donasi Beasiswa')
        ->assertDontSee('Donasi Gempa Cianjur')
        ->set('search', 'tidak-ada-program-ini')
        ->assertSee('Tidak ada program yang cocok');
});

it('shows only non-personal audit ledger entries on the homepage', function () {
    $donation = Donation::factory()->create(['donor_name' => 'Rahasia Pribadi', 'donor_contact' => 'rahasia@example.com']);
    $logger = app(AuditLogger::class);
    $logger->log('donation.paid', null, $donation, [], ['donor_name' => 'Rahasia Pribadi']);
    $logger->log('user.created', null, $donation, [], []);

    $this->get(route('program.index'))
        ->assertSee('Setiap rupiah meninggalkan jejak')
        ->assertSee('Donasi masuk')
        ->assertDontSee('Rahasia Pribadi')
        ->assertDontSee('rahasia@example.com')
        ->assertDontSee('user.created');
});

it('formats large amounts compactly', function () {
    expect(Rupiah::short(2898869))->toBe('2,9 jt');
    expect(Rupiah::short(2000000))->toBe('2 jt');
    expect(Rupiah::short(1250000000))->toBe('1,3 M');
    expect(Rupiah::short(45000))->toBe('45 rb');
    expect(Rupiah::short(900))->toBe('900');
});
