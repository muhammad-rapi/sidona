<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin SIDONA',
            'email' => 'admin@sidona.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Admin,
        ]);

        User::factory()->create([
            'name' => 'Bendahara Satu',
            'email' => 'bendahara1@sidona.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Bendahara,
        ]);

        User::factory()->create([
            'name' => 'Bendahara Dua',
            'email' => 'bendahara2@sidona.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Bendahara,
        ]);

        User::factory()->create([
            'name' => 'Auditor Satu',
            'email' => 'auditor1@sidona.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Auditor,
        ]);

        User::factory()->create([
            'name' => 'Auditor Dua',
            'email' => 'auditor2@sidona.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Auditor,
        ]);
    }
}
