<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Migrate existing hospital rows from worksites → hospitals table
        //    Map by worksite parent_id (which is the main site id)
        $hospitals = DB::table('worksites')->where('type', 'hospital')->get();
        $hospitalIdMap = []; // old worksite id → new hospital id

        foreach ($hospitals as $h) {
            $newId = DB::table('hospitals')->insertGetId([
                'name'        => $h->name,
                'description' => $h->description ?? null,
                'worksite_id' => $h->parent_id, // parent_id was the main site id
                'created_at'  => $h->created_at,
                'updated_at'  => $h->updated_at,
            ]);
            $hospitalIdMap[$h->id] = $newId;
        }

        // 2. Migrate existing sub_site rows from worksites → sub_sites table
        $subSites = DB::table('worksites')->where('type', 'sub_site')->get();

        foreach ($subSites as $s) {
            // parent_id was the old hospital worksite id → map to new hospital id
            $newHospitalId = $hospitalIdMap[$s->parent_id] ?? null;
            if ($newHospitalId) {
                DB::table('sub_sites')->insert([
                    'name'        => $s->name,
                    'description' => $s->description ?? null,
                    'hospital_id' => $newHospitalId,
                    'created_at'  => $s->created_at,
                    'updated_at'  => $s->updated_at,
                ]);
            }
        }

        // 3. Remove hospital/sub_site rows from worksites (keep only main_site rows)
        DB::table('worksites')->whereIn('type', ['hospital', 'sub_site'])->delete();

        // 4. Drop the type and parent_id columns from worksites
        Schema::table('worksites', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['type', 'parent_id']);
        });
    }

    public function down(): void
    {
        // Re-add columns
        Schema::table('worksites', function (Blueprint $table) {
            $table->string('type')->default('main_site');
            $table->foreignId('parent_id')->nullable()->constrained('worksites')->nullOnDelete();
        });
    }
};
