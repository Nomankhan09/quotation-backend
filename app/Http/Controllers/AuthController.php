<?php
namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(
        protected TenantDatabaseManager $manager
    ) {}

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Step 1: Central DB mein tenant dhundo by email
        $tenant = Tenant::where('email', $request->email)->first();

        if (!$tenant) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Step 2: Status check karo
        if ($tenant->isSuspended()) {
            return response()->json([
                'message' => 'Account suspended. Contact support.',
                'code'    => 'ACCOUNT_SUSPENDED'
            ], 403);
        }

        if ($tenant->isTrialExpired()) {
            return response()->json([
                'message' => 'Trial expired. Please contact support.',
                'code'    => 'TRIAL_EXPIRED'
            ], 403);
        }

        // Step 3: Tenant DB pe switch karo
        $this->manager->connect($tenant);

        // Step 4: Tenant DB mein password verify karo
        $token = JWTAuth::claims([
            'tenant_id' => $tenant->id,
            'tenant_db' => $tenant->db_name,
        ])->attempt([
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        if (!$token) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        return response()->json([
            'token'  => $token,
            'user'   => auth('api')->user(),
            'tenant' => [
                'id'     => $tenant->id,
                'name'   => $tenant->name,
                'plan'   => $tenant->plan->name ?? 'trial',
                'status' => $tenant->status,
            ],
        ]);
    }

    // Registration public se band — sirf Super Admin banata hai tenants
    public function register(Request $request)
    {
        return response()->json([
            'message' => 'Self registration is disabled.'
        ], 403);
    }

    // Baaki existing methods same rehte hain
    public function updateCompanyInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name'    => 'required|string|max:255',
            'company_address' => 'sometimes|string|max:1000',
            'company_type'    => 'nullable|string',
            'zip_code'        => 'sometimes|string|max:20',
            'company_phone'   => 'sometimes|string|max:20',
            'website'         => 'sometimes|string|max:255',
            'pdf_file_name_format' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $user       = auth()->user();
            $updateData = $request->only([
                'company_name', 'company_address',
                'zip_code', 'company_phone',
                'website', 'company_type',
                'pdf_file_name_format'
            ]);

            if ($request->has('company_logo') && $request->company_logo) {
                $logoData = $request->company_logo;
                if (preg_match('/^data:image\/(\w+);base64,/', $logoData, $type)) {
                    $imageData = substr($logoData, strpos($logoData, ',') + 1);
                    $imageType = strtolower($type[1]);
                    if (!in_array($imageType, ['jpg', 'jpeg', 'png', 'gif'])) {
                        return response()->json([
                            'message' => 'Invalid image type.'
                        ], 422);
                    }
                    $imageData = base64_decode($imageData);
                    $filename  = 'company-logo-' . $user->id . '-' . Str::random(10) . '.' . $imageType;
                    $directory = public_path('company-logos');
                    if (!file_exists($directory)) {
                        mkdir($directory, 0755, true);
                    }
                    if ($user->company_logo) {
                        $oldPath = public_path($user->company_logo);
                        if (file_exists($oldPath)) unlink($oldPath);
                    }
                    file_put_contents($directory . '/' . $filename, $imageData);
                    $updateData['company_logo'] = 'company-logos/' . $filename;
                }
            }

            $user->update($updateData);
            $user->refresh();

            $userData = $user->toArray();
            if ($user->company_logo) {
                $userData['company_logo_url'] = url($user->company_logo);
            }

            return response()->json([
                'message' => 'Company information updated successfully',
                'user'    => $userData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update company information',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
    
    // public function getCurrentUser()
    // {
    //     $user = auth()->user();
    //     return response()->json(['user' => $user], 200);
    // }