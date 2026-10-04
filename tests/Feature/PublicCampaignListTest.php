<?php

use App\Enums\CampaignStatus;
use App\Livewire\Public\CampaignList;
use App\Models\Campaign;

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
