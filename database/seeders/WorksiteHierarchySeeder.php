<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Worksite;

class WorksiteHierarchySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add AMIL (Main Site)
        $amil = Worksite::firstOrCreate(
            ['name' => 'AMIL'],
            ['description' => 'AMIL Main Site', 'type' => 'main_site']
        );

        // Add CLEAN IT (Main Site)
        $cleanIt = Worksite::firstOrCreate(
            ['name' => 'CLEAN IT'],
            ['description' => 'CLEAN IT Main Site', 'type' => 'main_site']
        );

        // Add Hospitals under AMIL
        $apeksha = Worksite::firstOrCreate(
            ['name' => 'Apeksha Hospital', 'parent_id' => $amil->id],
            ['description' => 'Apeksha Hospital', 'type' => 'hospital']
        );

        $castle = Worksite::firstOrCreate(
            ['name' => 'Castle Women\'s Hospital', 'parent_id' => $amil->id],
            ['description' => 'Castle Women\'s Hospital', 'type' => 'hospital']
        );

        $national = Worksite::firstOrCreate(
            ['name' => 'National Hospital', 'parent_id' => $amil->id],
            ['description' => 'National Hospital', 'type' => 'hospital']
        );

        // Add Sub Sites under Apeksha Hospital
        Worksite::firstOrCreate(
            ['name' => 'Apeksha', 'parent_id' => $apeksha->id],
            ['description' => 'Apeksha Sub Site', 'type' => 'sub_site']
        );

        Worksite::firstOrCreate(
            ['name' => 'Razavi', 'parent_id' => $apeksha->id],
            ['description' => 'Razavi Sub Site', 'type' => 'sub_site']
        );

        $this->command->info('Worksite hierarchy seeded successfully!');
    }
}
