<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bid_bonds', function (Blueprint $table) {
            $table->id();
            $table->string('valid_period')->nullable();
            $table->string('tender_status')->nullable();
            $table->string('bond_name')->nullable();
            $table->string('bond_number')->nullable();
            $table->string('duration_date')->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->boolean('is_awarded')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bid_bonds');
    }
};
