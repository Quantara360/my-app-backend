<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bid_bonds', function (Blueprint $table) {
            if (!Schema::hasColumn('bid_bonds', 'bond_name')) {
                $table->string('bond_name')->nullable();
            }
            if (!Schema::hasColumn('bid_bonds', 'bond_number')) {
                $table->string('bond_number')->nullable();
            }
        });

        Schema::table('performance_bonds', function (Blueprint $table) {
            if (!Schema::hasColumn('performance_bonds', 'bond_name')) {
                $table->string('bond_name')->nullable();
            }
            if (!Schema::hasColumn('performance_bonds', 'bond_number')) {
                $table->string('bond_number')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bid_bonds', function (Blueprint $table) {
            $table->dropColumn(['bond_name', 'bond_number']);
        });

        Schema::table('performance_bonds', function (Blueprint $table) {
            $table->dropColumn(['bond_name', 'bond_number']);
        });
    }
};
