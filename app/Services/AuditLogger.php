<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AuditLogger
{
    public static function genesisHash(): string
    {
        return str_repeat('0', 64);
    }

    public function log(string $action, ?User $user, ?Model $subject = null, array $before = [], array $after = []): ActivityLog
    {
        return DB::transaction(function () use ($action, $user, $subject, $before, $after) {
            $previous = ActivityLog::query()->orderByDesc('id')->lockForUpdate()->first();
            $prevHash = $previous->hash ?? self::genesisHash();

            $payload = [
                'action' => $action,
                'user_id' => $user?->id,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'before' => $before,
                'after' => $after,
            ];

            $hash = hash('sha256', $prevHash.json_encode($payload, JSON_UNESCAPED_SLASHES));

            return ActivityLog::create([
                'user_id' => $user?->id,
                'action' => $action,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'before' => $before,
                'after' => $after,
                'prev_hash' => $prevHash,
                'hash' => $hash,
            ]);
        });
    }

    /**
     * @return array{valid: bool, tampered_at: int|null}
     */
    public function verifyChain(): array
    {
        $expectedPrevHash = self::genesisHash();
        $tamperedAt = null;

        foreach (ActivityLog::query()->orderBy('id')->cursor() as $entry) {
            $payload = [
                'action' => $entry->action,
                'user_id' => $entry->user_id,
                'subject_type' => $entry->subject_type,
                'subject_id' => $entry->subject_id,
                'before' => $entry->before ?? [],
                'after' => $entry->after ?? [],
            ];

            $expectedHash = hash('sha256', $expectedPrevHash.json_encode($payload, JSON_UNESCAPED_SLASHES));

            if ($entry->prev_hash !== $expectedPrevHash || $entry->hash !== $expectedHash) {
                $tamperedAt = $entry->id;
                break;
            }

            $expectedPrevHash = $entry->hash;
        }

        return [
            'valid' => $tamperedAt === null,
            'tampered_at' => $tamperedAt,
        ];
    }
}
