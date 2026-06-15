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
        $tables = [
            'assets',
            'chemicals',
            'machineries',
            'approval_requests',
            'peticash_transactions',
            'office_staff_salaries',
            'worker_salaries',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreignId('worksite_id')->nullable()->constrained('worksites')->nullOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'assets',
            'chemicals',
            'machineries',
            'approval_requests',
            'peticash_transactions',
            'office_staff_salaries',
            'worker_salaries',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['worksite_id']);
                    $table->dropColumn('worksite_id');
                });
            }
        }
    }
};
