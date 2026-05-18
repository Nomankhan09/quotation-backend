<?php

namespace Database\Seeders;

use App\Models\ContactStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ContactStatus::insert([
            [
                'status' => 'Lead',
                'color' => '#2563EB',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'Proposal',
                'color' => '#7C3AED',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'Won',
                'color' => '#059669',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'Lost',
                'color' => '#991B1B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'New',
                'color' => '#0EA5E9',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'Contacted',
                'color' => '#6366F1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'Follow Up',
                'color' => '#F59E0B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'Qualified',
                'color' => '#14B8A6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'Interested',
                'color' => '#22C55E',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'Not Interested',
                'color' => '#EF4444',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'Customer',
                'color' => '#8B5CF6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
