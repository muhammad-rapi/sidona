<?php

use App\Livewire\Auth\LoginForm;
use App\Models\User;
use Livewire\Livewire;

it('logs a user in with correct credentials and redirects to the dashboard', function () {
    $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

    Livewire::test(LoginForm::class)
        ->set('email', $user->email)
        ->set('password', 'rahasia123')
        ->call('authenticate')
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects a wrong password', function () {
    $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

    Livewire::test(LoginForm::class)
        ->set('email', $user->email)
        ->set('password', 'salah')
        ->call('authenticate')
        ->assertHasErrors('email');

    $this->assertGuest();
});
