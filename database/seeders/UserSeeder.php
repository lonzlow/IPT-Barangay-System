<?php

namespace Database\Seeders;

use App\Models\Official;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $officials = Official::with('resident')->get();

        foreach ($officials as $official) {
            User::factory()->create([
                'email' => strtolower(
                    $official->resident->first_name[0] . '.' . str_replace(' ', '', $official->resident->last_name)
                ) . '@barangaynewera.gov.ph',
                'password' => Hash::make('password'),
                'status' => 'Inactive',
            ]);
        }
    }
}
