<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Services\TenantDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
        // $credentials = $request->only('email', 'password');
        // if (!$token = auth()->attempt($credentials)) {
        //     return response()->json(['message' => 'Invalid Credentials'], 401);
        // }
        // return response()->json([
        //     'token' => $token,
        //     'user'  => auth()->user()
        // ]);
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $tenantUser = TenantUser::where(
            'email',
            $request->email
        )->first();

        if (!$tenantUser) {

            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        /*
    |--------------------------------------------------------------------------
    | STEP 2: Get tenant from central DB
    |--------------------------------------------------------------------------
    */

        $tenant = Tenant::find($tenantUser->tenant_id);

        if (!$tenant) {

            return response()->json([
                'message' => 'Tenant not found'
            ], 404);
        }

        /*
    |--------------------------------------------------------------------------
    | STEP 3: Status checks
    |--------------------------------------------------------------------------
    */

        if ($tenant->isSuspended()) {

            return response()->json([
                'message' => 'Account suspended',
                'code' => 'ACCOUNT_SUSPENDED'
            ], 403);
        }

        if ($tenant->isTrialExpired()) {

            return response()->json([
                'message' => 'Trial expired',
                'code' => 'TRIAL_EXPIRED'
            ], 403);
        }

        /*
    |--------------------------------------------------------------------------
    | STEP 4: Connect tenant DB
    |--------------------------------------------------------------------------
    */

        $this->manager->connect($tenant);

        /*
    |--------------------------------------------------------------------------
    | STEP 5: Authenticate in tenant DB
    |--------------------------------------------------------------------------
    */

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

        /*
    |--------------------------------------------------------------------------
    | STEP 6: Return response
    |--------------------------------------------------------------------------
    */
        $company = Company::on('tenant')->first();

        return response()->json([
            'token'  => $token,
            'user'   => auth('api')->user(),
            'company' => $company,
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
        $request->validate([
            'tenant_id'  => 'required|exists:tenants,id',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'email'      => 'required|email',
            'phone'      => 'nullable|string|max:20',
            'password'   => 'required|string|min:8',
        ]);

        // Get tenant
        $tenant = Tenant::findOrFail($request->tenant_id);

        // Connect tenant DB
        $this->manager->connect($tenant);

        // Create user inside tenant DB users table
        $user = User::on('tenant')->create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name ?? '',
            'email'      => $request->email,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password),
        ]);

        // Save into central DB tenant_users table
        $tenantUser = TenantUser::create([
            'tenant_id'      => $tenant->id,
            'tenant_user_id' => $user->id,
            'first_name'     => $request->first_name,
            'last_name'      => $request->last_name ?? '',
            'email'          => $request->email,
            'phone'          => $request->phone,
            'password'       => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $tenantUser,
        ], 201);
    }

    // Baaki existing methods same rehte hain
    public function updateCompanyInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name'    => 'required|max:255',
            'company_address' => 'sometimes|max:1000',
            'company_email'   => 'sometimes|email',
            'company_type'    => 'nullable',
            'zip_code'        => 'sometimes|max:20',
            'company_phone'   => 'sometimes|max:20',
            'website'         => 'sometimes|max:255',
            'pdf_file_name_format' => 'nullable',
            'gst_number'    => 'nullable|max:100',

            // bank deatils
            'bank_name'             => 'nullable|max:255',
            'account_number'        => 'nullable|max:100',
            'ifsc_code'             => 'nullable|max:50',
            'account_holder_name'   => 'nullable|max:255',
            'swift_code'            => 'nullable|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $company = Company::on('tenant')->first();

            /*
        |--------------------------------------------------------------------------
        | CREATE IF NOT EXISTS
        |--------------------------------------------------------------------------
        */

            if (!$company) {
                $company = Company::on('tenant')->create([]);
            }

            /*
        |--------------------------------------------------------------------------
        | UPDATE DATA
        |--------------------------------------------------------------------------
        */

            $updateData = $request->only([
                'company_name',
                'company_address',
                'zip_code',
                'company_phone',
                'website',
                'company_type',
                'pdf_file_name_format',
                'company_email',
                'gst_number',

                // bank details
                'bank_name',
                'account_number',
                'ifsc_code',
                'account_holder_name',
                'swift_code',
            ]);

            /*
        |--------------------------------------------------------------------------
        | HANDLE LOGO
        |--------------------------------------------------------------------------
        */

            if ($request->filled('company_logo')) {

                $logoData = $request->company_logo;

                if (preg_match(
                    '/^data:image\/(\w+);base64,/',
                    $logoData,
                    $type
                )) {

                    $imageData = substr(
                        $logoData,
                        strpos($logoData, ',') + 1
                    );

                    $imageType = strtolower($type[1]);

                    /*
                |--------------------------------------------------------------------------
                | VALIDATE IMAGE TYPE
                |--------------------------------------------------------------------------
                */

                    if (
                        !in_array(
                            $imageType,
                            ['jpg', 'jpeg', 'png', 'gif']
                        )
                    ) {

                        return response()->json([
                            'message' => 'Invalid image type',
                        ], 422);
                    }

                    $imageData = base64_decode($imageData);

                    /*
                |--------------------------------------------------------------------------
                | FILE NAME
                |--------------------------------------------------------------------------
                */

                    $filename = 'company-logo-' . Str::random(20) . '.' . $imageType;

                    $directory = public_path(
                        'company-logos'
                    );

                    /*
                |--------------------------------------------------------------------------
                | CREATE DIRECTORY
                |--------------------------------------------------------------------------
                */

                    if (!file_exists($directory)) {
                        mkdir($directory,     0755,    true);
                    }

                    /*
                |--------------------------------------------------------------------------
                | DELETE OLD LOGO
                |--------------------------------------------------------------------------
                */

                    if ($company->company_logo) {
                        $oldPath = public_path(
                            $company->company_logo
                        );

                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    /*
                |--------------------------------------------------------------------------
                | SAVE FILE
                |--------------------------------------------------------------------------
                */

                    file_put_contents(
                        $directory . '/' . $filename,
                        $imageData
                    );

                    $updateData['company_logo'] =
                        'company-logos/' . $filename;
                }
            }

            /*
        |--------------------------------------------------------------------------
        | UPDATE COMPANY
        |--------------------------------------------------------------------------
        */

            $company->update($updateData);
            $company->refresh();
            $companyData = $company->toArray();

            /*
        |--------------------------------------------------------------------------
        | APPEND FULL LOGO URL
        |--------------------------------------------------------------------------
        */

            if ($company->company_logo) {
                $companyData['company_logo_url'] = url(
                    $company->company_logo
                );
            }

            return response()->json([
                'message' =>  'Company information updated successfully',
                'user' => $companyData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update company information',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function getCryptpass(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $cryptpass = Crypt::encryptString($request->password);

        return response()->json([
            'cryptpass' => $cryptpass
        ]);
    }

    public function bootstrapData(Request $request)
    {
        try {
            $user = auth()->user();
            $company = Company::first();

            return response()->json([
                'success' => true,
                'user' => $user,
                'company' => $company,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function me(Request $request)
    {
        try {
            $user = auth('api')->user();
            $company = Company::first();

            return response()->json([
                'success' => true,
                'user' => $user,
                'company' => $company,
            ]);
        } catch (\Exception $e) {
            Log::error('AUTH_ME_FAILED', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
    
    // public function getCurrentUser()
    // {
    //     $user = auth()->user();
    //     return response()->json(['user' => $user], 200);
    // }