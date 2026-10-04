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

            if ($status === CampaignStatus::Active) {
                $this->attachSamplePhotos($campaign, $index);
            }

            $logger->log('campaign.created', $admin, $campaign, [], $campaign->only([
                'name', 'description', 'target_amount', 'starts_on', 'ends_on', 'status',
            ]));
        }
    }

    /**
     * Foto contoh buatan GD untuk keperluan testing tampilan galeri.
     */
    private function attachSamplePhotos(Campaign $campaign, int $seed): void
    {
        $skies = [[255, 198, 26], [120, 190, 235], [240, 120, 90], [150, 210, 160]];
        $campaign->update([
            'cover_image' => $this->makeSampleImage("campaign-covers/sample-{$campaign->id}.jpg", $skies[$seed % 4], $seed),
        ]);

        foreach (range(1, 4) as $n) {
            $campaign->photos()->create([
                'path' => $this->makeSampleImage("campaign-gallery/sample-{$campaign->id}-{$n}.jpg", $skies[($seed + $n) % 4], $seed * 5 + $n),
                'caption' => "Foto contoh {$n} (untuk testing)",
                'position' => $n,
            ]);
        }
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $sky
     */
    private function makeSampleImage(string $path, array $sky, int $seed): string
    {
        mt_srand($seed + 7);
        $w = 1200;
        $h = 800;
        $img = imagecreatetruecolor($w, $h);
        $c = fn (int $r, int $g, int $b) => imagecolorallocate($img, max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));

        imagefill($img, 0, 0, $c(...$sky));
        imagefilledellipse($img, mt_rand(200, 1000), mt_rand(120, 260), 180, 180, $c(255, 245, 200));
        imagefilledrectangle($img, 0, 560, $w, $h, $c(23, 19, 10));

        for ($x = -60; $x < $w; $x += mt_rand(150, 230)) {
            $bw = mt_rand(110, 190);
            $bh = mt_rand(120, 300);
            imagefilledrectangle($img, $x, 560 - $bh, $x + $bw, 560, $c(mt_rand(40, 90), mt_rand(30, 70), mt_rand(20, 60)));
            imagefilledrectangle($img, $x + 14, 560 - $bh + 18, $x + 44, 560 - $bh + 48, $c(255, 198, 26));
        }

        imagefilledrectangle($img, 0, 640, $w, 664, $c(212, 42, 31));

        ob_start();
        imagejpeg($img, null, 82);
        Storage::disk('public')->put($path, ob_get_clean());

        return $path;
    }
}
