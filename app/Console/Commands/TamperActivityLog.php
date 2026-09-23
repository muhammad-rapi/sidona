<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('demo:tamper-log')]
#[Description('Directly corrupts the most recent activity log row, bypassing the app, for the integrity-verification demo.')]
class TamperActivityLog extends Command
{
    public function handle(): int
    {
        $entry = ActivityLog::query()->latest('id')->first();

        if (! $entry) {
            $this->error('Tidak ada activity log untuk dimanipulasi.');

            return self::FAILURE;
        }

        $entry->forceFill(['after' => ['tampered' => true]])->saveQuietly();

        $this->info("Baris #{$entry->id} telah dimanipulasi langsung di database.");

        return self::SUCCESS;
    }
}
