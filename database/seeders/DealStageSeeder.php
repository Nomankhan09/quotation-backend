<?php

namespace Database\Seeders;

use App\Models\DealStage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DealStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DealStage::insert([
            [
                'stage_name' => 'New',
                'probability' => 10,
                'is_closed' => false,
                'is_won' => false,
                'color' => '#6B7280',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'stage_name' => 'Contacted',
                'probability' => 25,
                'is_closed' => false,
                'is_won' => false,
                'color' => '#3B82F6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'stage_name' => 'Qualified',
                'probability' => 40,
                'is_closed' => false,
                'is_won' => false,
                'color' => '#6366F1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'stage_name' => 'Proposal Sent',
                'probability' => 60,
                'is_closed' => false,
                'is_won' => false,
                'color' => '#F59E0B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'stage_name' => 'Negotiation',
                'probability' => 80,
                'is_closed' => false,
                'is_won' => false,
                'color' => '#8B5CF6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'stage_name' => 'Won',
                'probability' => 100,
                'is_closed' => true,
                'is_won' => true,
                'color' => '#10B981',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'stage_name' => 'Lost',
                'probability' => 0,
                'is_closed' => true,
                'is_won' => false,
                'color' => '#EF4444',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
