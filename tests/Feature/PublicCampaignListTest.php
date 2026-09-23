<?php

use App\Enums\CampaignStatus;
use App\Models\Campaign;

it('lets a guest view only active campaigns', function () {
    Campaign::factory()->create(['name' => 'Donasi Aktif', 'status' => CampaignStatus::Active]);
    Campaign::factory()->create(['name' => 'Donasi Selesai', 'status' => CampaignStatus::Completed]);

    $response = $this->get('/program');

    $response->assertOk();
    $response->assertSee('Donasi Aktif');
    $response->assertDontSee('Donasi Selesai');
});
