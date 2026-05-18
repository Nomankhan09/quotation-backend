<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantDatabaseManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class ResolveTenant
{
    public function __construct(
        protected TenantDatabaseManager $manager
    ) {}

    public function handle(Request $request, Closure $next)
    {
        try {
            $payload  = JWTAuth::parseToken()->getPayload();
            $tenantId = $payload->get('tenant_id');
        } catch (\Exception $e) {
            Log::error('JWT Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        if (!$tenantId) {
            Log::error('No tenantId in token payload');
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // Central DB se tenant fetch karo
        $tenant = Tenant::on('mysql')->find($tenantId);

        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found'
            ], 404);
        }

        if ($tenant->isSuspended()) {
            return response()->json([
                'message' => 'Account suspended. Contact support.',
                'code'    => 'ACCOUNT_SUSPENDED'
            ], 403);
        }

        if ($tenant->isTrialExpired()) {
            return response()->json([
                'message' => 'Trial expired. Please subscribe.',
                'code'    => 'TRIAL_EXPIRED'
            ], 403);
        }

        // Tenant DB pe switch karo
        $this->manager->connect($tenant);
        DB::setDefaultConnection('tenant');
        app()->instance('tenant', $tenant);
        
        Log::info('Tenant resolved', [
            'tenant_id' => $tenant->id,
            'db_name' => $tenant->db_name,
            'tenant_connection_db' => DB::connection('tenant')->getDatabaseName(),
        ]);
        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
