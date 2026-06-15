<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->string('nic')->nullable()->after('phone');
            $table->unsignedInteger('age')->nullable()->after('nic');
            $table->date('join_date')->nullable()->after('age');
            $table->boolean('face_recognition_enabled')->default(false)->after('join_date');
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn(['nic', 'age', 'join_date', 'face_recognition_enabled']);
        });
    }
};
