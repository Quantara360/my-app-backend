<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_in_hand_entries', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('cheque_no')->nullable();
            $table->text('description')->nullable();
            $table->decimal('debit', 15, 2)->nullable();
            $table->decimal('credit', 15, 2)->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_in_hand_entries');
    }
};
