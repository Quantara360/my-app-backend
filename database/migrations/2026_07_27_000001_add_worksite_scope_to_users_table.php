<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add worksite_id to users — nullable so existing users are unaffected
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('worksite_id')
                  ->nullable()
                  ->after('role')
                  ->constrained('worksites')
                  ->nullOnDelete();
        });

        // Pivot table: a supervisor can be scoped to 1 or more hospitals
        Schema::create('user_hospitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'hospital_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_hospitals');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['worksite_id']);
            $table->dropColumn('worksite_id');
        });
    }
};
