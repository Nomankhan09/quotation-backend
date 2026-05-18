<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Services\TenantDatabaseManager;

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
}
