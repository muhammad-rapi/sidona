<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('proof_path')->nullable()->change();
            $table->string('payment_method', 20)->nullable()->after('amount');
            $table->boolean('is_anonymous')->default(false)->after('donor_contact');
            $table->timestamp('paid_at')->nullable()->after('transferred_at');
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'is_anonymous', 'paid_at']);
        });
    }
};
