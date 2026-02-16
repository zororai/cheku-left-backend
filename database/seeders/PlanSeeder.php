<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Monthly',
                'price' => 50.00,
                'duration_days' => 30,
            ],
            [
                'name' => 'Yearly',
                'price' => 500.00,
                'duration_days' => 365,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
