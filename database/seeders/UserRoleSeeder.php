<?php

namespace Database\Seeders;

use App\Models\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserRole::insert([
            [
                'role' => 'ADMIN',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role' => 'USER',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
