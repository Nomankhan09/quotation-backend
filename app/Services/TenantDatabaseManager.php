<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;

class TenantDatabaseManager
{
    public function connect(Tenant $tenant): void
    {
        // Config::set(
        //     'database.connections.tenant.database',
        //     $tenant->db_name
        // );
        // DB::purge('tenant');
        // DB::reconnect('tenant');
        DB::purge('tenant');

        config([
            'database.connections.tenant.driver' => 'mysql',
            'database.connections.tenant.host' => env('DB_HOST', '127.0.0.1'),
            'database.connections.tenant.port' => env('DB_PORT', '3306'),
            'database.connections.tenant.database' => $tenant->db_name,
            'database.connections.tenant.username' => $tenant->db_username,
            'database.connections.tenant.password' => $tenant->db_password ? Crypt::decryptString($tenant->db_password) : null,
            'database.connections.tenant.charset' => 'utf8mb4',
            'database.connections.tenant.collation' => 'utf8mb4_unicode_ci',
            'database.connections.tenant.prefix' => '',
            'database.connections.tenant.strict' => true,
        ]);

        DB::reconnect('tenant');

        DB::connection('tenant')->getPdo();
        // DB::setDefaultConnection('tenant');
    }

    public function createDatabase(Tenant $tenant): void
    {
        try {
            $this->connect($tenant);
            DB::connection('tenant')->getPdo();
        } catch (\Exception $e) {

            throw new \Exception(
                'Failed to connect tenant database: ' . $e->getMessage()
            );
        }

        // Step 3: Run tenant migrations
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path'     => 'database/migrations/tenant',
            '--force'    => true,
        ]);

        // Step 4: Run tenant seeders
        Artisan::call('db:seed', [
            '--database' => 'tenant',
            '--class'    => 'Database\\Seeders\\TenantDatabaseSeeder',
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
