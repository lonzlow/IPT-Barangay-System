<?php

namespace Database\Seeders;

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
                'Admin', 'Punong Barangay', 'Secretary', 'Treasurer', 'Kagawad',
                'SK Chair', 'Tanod', 'BHW', 'BDRRM Coordinator', 'Encoder',
                'Auditor', 'Guest',
        ];

        foreach($roles as $role) {
            Role::factory()->create([
                'role_name' => $role,
            ]);
        }

        User::factory(5)->create();
    }
}
