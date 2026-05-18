<?php

namespace App\Http\Controllers;

use App\Models\AppError;
use App\Models\Tenant;
use App\Services\TenantDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AppErrorController extends Controller
{
    public function __construct(
        protected TenantDatabaseManager $manager
    ) {}

    public function store(Request $request)
    {
        try {

            $tenant = null;

            /*
        |--------------------------------------------------------------------------
        | Try resolving tenant from JWT
        |--------------------------------------------------------------------------
        */

            try {

                if ($request->bearerToken()) {

                    $payload = JWTAuth::parseToken()->getPayload();

                    $tenantId = $payload->get('tenant_id');

                    if ($tenantId) {

                        $tenant = Tenant::on('mysql')->find($tenantId);

                        if ($tenant) {

                            $this->manager->connect($tenant);

                            DB::setDefaultConnection('tenant');
                        }
                    }
                }
            } catch (\Exception $e) {

                Log::warning('APP_ERROR JWT parse failed', [
                    'error' => $e->getMessage()
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | Prepare data
        |--------------------------------------------------------------------------
        */

            $data = [
                'user_id' => auth()->id(),

                'message' => $request->input('message'),

                'stack' => is_array($request->input('stack'))
                    ? json_encode($request->input('stack'))
                    : $request->input('stack'),

                'screen' => $request->input('screen'),

                'platform' => $request->input('platform'),

                'is_fatal' => (bool) $request->input('is_fatal'),

                'app_version' => $request->input('app_version'),
            ];

            /*
        |--------------------------------------------------------------------------
        | Save to tenant DB only if tenant resolved
        |--------------------------------------------------------------------------
        */

            if ($tenant) {
                AppError::create($data);
            }

            /*
        |--------------------------------------------------------------------------
        | Always save to laravel logs
        |--------------------------------------------------------------------------
        */

            Log::error('APP_ERROR', [
                ...$data,
                'tenant_id' => $tenant?->id,
                'tenant_db' => $tenant?->db_name,
            ]);

            return response()->json([
                'ok' => true
            ]);
        } catch (\Exception $e) {

            Log::error('APP_ERROR_SAVE_FAILED', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to save app error',
            ], 500);
        }
    }
}
