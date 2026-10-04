<?php

use App\Enums\UserRole;
use App\Livewire\Auth\LoginForm;
use App\Livewire\Users\UserIndex;
use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Livewire;

function superAdmin(): User
{
    return User::factory()->create(['role' => UserRole::SuperAdmin]);
}

it('lets only a super admin open the user management page', function () {
    $this->actingAs(superAdmin())->get(route('users.index'))->assertOk()->assertSee('Kelola pengguna');

    foreach ([UserRole::Admin, UserRole::Bendahara, UserRole::Auditor] as $role) {
        $this->actingAs(User::factory()->create(['role' => $role]))->get(route('users.index'))->assertForbidden();
    }

    auth()->logout();
    $this->get(route('users.index'))->assertRedirect(route('login'));
});

it('shows the users menu only to super admins', function () {
    $this->actingAs(superAdmin())->get(route('dashboard'))->assertSee('Pengguna');
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))->get(route('dashboard'))->assertDontSee('href="'.route('users.index').'"', false);
});

it('creates a user with a role and an audit entry without logging the password', function () {
    $admin = superAdmin();

    Livewire::actingAs($admin)->test(UserIndex::class)
        ->call('startCreate')
        ->set('name', 'Staf Baru')
        ->set('email', 'Staf.Baru@Sidona.test')
        ->set('userRole', 'auditor')
        ->set('password', 'rahasia123')
        ->call('save')
        ->assertHasNoErrors();

    $created = User::where('email', 'staf.baru@sidona.test')->first();
    expect($created->role)->toBe(UserRole::Auditor);
    expect($created->is_active)->toBeTrue();
    expect(ActivityLog::where('action', 'user.created')->count())->toBe(1);
    expect(ActivityLog::all()->toJson())->not->toContain('rahasia123');
});

it('validates the user form', function (string $field, string $value) {
    User::factory()->create(['email' => 'ada@sidona.test']);

    Livewire::actingAs(superAdmin())->test(UserIndex::class)
        ->call('startCreate')
        ->set('name', 'Staf')->set('email', 'staf@sidona.test')->set('userRole', 'auditor')->set('password', 'rahasia123')
        ->set($field, $value)
        ->call('save')
        ->assertHasErrors($field);
})->with([
    'no name' => ['name', ''],
    'bad email' => ['email', 'x'],
    'taken email' => ['email', 'ada@sidona.test'],
    'bad role' => ['userRole', 'dewa'],
    'weak password' => ['password', 'pendek'],
]);

it('edits a user, optionally resetting the password', function () {
    $target = User::factory()->create(['role' => UserRole::Auditor, 'name' => 'Lama']);

    Livewire::actingAs(superAdmin())->test(UserIndex::class)
        ->call('startEdit', $target->id)
        ->set('name', 'Baru')
        ->set('userRole', 'bendahara')
        ->set('password', 'passbaru123')
        ->call('save')
        ->assertHasNoErrors();

    $target->refresh();
    expect($target->name)->toBe('Baru');
    expect($target->role)->toBe(UserRole::Bendahara);
    expect(Hash::check('passbaru123', $target->password))->toBeTrue();
    expect(ActivityLog::where('action', 'user.password_reset')->count())->toBe(1);
});

it('protects the super admin from demoting or deactivating themselves', function () {
    $me = superAdmin();

    Livewire::actingAs($me)->test(UserIndex::class)
        ->call('startEdit', $me->id)
        ->set('userRole', 'auditor')
        ->call('save')
        ->assertHasErrors('userRole')
        ->call('toggleActive', $me->id);

    expect($me->fresh()->role)->toBe(UserRole::SuperAdmin);
    expect($me->fresh()->is_active)->toBeTrue();
});

it('deactivates a user who then cannot log in, and reactivates them', function () {
    $target = User::factory()->create(['role' => UserRole::Admin, 'password' => bcrypt('rahasia123')]);

    Livewire::actingAs(superAdmin())->test(UserIndex::class)->call('toggleActive', $target->id);
    expect($target->fresh()->is_active)->toBeFalse();
    expect(ActivityLog::where('action', 'user.deactivated')->count())->toBe(1);

    Livewire::test(LoginForm::class)
        ->set('email', $target->email)->set('password', 'rahasia123')
        ->call('authenticate')
        ->assertHasErrors('email');
    expect(auth()->id())->not->toBe($target->id);

    Livewire::actingAs(superAdmin())->test(UserIndex::class)->call('toggleActive', $target->id);

    Livewire::test(LoginForm::class)
        ->set('email', $target->email)->set('password', 'rahasia123')
        ->call('authenticate')
        ->assertHasNoErrors();
});

it('logs out an already signed-in user who gets deactivated', function () {
    $target = User::factory()->create(['role' => UserRole::Admin]);
    $this->actingAs($target)->get(route('dashboard'))->assertOk();

    $target->update(['is_active' => false]);

    $this->actingAs($target->fresh())->get(route('dashboard'))->assertRedirect(route('login'));
});
