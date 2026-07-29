<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_site_images', function (Blueprint $table) {
            // Make sub_site_id nullable so worksite-level images don't need a sub-site
            $table->unsignedBigInteger('sub_site_id')->nullable()->change();
            // Add worksite_id for images scoped directly to a worksite (no sub-site)
            $table->unsignedBigInteger('worksite_id')->nullable()->after('sub_site_id');
            $table->foreign('worksite_id')->references('id')->on('worksites')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('sub_site_images', function (Blueprint $table) {
            $table->dropForeign(['worksite_id']);
            $table->dropColumn('worksite_id');
            $table->unsignedBigInteger('sub_site_id')->nullable(false)->change();
        });
    }
};
