<?php

use App\Enums\UserRole;
use App\Livewire\Profile\ProfilePage;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

it('requires login to open the profile page', function () {
    $this->get(route('profile'))->assertRedirect(route('login'));
});

it('lets every staff role open and update their profile with an audit entry', function (UserRole $role) {
    $user = User::factory()->create(['role' => $role, 'name' => 'Lama', 'email' => 'lama@sidona.test']);

    Livewire::actingAs($user)->test(ProfilePage::class)
        ->set('name', 'Nama Baru')
        ->set('email', 'baru@sidona.test')
        ->call('saveProfile')
        ->assertHasNoErrors();

    expect($user->fresh()->name)->toBe('Nama Baru');
    expect($user->fresh()->email)->toBe('baru@sidona.test');
    expect(ActivityLog::where('action', 'profile.updated')->where('user_id', $user->id)->count())->toBe(1);
})->with(UserRole::cases());

it('validates the profile fields', function (string $field, string $value) {
    User::factory()->create(['email' => 'dipakai@sidona.test']);
    $user = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($user)->test(ProfilePage::class)->set($field, $value)->call('saveProfile')->assertHasErrors($field);
})->with([
    'empty name' => ['name', ''],
    'bad email' => ['email', 'bukan-email'],
    'taken email' => ['email', 'dipakai@sidona.test'],
]);

it('changes the password only with the correct current one and a strong new one', function () {
    $user = User::factory()->create(['role' => UserRole::Bendahara, 'password' => bcrypt('lamaaja123')]);

    $test = Livewire::actingAs($user)->test(ProfilePage::class);

    $test->set('current_password', 'salah')->set('password', 'baruaja456')->set('password_confirmation', 'baruaja456')
        ->call('changePassword')->assertHasErrors('current_password');

    $test->set('current_password', 'lamaaja123')->set('password', 'pendek')->set('password_confirmation', 'pendek')
        ->call('changePassword')->assertHasErrors('password');

    $test->set('current_password', 'lamaaja123')->set('password', 'baruaja456')->set('password_confirmation', 'beda12345')
        ->call('changePassword')->assertHasErrors('password');

    $test->set('current_password', 'lamaaja123')->set('password', 'lamaaja123')->set('password_confirmation', 'lamaaja123')
        ->call('changePassword')->assertHasErrors('password');

    $test->set('current_password', 'lamaaja123')->set('password', 'baruaja456')->set('password_confirmation', 'baruaja456')
        ->call('changePassword')->assertHasNoErrors();

    expect(Hash::check('baruaja456', $user->fresh()->password))->toBeTrue();
    expect(ActivityLog::where('action', 'profile.password_changed')->count())->toBe(1);
    expect(ActivityLog::all()->pluck('after')->toJson())->not->toContain('baruaja456');
});
