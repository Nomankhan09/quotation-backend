<?php

namespace Database\Seeders;

use App\Models\TaskPriority;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskPrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TaskPriority::insert([
            [
                'priority' => 'low',
                'color' => '#10b981',
                'icon' => 'arrow-down-outline',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'priority' => 'medium',
                'color' => '#f59e0b',
                'icon' => 'remove-outline',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'priority' => 'high',
                'color' => '#ef4444',
                'icon' => 'arrow-up-outline',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
