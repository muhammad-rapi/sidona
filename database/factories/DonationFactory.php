<?php

namespace Database\Factories;

use App\Enums\DonationStatus;
use App\Enums\PaymentMethod;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'donor_name' => fake('id_ID')->name(),
            'donor_contact' => fake()->boolean(60) ? fake('id_ID')->numerify('08##########') : fake()->unique()->safeEmail(),
            'amount' => fake()->numberBetween(10_000, 5_000_000),
            'payment_method' => PaymentMethod::Qris,
            'is_anonymous' => false,
            'status' => DonationStatus::Pending,
        ];
    }
}
