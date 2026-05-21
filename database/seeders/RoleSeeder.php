<?php

namespace Database\Seeders;

use App\Models\Official;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $aliases = [
            'Secretary' => 'Barangay Secretary',
            'Treasurer' => 'Barangay Treasurer',
            'SK Chair' => 'SK Chairperson',
            'Tanod' => 'Barangay Tanod',
            'BHW' => 'Health Worker / BHW',
            'Encoder' => 'Encoder / Data Entry Clerk',
            'Auditor' => 'Auditor / Inspector',
        ];

        foreach ($aliases as $alias => $canonical) {
            $aliasRole = Role::where('role_name', $alias)->first();
            $canonicalRole = Role::where('role_name', $canonical)->first();

            if (! $aliasRole) {
                continue;
            }

            if (! $canonicalRole) {
                $aliasRole->update(['role_name' => $canonical]);
                continue;
            }

            User::where('role_id', $aliasRole->id)->update(['role_id' => $canonicalRole->id]);
            Official::where('role_id', $aliasRole->id)->update(['role_id' => $canonicalRole->id]);
            $aliasRole->delete();
        }

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
            'Auditor / Inspector',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'role_name' => $role,
            ]);
        }
    }
}
