<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ledger_settings', function (Blueprint $table) {
            $table->string('ledger_type')->unique()->nullable();
            $table->decimal('manual_prev_balance', 14, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ledger_settings', function (Blueprint $table) {
            $table->dropColumn(['ledger_type', 'manual_prev_balance']);
        });
    }
};
