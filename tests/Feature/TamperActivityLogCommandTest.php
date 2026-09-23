<?php

use App\Models\User;
use App\Services\AuditLogger;

it('tampers with the most recent activity log row and breaks the chain', function () {
    $user = User::factory()->create();
    app(AuditLogger::class)->log('campaign.created', $user, null, [], ['name' => 'Test']);
    app(AuditLogger::class)->log('campaign.updated', $user, null, ['name' => 'Test'], ['name' => 'Test 2']);

    expect(app(AuditLogger::class)->verifyChain()['valid'])->toBeTrue();

    $this->artisan('demo:tamper-log')->assertSuccessful();

    expect(app(AuditLogger::class)->verifyChain()['valid'])->toBeFalse();
});

it('fails gracefully when there is nothing to tamper with', function () {
    $this->artisan('demo:tamper-log')->assertFailed();
});
