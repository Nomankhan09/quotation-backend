<?php

namespace Database\Seeders;

use App\Models\QuotationStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuotationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        QuotationStatus::insert([
            [
                'status' => 'draft',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'sent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'accepted',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'rejected',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
