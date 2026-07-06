<?php

namespace Database\Seeders;

use App\Models\ApprovalRequest;
use App\Models\Asset;
use App\Models\Chemical;
use App\Models\Machinery;
use App\Models\PeticashTransaction;
use App\Models\User;
use App\Models\Worksite;
use App\Models\Worker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name'     => 'Admin User',
            'username' => 'admin',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $supervisor = User::factory()->create([
            'name'     => 'Supervisor User',
            'username' => 'supervisor',
            'email'    => 'supervisor@example.com',
            'password' => bcrypt('password'),
            'role'     => 'supervisor',
        ]);

        $officeStaff = User::factory()->create([
            'name'     => 'Office Staff',
            'username' => 'office_staff',
            'email'    => 'office@example.com',
            'password' => bcrypt('password'),
            'role'     => 'officeStaff',
        ]);

        $cleanit = Worksite::create([
            'name' => 'CleanIt Worksite',
            'description' => 'Supervisor worksite for CleanIt operations.',
            'supervisor_id' => $supervisor->id,
        ]);

        $amil = Worksite::create([
            'name' => 'Amil Worksite',
            'description' => 'Supervisor worksite for Amil operations.',
            'supervisor_id' => $supervisor->id,
        ]);

        Worker::insert([
            [
                'name' => 'Amina',
                'role' => 'operator',
                'assigned_worksite_id' => $cleanit->id,
                'phone' => '09012345678',
                'status' => 'active',
                'nic' => '900123456V',
                'age' => 28,
                'join_date' => now()->subMonths(4)->toDateString(),
                'face_recognition_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Samuel',
                'role' => 'generator',
                'assigned_worksite_id' => $amil->id,
                'phone' => '09087654321',
                'status' => 'active',
                'nic' => '900987654V',
                'age' => 32,
                'join_date' => now()->subMonths(2)->toDateString(),
                'face_recognition_enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Asset::insert([
            ['name' => 'Safety Helmet', 'type' => 'PPE', 'location' => 'Main Office', 'status' => 'available', 'assigned_to' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mobile Camera', 'type' => 'Inspection', 'location' => 'Warehouse', 'status' => 'in use', 'assigned_to' => $officeStaff->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Chemical::insert([
            ['name' => 'Disinfectant', 'quantity' => '20L', 'hazard_level' => 'medium', 'storage_location' => 'Store Room 2', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Solvent', 'quantity' => '10L', 'hazard_level' => 'high', 'storage_location' => 'Chemical Storage', 'created_at' => now(), 'updated_at' => now()],
        ]);

        ApprovalRequest::insert([
            ['title' => 'Safety equipment purchase', 'description' => 'Purchase new protective vests for field crew.', 'status' => 'pending', 'requested_by' => $officeStaff->id, 'approved_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Overtime approval', 'description' => 'Approve overtime for supervisor team.', 'status' => 'approved', 'requested_by' => $officeStaff->id, 'approved_by' => $supervisor->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        PeticashTransaction::insert([
            ['title' => 'Stationery purchase', 'amount' => 45.50, 'status' => 'pending', 'requested_by' => $officeStaff->id, 'notes' => 'Printer paper and pens', 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Fuel reimbursement', 'amount' => 120.00, 'status' => 'approved', 'requested_by' => $officeStaff->id, 'notes' => 'Site transport cost', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Machinery::insert([
            ['name' => 'Bulldozer', 'status' => 'maintenance due', 'location' => 'Site A', 'maintenance_due_at' => now()->addDays(7), 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Forklift', 'status' => 'operational', 'location' => 'Warehouse', 'maintenance_due_at' => now()->addDays(30), 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
