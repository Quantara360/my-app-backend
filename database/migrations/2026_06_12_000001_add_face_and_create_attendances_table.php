<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add face photo path to workers table
        Schema::table('workers', function (Blueprint $table) {
            $table->string('face_photo_path')->nullable()->after('face_recognition_enabled');
        });

        // Create attendances table
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->foreignId('worksite_id')->nullable()->constrained('worksites')->nullOnDelete();
            $table->string('shift')->default('Morning'); // Morning / Evening
            $table->date('date');
            $table->timestamp('marked_at')->useCurrent();
            $table->string('status')->default('present'); // present / absent / late
            $table->string('method')->default('face');    // face / manual
            $table->float('confidence', 8, 4)->nullable(); // face match distance score
            $table->timestamps();

            // One attendance record per worker per shift per day
            $table->unique(['worker_id', 'date', 'shift'], 'unique_worker_attendance');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');

        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn('face_photo_path');
        });
    }
};
