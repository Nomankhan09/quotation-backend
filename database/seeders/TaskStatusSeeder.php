<?php

namespace Database\Seeders;

use App\Models\TaskStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TaskStatus::insert([
            [
                'status' => 'pending',
                'color' => '#f59e0b',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'completed',
                'color' => '#10b981',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
