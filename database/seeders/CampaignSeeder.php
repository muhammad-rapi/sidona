<?php

namespace Database\Seeders;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CampaignSeeder extends Seeder
{
    public function run(AuditLogger $logger): void
    {
        $admin = User::query()->where('email', 'admin@sidona.test')->firstOrFail();

        $programs = [
            ['Donasi Gempa Cianjur', 'gempa-cianjur', 'Ribuan rumah di Cianjur rusak akibat gempa. Dana terkumpul dipakai untuk tenda hunian sementara, makanan siap saji, selimut, dan obat-obatan bagi keluarga yang masih mengungsi.'],
            ['Donasi Pendidikan Anak Yatim', 'pendidikan-anak-yatim', 'Biaya sekolah, seragam, dan perlengkapan belajar untuk anak yatim agar mereka tetap bisa bersekolah sampai lulus.'],
            ['Donasi Korban Banjir Demak', 'banjir-demak', 'Banjir merendam permukiman warga Demak berhari-hari. Bantuan berupa sembako, air bersih, dan perlengkapan bayi untuk warga yang terdampak.'],
            ['Donasi Renovasi Panti Asuhan', 'renovasi-panti', 'Atap bocor dan kamar yang sempit membuat penghuni panti tidak nyaman. Dana dipakai memperbaiki atap, lantai, dan kamar mandi.'],
            ['Donasi Beasiswa Mahasiswa', null, 'Beasiswa semester untuk mahasiswa berprestasi dari keluarga kurang mampu. Program ini sudah selesai.'],
            ['Donasi Bantuan Pangan Lansia', null, 'Paket pangan bulanan untuk lansia yang hidup sendiri. Program ini sudah selesai.'],
        ];

        foreach ($programs as $index => [$name, $slug, $description]) {
            $status = $index < 4 ? CampaignStatus::Active : CampaignStatus::Completed;

            $campaign = Campaign::factory()->create([
                'name' => $name,
                'description' => $description,
                'status' => $status,
            ]);

            if ($slug !== null) {
                $this->attachSamplePhotos($campaign, $slug);
            }

            $logger->log('campaign.created', $admin, $campaign, [], $campaign->only([
                'name', 'description', 'target_amount', 'starts_on', 'ends_on', 'status',
            ]));
        }
    }

    /**
     * Foto asli berlisensi bebas dari Wikimedia Commons (lihat sample-photos/credits.json).
     */
    private function attachSamplePhotos(Campaign $campaign, string $slug): void
    {
        $dir = database_path('seeders/sample-photos');
        $credits = json_decode(file_get_contents($dir.'/credits.json'), true)[$slug] ?? [];

        foreach ($credits as $index => $credit) {
            $path = ($index === 0 ? 'campaign-covers' : 'campaign-gallery')."/{$slug}-{$credit['file']}";
            Storage::disk('public')->put($path, file_get_contents("{$dir}/{$slug}/{$credit['file']}"));

            if ($index === 0) {
                $campaign->update(['cover_image' => $path]);

                continue;
            }

            $caption = trim(($credit['artist'] !== '' ? $credit['artist'].', ' : '').$credit['license'].', Wikimedia Commons');
            $campaign->photos()->create(['path' => $path, 'caption' => $caption, 'position' => $index]);
        }
    }
}
