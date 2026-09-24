<?php

namespace Database\Factories;

use App\Enums\CampaignStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Donasi '.fake()->words(3, true),
            'description' => fake()->sentence(),
            'target_amount' => fake()->numberBetween(1_000_000, 100_000_000),
            'bank_name' => fake()->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI']),
            'account_number' => fake()->numerify('##########'),
            'account_holder' => 'Yayasan SIDONA',
            'starts_on' => now()->subDays(10)->toDateString(),
            'ends_on' => now()->addDays(30)->toDateString(),
            'status' => CampaignStatus::Active,
        ];
    }
}
