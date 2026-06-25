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
        Schema::table('attendances', function (Blueprint $table) {
            $table->timestamp('out_marked_at')->nullable()->after('marked_at');
            $table->string('out_method')->nullable()->after('method');
            $table->float('out_confidence', 8, 4)->nullable()->after('confidence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['out_marked_at', 'out_method', 'out_confidence']);
        });
    }
};
