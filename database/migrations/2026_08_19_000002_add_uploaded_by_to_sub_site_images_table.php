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
        Schema::table('sub_site_images', function (Blueprint $table) {
            // Tracks which supervisor (user) captured this image, so:
            //  - each supervisor's own "Preview" capture screen can be scoped
            //    to only their own uploads, instead of showing every
            //    supervisor's photos for the same book/site.
            //  - office staff browsing the books/documents can see who
            //    uploaded each photo. Nullable + nullOnDelete so pre-existing
            //    images (uploaded before this column existed) and images
            //    whose uploader account is later deleted still display fine.
            $table->foreignId('uploaded_by')->nullable()->after('book_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_site_images', function (Blueprint $table) {
            $table->dropConstrainedForeignId('uploaded_by');
        });
    }
};
