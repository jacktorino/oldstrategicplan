<?php

namespace Database\Seeders;

use App\Models\StrategicPlan;
use Illuminate\Database\Seeder;

class StrategicPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'title' => 'University of the Visayas',
                'school_year' => '2023-2024',
                'description' => null,
                'status' => 'Archived',
            ],
            [
                'title' => 'University of the Visayas',
                'school_year' => '2024-2025',
                'description' => null,
                'status' => 'Archived',
            ],
            [
                'title' => 'University of the Visayas',
                'school_year' => '2025-2026',
                'description' => null,
                'status' => 'Archived',
            ],
            [
                'title' => 'University of the Visayas',
                'school_year' => '2026-2027',
                'description' => null,
                'status' => 'Active',
            ],
        ];

        foreach ($plans as $plan) {
            StrategicPlan::updateOrCreate(
                [
                    'title' => $plan['title'],
                    'school_year' => $plan['school_year'],
                ],
                [
                    'description' => $plan['description'],
                    'status' => $plan['status'],
                    'created_by' => null,
                ]
            );
        }
    }
}
