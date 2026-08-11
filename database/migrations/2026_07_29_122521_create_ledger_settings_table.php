<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_settings', function (Blueprint $table) {
            $table->id();
            $table->string('ledger_type')->unique(); // 'bank' | 'cash_in_hand'
            $table->decimal('manual_prev_balance', 14, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_settings');
    }
};
