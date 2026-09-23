<?php

use App\Models\User;
use App\Services\AuditLogger;

it('chains each new entry to the hash of the previous entry', function () {
    $logger = app(AuditLogger::class);
    $user = User::factory()->create();

    $first = $logger->log('campaign.created', $user, after: ['name' => 'Donasi Gempa']);
    $second = $logger->log('campaign.updated', $user, before: ['name' => 'Donasi Gempa'], after: ['name' => 'Donasi Gempa Cianjur']);

    expect($first->prev_hash)->toBe(AuditLogger::genesisHash());
    expect($second->prev_hash)->toBe($first->hash);
    expect($second->hash)->not->toBe($first->hash);
});

it('reports the chain as valid when nothing has been tampered with', function () {
    $logger = app(AuditLogger::class);
    $user = User::factory()->create();

    $logger->log('campaign.created', $user, after: ['name' => 'Donasi Gempa']);
    $logger->log('campaign.updated', $user, before: ['name' => 'Donasi Gempa'], after: ['name' => 'Donasi Gempa Cianjur']);

    expect($logger->verifyChain())->toBe(['valid' => true, 'tampered_at' => null]);
});

it('detects a row that was changed outside of AuditLogger', function () {
    $logger = app(AuditLogger::class);
    $user = User::factory()->create();

    $logger->log('campaign.created', $user, after: ['name' => 'Donasi Gempa']);
    $tampered = $logger->log('campaign.updated', $user, before: ['name' => 'Donasi Gempa'], after: ['name' => 'Donasi Gempa Cianjur']);

    $tampered->forceFill(['action' => 'campaign.deleted'])->saveQuietly();

    $result = $logger->verifyChain();

    expect($result['valid'])->toBeFalse();
    expect($result['tampered_at'])->toBe($tampered->id);
});
