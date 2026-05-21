<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Services\TenantDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class TenantUserSyncController extends Controller
{
    protected TenantDatabaseManager $manager;

    public function __construct(TenantDatabaseManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Sync all tenant users to central DB
     */
    public function sync($tenantId)
    {
        try {

            // Get tenant from central DB
            $tenant = Tenant::findOrFail($tenantId);

            // Connect tenant DB
            $this->manager->connect($tenant);

            // Get users from tenant DB
            $users = User::on('tenant')->get();

            $synced = 0;

            foreach ($users as $user) {

                TenantUser::updateOrCreate(
                    [
                        'email' => $user->email,
                    ],
                    [
                        'tenant_id' => $tenant->id,
                        'user_id'   => $user->id,
                    ]
                );

                $synced++;
            }

            return response()->json([
                'success' => true,
                'message' => 'Tenant users synced successfully',
                'tenant'  => $tenant->name,
                'count'   => $synced,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function migrate(Request $request)
    {
        // SECURITY
        if ($request->header('x-sync-key') !== env('TENANT_SYNC_KEY')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // PREVENT MULTIPLE RUNS
        $lock = Cache::lock('tenant-migration-lock', 300);

        if (!$lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Migration already running'
            ], 429);
        }

        $results = [];

        try {

            $tenants = Tenant::all();

            foreach ($tenants as $tenant) {

                $this->manager->connect($tenant);

                $migrationPath = database_path('migrations/tenant');

                $files = File::files($migrationPath);

                foreach ($files as $file) {

                    $migrationName = pathinfo($file, PATHINFO_FILENAME);

                    // already migrated?
                    $exists = DB::connection('tenant')
                        ->table('migrations')
                        ->where('migration', $migrationName)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    try {

                        Artisan::call('migrate', [
                            '--database' => 'tenant',
                            '--path' => 'database/migrations/tenant/' . $file->getFilename(),
                            '--force' => true,
                        ]);

                        $results[] = [
                            'tenant_id' => $tenant->id,
                            'migration' => $migrationName,
                            'status' => 'success',
                        ];
                    } catch (\Exception $e) {

                        // table already exists
                        if (
                            $e->getCode() == '42S01' ||
                            str_contains($e->getMessage(), 'Base table or view already exists')
                        ) {

                            $batch = DB::connection('tenant')
                                ->table('migrations')
                                ->max('batch') + 1;

                            DB::connection('tenant')
                                ->table('migrations')
                                ->insert([
                                    'migration' => $migrationName,
                                    'batch' => $batch,
                                ]);

                            $results[] = [
                                'tenant_id' => $tenant->id,
                                'migration' => $migrationName,
                                'status' => 'skipped_existing_table',
                            ];

                            continue;
                        }

                        $results[] = [
                            'tenant_id' => $tenant->id,
                            'migration' => $migrationName,
                            'status' => 'failed',
                            'error' => $e->getMessage(),
                        ];
                    }
                }
            }
            $lock->release();

            return response()->json([
                'success' => true,
                'message' => 'Tenant migrations completed',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            $lock->release();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        } finally {
            optional($lock)->release();
        }
    }
}
