<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessOwner;
use App\Models\BusinessPermit;
use App\Models\Official;
use App\Models\PermitRenewal;
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

        $businesses = Business::all();
        $i = 1;
        $official_id = Official::first()->id;

        foreach ($businesses as $business) {
            BusinessPermit::create([
                'business_id' => $business->id,
                'permit_number' => fake()->numberBetween(2000, 2026) . '-' .
                        str_pad($i++, 5, '0', STR_PAD_LEFT),
                'expiry_date' => now()->addYears(3)->format('Y-m-d'),
                'permit_status' => 'Approved',
                'issued_by' => $official_id,
            ]);
        }

        $permits = BusinessPermit::all();
        $official_id = Official::inRandomOrder()->first()->id;

        foreach ($permits as $permit) {
            PermitRenewal::create([
                'permit_id' => $permit->id,
                'fee_paid' => fake()->numberBetween(5000, 1000000),
                'renewal_date' => now()->format('Y-m-d'),
                'new_expiry_date' => now()->addYears(3),
                'processed_by' => $official_id,
            ]);
        }
    }
    // php artisan db:seed --class=BusinessSeeder
}
