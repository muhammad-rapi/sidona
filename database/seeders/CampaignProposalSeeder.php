<?php

namespace Database\Seeders;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Services\AuditLogger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CampaignProposalSeeder extends Seeder
{
    public function run(AuditLogger $logger): void
    {
        $proposals = [
            [
                'name' => 'Bantu Renovasi Musala Kampung Cibadak',
                'description' => 'Atap musala kami bocor dan lantainya retak setelah musim hujan. Dana dipakai untuk mengganti atap, memperbaiki lantai, dan menambah tempat wudu bagi sekitar 80 jamaah.',
                'target_amount' => 25_000_000,
                'proposer_name' => 'Siti Aminah',
                'proposer_contact' => '081234567890',
                'bank_name' => 'BRI',
                'account_number' => '0123456789012',
                'account_holder' => 'Siti Aminah',
                'status' => CampaignStatus::Pending,
            ],
            [
                'name' => 'Obat dan Perawatan untuk Pak Darman',
                'description' => 'Pak Darman, 61 tahun, menjalani cuci darah dua kali seminggu. Keluarganya butuh bantuan biaya transportasi dan obat bulanan selama enam bulan ke depan.',
                'target_amount' => 18_000_000,
                'proposer_name' => 'Rizki Pratama',
                'proposer_contact' => 'rizki.pratama@example.com',
                'bank_name' => 'Mandiri',
                'account_number' => '1230009876543',
                'account_holder' => 'Rizki Pratama',
                'status' => CampaignStatus::Pending,
            ],
            [
                'name' => 'Beli Motor Baru untuk Kurir Lepas',
                'description' => 'Pengajuan contoh yang ditolak: tujuan penggalangan tidak sesuai kebijakan program sosial SIDONA.',
                'target_amount' => 20_000_000,
                'proposer_name' => 'Anonim Contoh',
                'proposer_contact' => '085100000000',
                'bank_name' => 'BCA',
                'account_number' => '5550001112223',
                'account_holder' => 'Anonim Contoh',
                'status' => CampaignStatus::Rejected,
                'rejection_reason' => 'Tujuan penggalangan tidak termasuk program sosial.',
            ],
        ];

        foreach ($proposals as $data) {
            $campaign = Campaign::create($data + [
                'proposal_code' => 'PRG-'.strtoupper(Str::random(8)),
                'starts_on' => today(),
                'ends_on' => today()->addDays(30),
            ]);

            $logger->log('campaign.proposed', null, $campaign, [], $campaign->only([
                'name', 'target_amount', 'proposer_name', 'proposer_contact', 'status',
            ]));
        }
    }
}
