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
        Schema::create('sub_site_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_site_id')->constrained('sub_sites')->onDelete('cascade');
            $table->integer('book_id'); // 1, 2, or 3
            $table->string('image_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_site_images');
    }
};
