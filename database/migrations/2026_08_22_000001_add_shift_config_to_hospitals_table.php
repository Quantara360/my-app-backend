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
        Schema::table('hospitals', function (Blueprint $table) {
            // All nullable - a hospital with no shift config falls back to
            // the app-wide defaults (see OfficeController::DEFAULT_SHIFT_CONFIG),
            // so nothing changes for any hospital until an admin explicitly
            // sets these via the new "Hospital Shifts" admin panel.
            $table->time('day_shift_start')->nullable()->after('worksite_id');
            $table->time('day_shift_end')->nullable()->after('day_shift_start');
            $table->unsignedInteger('day_late_grace_minutes')->nullable()->after('day_shift_end');
            $table->unsignedInteger('day_early_grace_minutes')->nullable()->after('day_late_grace_minutes');

            $table->time('night_shift_start')->nullable()->after('day_early_grace_minutes');
            $table->time('night_shift_end')->nullable()->after('night_shift_start');
            $table->unsignedInteger('night_late_grace_minutes')->nullable()->after('night_shift_end');
            $table->unsignedInteger('night_early_grace_minutes')->nullable()->after('night_late_grace_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
            $table->dropColumn([
                'day_shift_start',
                'day_shift_end',
                'day_late_grace_minutes',
                'day_early_grace_minutes',
                'night_shift_start',
                'night_shift_end',
                'night_late_grace_minutes',
                'night_early_grace_minutes',
            ]);
        });
    }
};
