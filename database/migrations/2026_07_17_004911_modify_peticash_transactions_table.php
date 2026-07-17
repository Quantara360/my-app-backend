<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('peticash_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('peticash_transactions', 'requested_by')) {
                if (DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign(['requested_by']);
                }
                $table->dropColumn('requested_by');
            }
            if (Schema::hasColumn('peticash_transactions', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('peticash_transactions', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('peticash_transactions', 'notes')) {
                $table->dropColumn('notes');
            }

            if (!Schema::hasColumn('peticash_transactions', 'type')) {
                $table->string('type')->default('expense');
            }
            if (!Schema::hasColumn('peticash_transactions', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('peticash_transactions', 'transaction_date')) {
                $table->date('transaction_date')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('peticash_transactions', function (Blueprint $table) {
            $table->dropColumn(['type', 'description', 'transaction_date']);

            $table->string('title')->default('');
            $table->string('status')->default('pending');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
        });
    }
};
