<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Tenant;
use App\Services\TenantDatabaseManager;

class TenantAuthMiddleware
{
    public function __construct(
        protected TenantDatabaseManager $manager
    ) {}

    public function handle(Request $request, Closure $next)
    {
        try {

            $token = JWTAuth::parseToken();

            $payload = $token->getPayload();

            $tenantId = $payload->get('tenant_id');

            if (!$tenantId) {
                return response()->json([
                    'message' => 'Tenant missing'
                ], 401);
            }

            $tenant = Tenant::on('mysql')->find($tenantId);

            if (!$tenant) {
                return response()->json([
                    'message' => 'Tenant not found'
                ], 404);
            }

            $this->manager->connect($tenant);

            DB::setDefaultConnection('tenant');

            $user = $token->authenticate();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            auth('api')->setUser($user);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Unauthorized',
                'error' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}
