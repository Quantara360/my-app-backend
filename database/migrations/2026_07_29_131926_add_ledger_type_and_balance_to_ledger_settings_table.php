<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        // Fresh installs already get these columns from create_ledger_settings_table;
        // this migration only needs to backfill them on databases created before that.
        Schema::table('ledger_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('ledger_settings', 'ledger_type')) {
                $table->string('ledger_type')->unique()->nullable();
            }
            if (! Schema::hasColumn('ledger_settings', 'manual_prev_balance')) {
                $table->decimal('manual_prev_balance', 14, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ledger_settings', function (Blueprint $table) {
            $table->dropColumn(array_filter(['ledger_type', 'manual_prev_balance'], fn ($col) => Schema::hasColumn('ledger_settings', $col)));
        });
    }
};
