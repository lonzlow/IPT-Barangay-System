<?php

namespace Database\Seeders;

use App\Models\Household;
use App\Models\Purok;
use App\Models\Resident;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    // php artisan migrate:fresh --seed
    // php artisan db:seed

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        /* User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]); */

        // User::factory(5)->create();

        Purok::factory()->create([
            'purok_name' => 'Purok 2',
        ]);

        Household::factory(5)->create();

        Resident::factory(20)->create();

        $this->call(RoleSeeder::class);
        $this->call(CommitteeOfficialSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(BusinessSeeder::class);
    }
}
