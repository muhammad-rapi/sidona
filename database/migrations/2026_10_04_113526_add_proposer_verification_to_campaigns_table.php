<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('proposer_phone')->nullable();
            $table->timestamp('proposer_verified_at')->nullable();
            $table->string('monitor_token', 64)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['proposer_phone', 'proposer_verified_at', 'monitor_token']);
        });
    }
};
