<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_staff_salaries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('salary', 12, 2)->default(0);
            $table->string('type')->default('monthly');
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_staff_salaries');
    }
};
