<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            ContactStatusSeeder::class,
            DealStageSeeder::class,
            QuotationStatusSeeder::class,
            TaskPrioritySeeder::class,
            TaskStatusSeeder::class,
            UserRoleSeeder::class
        ]);
    }
}
