<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPasswordOtpMail;
use App\Mail\PublicUserLeadMail;
use App\Models\Company;
use App\Models\PublicUser;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Services\TenantDatabaseManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

        $tenantUser = TenantUser::where(
            'email',
            $request->email
        )->first();

        if (!$tenantUser) {

            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }


        $tenant = Tenant::find($tenantUser->tenant_id);

        if (!$tenant) {

            return response()->json([
                'message' => 'Tenant not found'
            ], 404);
        }



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

        $this->manager->connect($tenant);


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
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'email'      => 'required|email|unique:public_users,email',
            'phone'      => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:1000',
        ]);

        $oldPublicUser = PublicUser::where('email', $request->email)->first();

        if($oldPublicUser) {
            return response()->json([
                'status' => 422,
                'message' => 'A request has already been submitted using this email address. Our team will contact you shortly.',
            ]);
        }

        // save in central db
        $publicUser = PublicUser::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company_name' => $request->company_name,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        // send email to internal team
        Mail::to('aman@flairm.com')
            ->send(new PublicUserLeadMail($publicUser));

        return response()->json([
            'message' => 'Your details were sent successfully. Our team will contact you shortly.',
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

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $tenantUser = TenantUser::where(
            'email',
            $request->email
        )->first();

        if (!$tenantUser) {
            return response()->json([
                'message' => 'Email not found'
            ], 404);
        }

        $otp = rand(100000, 999999);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(20),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Mail::to($request->email)
            ->send(new ForgotPasswordOtpMail($otp));

        return response()->json([
            'message' => 'OTP sent successfully'
        ]);
    }

    // verify OTP and reset password
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $reset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$reset) {
            return response()->json([
                'message' => 'Invalid OTP'
            ], 400);
        }

        if (Carbon::parse($reset->expires_at)->isPast()) {

            return response()->json([
                'message' => 'OTP expired'
            ], 400);
        }

        return response()->json([
            'message' => 'OTP verified'
        ]);
    }

    // reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        // check otp
        $reset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$reset) {
            return response()->json([
                'message' => 'Invalid OTP'
            ], 400);
        }

        // check expiry
        if (Carbon::parse($reset->expires_at)->isPast()) {
            return response()->json([
                'message' => 'OTP expired'
            ], 400);
        }

        // central db tenant user
        $tenantUser = TenantUser::where('email', $request->email)->first();

        if (!$tenantUser) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // get tenant
        $tenant = $tenantUser->tenant_id;

        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found'
            ], 404);
        }

        $tenant_db = Tenant::where('id', $tenant)->first();

        // switch tenant database dynamically
        config([
            'database.connections.tenant.database'
            => $tenant_db->db_name
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');

        // update tenant user password
        DB::connection('tenant')
            ->table('users')
            ->where('email', $request->email)
            ->update([
                'password' => Hash::make(
                    $request->password
                ),
                'updated_at' => now(),
            ]);

        // delete otp
        DB::table('password_resets')
            ->where('email', $request->email)
            ->delete();

        return response()->json([
            'message' => 'Password reset successful'
        ]);
    }
}
    
    // public function getCurrentUser()
    // {
    //     $user = auth()->user();
    //     return response()->json(['user' => $user], 200);
    // }