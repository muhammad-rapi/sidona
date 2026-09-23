<?php

namespace Database\Seeders;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class LoginLogSeeder extends Seeder
{
    public function run(): void
    {
        foreach (User::all() as $user) {
            $count = fake()->numberBetween(2, 5);

            for ($i = 0; $i < $count; $i++) {
                LoginLog::factory()->create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'status' => 'success',
                    'created_at' => now()->subDays(fake()->numberBetween(0, 20)),
                ]);
            }
        }

        $suspiciousEmail = 'bendahara1@sidona.test';
        $base = now()->subHours(2);

        LoginLog::factory()->create([
            'email' => $suspiciousEmail,
            'status' => 'failed',
            'created_at' => $base,
        ]);
        LoginLog::factory()->create([
            'email' => $suspiciousEmail,
            'status' => 'failed',
            'created_at' => $base->copy()->addMinutes(3),
        ]);
        LoginLog::factory()->create([
            'email' => $suspiciousEmail,
            'status' => 'failed',
            'created_at' => $base->copy()->addMinutes(6),
        ]);
    }
}
