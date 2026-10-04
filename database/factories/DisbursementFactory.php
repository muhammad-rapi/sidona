<?php

namespace Database\Factories;

use App\Enums\DisbursementStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisbursementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'amount' => fake()->numberBetween(50_000, 500_000),
            'description' => fake()->randomElement(['Pembelian logistik untuk warga terdampak', 'Biaya sekolah dan perlengkapan belajar', 'Perbaikan fasilitas yang rusak']),
            'status' => DisbursementStatus::Submitted,
            'submitted_by' => User::factory()->state(['role' => UserRole::Bendahara]),
        ];
    }
}
