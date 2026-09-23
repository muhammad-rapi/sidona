<?php

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;

it('records a login log entry when a user authenticates successfully', function () {
    $user = User::factory()->create();

    event(new Login('web', $user, false));

    expect(LoginLog::query()
        ->where('user_id', $user->id)
        ->where('status', 'success')
        ->exists())->toBeTrue();
});

it('records a login log entry when authentication fails', function () {
    event(new Failed('web', null, ['email' => 'tidak-ada@sidona.test', 'password' => 'salah']));

    expect(LoginLog::query()
        ->whereNull('user_id')
        ->where('email', 'tidak-ada@sidona.test')
        ->where('status', 'failed')
        ->exists())->toBeTrue();
});
