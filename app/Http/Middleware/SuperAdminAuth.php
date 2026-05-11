<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class SuperAdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();

            if ($payload->get('role') !== 'super_admin') {
                return response()->json([
                    'message' => 'Unauthorized — Not a super admin'
                ], 403);
            }
        } catch (\Exception $e) {
          Log::error('JWT Error: '.$e->getMessage());
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}