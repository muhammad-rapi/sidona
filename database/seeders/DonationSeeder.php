<?php

namespace Database\Seeders;

use App\Enums\DonationStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Seeder;

class DonationSeeder extends Seeder
{
    public function run(AuditLogger $logger): void
    {
        $bendaharas = User::query()->where('role', UserRole::Bendahara)->get();
        $campaigns = Campaign::all();

        foreach ($campaigns as $campaign) {
            $count = fake()->numberBetween(6, 10);

            for ($i = 0; $i < $count; $i++) {
                $isExtreme = fake()->boolean(8);

                $donation = Donation::factory()->create([
                    'campaign_id' => $campaign->id,
                    'amount' => $isExtreme
                        ? fake()->numberBetween(20_000_000, 50_000_000)
                        : fake()->numberBetween(10_000, 1_000_000),
                ]);

                $logger->log('donation.created', null, $donation, [], $donation->only([
                    'campaign_id', 'donor_name', 'donor_contact', 'amount', 'status',
                ]));

                $roll = fake()->numberBetween(1, 100);

                if ($roll <= 60) {
                    $bendahara = $bendaharas->random();
                    $before = $donation->only(['status', 'verified_by', 'verified_at']);

                    $donation->update([
                        'status' => DonationStatus::Verified,
                        'verified_by' => $bendahara->id,
                        'verified_at' => $donation->created_at->addMinutes(fake()->numberBetween(5, 600)),
                    ]);

                    $logger->log('donation.verified', $bendahara, $donation, $before, $donation->only(['status', 'verified_by', 'verified_at']));
                } elseif ($roll <= 85) {
                    $bendahara = $bendaharas->random();
                    $before = $donation->only(['status', 'verified_by', 'verified_at', 'rejection_reason']);

                    $donation->update([
                        'status' => DonationStatus::Rejected,
                        'verified_by' => $bendahara->id,
                        'verified_at' => $donation->created_at->addMinutes(fake()->numberBetween(5, 600)),
                        'rejection_reason' => 'Bukti transfer tidak sesuai atau tidak terbaca.',
                    ]);

                    $logger->log('donation.rejected', $bendahara, $donation, $before, $donation->only(['status', 'verified_by', 'verified_at', 'rejection_reason']));
                }
            }
        }
    }
}
