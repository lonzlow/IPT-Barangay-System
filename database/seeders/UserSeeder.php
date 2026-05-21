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
            User::updateOrCreate(
                ['official_id' => $official->id],
                [
                    'email' => $this->makeEmail($official),
                    'password' => Hash::make('password'),
                    'status' => 'Inactive',
                ]
            );
        }
    }

    private function makeEmail(Official $official): string
    {
        $resident = $official->resident;
        $localPart = strtolower(
            $resident->first_name[0] . '.' . str_replace(' ', '', $resident->last_name) . '.' . str_replace('-', '', $official->id)
        );

        return $localPart . '@barangaynewera.gov.ph';
    }
}
