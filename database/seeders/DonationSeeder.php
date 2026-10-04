<?php

namespace Database\Seeders;

use App\Enums\DonationStatus;
use App\Enums\PaymentMethod;
use App\Models\Campaign;
use App\Models\Donation;
use App\Services\AuditLogger;
use Illuminate\Database\Seeder;

class DonationSeeder extends Seeder
{
    public function run(AuditLogger $logger): void
    {
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

                if ($roll <= 90) {
                    $before = $donation->only(['status', 'paid_at']);
                    $paidAt = $donation->created_at->addMinutes(fake()->numberBetween(1, 5));

                    $donation->update([
                        'status' => DonationStatus::Verified,
                        'paid_at' => $paidAt,
                        'is_anonymous' => fake()->boolean(20),
                        'payment_method' => fake()->randomElement(PaymentMethod::cases()),
                    ]);

                    $logger->log('donation.paid', null, $donation, $before, $donation->only(['status', 'paid_at', 'payment_method']) + ['source' => 'simulation']);
                }
            }
        }
    }
}
