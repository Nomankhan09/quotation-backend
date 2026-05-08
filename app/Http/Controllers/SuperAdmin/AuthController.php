<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $admin = SuperAdmin::where('email', $request->email)
                           ->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = JWTAuth::claims(['role' => 'super_admin'])
                        ->fromUser($admin);

        return response()->json([
            'token' => $token,
            'admin' => $admin,
        ]);
    }

    public function me(Request $request)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $admin   = SuperAdmin::find($payload->get('sub'));
            return response()->json(['admin' => $admin]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}