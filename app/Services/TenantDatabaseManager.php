<?php
namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

class TenantDatabaseManager
{
    public function connect(Tenant $tenant): void
    {
        Config::set(
            'database.connections.tenant.database',
            $tenant->db_name
        );
        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public function createDatabase(Tenant $tenant): void
    {
        // Step 1: Create DB
        DB::statement(
            "CREATE DATABASE IF NOT EXISTS `{$tenant->db_name}`"
        );

        // Step 2: Connect to new DB
        $this->connect($tenant);

        // Step 3: Run tenant migrations
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path'     => 'database/migrations/tenant',
            '--force'    => true,
        ]);
    }

    public function deleteDatabase(Tenant $tenant): void
    {
        DB::statement(
            "DROP DATABASE IF EXISTS `{$tenant->db_name}`"
        );
    }
}