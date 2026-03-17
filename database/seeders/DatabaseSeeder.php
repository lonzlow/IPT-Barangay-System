<?php

namespace Database\Seeders;

use App\Models\Household;
use App\Models\Purok;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
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

        /* User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]); */

        $roles = [
                'Admin', 
                'Punong Barangay', 
                'Barangay Secretary', 
                'Barangay Treasurer', 
                'Kagawad',
                'SK Chairperson', 
                'Barangay Tanod', 
                'Health Worker / BHW', 
                'BDRRM Coordinator', 
                'Encoder / Data Entry Clerk',
                'Auditor',
                'Guest'
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['role_name' => $roleName]);
        }

        User::factory(5)->create();

        Purok::factory()->create([
            'purok_name' => 'Purok 2',
        ]);

        Household::factory(5)->create();

        Resident::factory(5)->create();
    }
}
