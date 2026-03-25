<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessOwner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BusinessOwner::factory(5)->create();
        Business::factory(5)->create();

        for($i = 1; $i <= 3; $i++) {
            $owner = BusinessOwner::inRandomOrder()->first();
            $business = Business::inRandomOrder()->first();

            $owner->businesses()->attach($business->id);
        }
    }
    // php artisan db:seed --class=BusinessSeeder
}
