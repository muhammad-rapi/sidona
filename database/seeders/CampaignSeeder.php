<?php

namespace Database\Seeders;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    public function run(AuditLogger $logger): void
    {
        $admin = User::query()->where('email', 'admin@sidona.test')->firstOrFail();

        $names = [
            'Donasi Gempa Cianjur',
            'Donasi Pendidikan Anak Yatim',
            'Donasi Korban Banjir Demak',
            'Donasi Renovasi Panti Asuhan',
            'Donasi Beasiswa Mahasiswa',
            'Donasi Bantuan Pangan Lansia',
        ];

        foreach ($names as $index => $name) {
            $status = $index < 4 ? CampaignStatus::Active : CampaignStatus::Completed;

            $campaign = Campaign::factory()->create([
                'name' => $name,
                'status' => $status,
            ]);

            $logger->log('campaign.created', $admin, $campaign, [], $campaign->only([
                'name', 'description', 'target_amount', 'starts_on', 'ends_on', 'status',
            ]));
        }
    }
}
