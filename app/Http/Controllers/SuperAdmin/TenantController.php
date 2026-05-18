<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\TenantUser;
use App\Services\TenantDatabaseManager;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    public function __construct(
        protected TenantDatabaseManager $manager
    ) {}

    // GET /api/superadmin/tenants
    public function index()
    {
        $tenants = Tenant::with('plan', 'subscriptions')
            ->latest()
            ->get();

        return response()->json([
            'tenants' => $tenants
        ]);
    }

    // GET /api/superadmin/tenants/{id}
    public function show(Tenant $tenant)
    {
        return response()->json([
            'tenant' => $tenant->load('plan', 'subscriptions')
        ]);
    }

    // POST /api/superadmin/tenants
    public function store(Request $request)
    {

        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:tenants,email',
            'phone'      => 'nullable|string|max:20',
            'plan_id'    => 'required|exists:plans,id',
            'password'   => 'required|string|min:8',
            'first_name' => 'required|string',
            'last_name'  => 'nullable|string',
            'db_name' => [
                'required',
                'string',
                'max:64',
                'unique:tenants,db_name',
            ],
            'db_username' => [
                'required',
                'string',
                'max:64',
            ],
            'db_password' => ['nullable', 'string'],
        ]);

        $dbName = strtolower($request->db_name);
        $dbUsername = strtolower($request->db_username);
        $dbPassword = $request->db_password;

        // 1. Central DB mein tenant record banao
        $tenant = Tenant::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'plan_id'       => $request->plan_id,
            'db_name'       => $dbName,
            'db_username'   => $dbUsername,
            'db_password'   => Crypt::encryptString($dbPassword),
            'status'        => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        // 2. Tenant ka DB banao + migrations run karo
        $this->manager->createDatabase($tenant);

        // 3. Tenant DB mein pehla user (admin) banao
        $this->manager->connect($tenant);
        $user = User::on('tenant')->create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name ?? '',
            'email'      => $request->email,
            'phone'      => $request->phone,
            'password'   => $request->password ? Hash::make($request->password) : null,
        ]);

        Company::on('tenant')->create([
            'company_name'           => $request->company_name,
            'company_address'        => $request->company_address,
            'zip_code'               => $request->zip_code,
            'company_email'          => $request->company_email,
            'company_phone'          => $request->company_phone,
            'website'                => $request->website,
            'company_logo'           => null,
            'company_type'           => $request->company_type,
            'pdf_file_name_format'   => $request->pdf_file_name_format ?? 'Quotation_{date}',
            'gst_number'            => $request->gst_number,

            // bank details
            'bank_name'             => $request->bank_name,
            'account_number'        => $request->account_number,
            'ifsc_code'            => $request->ifsc_code,
            'account_holder_name'   => $request->account_holder_name,
            'swift_code'           => $request->swift_code,
        ]);

        TenantUser::create([
            'tenant_id' => $tenant->id,
            'user_id'   => $user->id,
            'email'    => $user->email,
        ]);

        // 4. Subscription banao central DB mein
        Subscription::create([
            'tenant_id'  => $tenant->id,
            'plan_id'    => $request->plan_id,
            'status'     => 'active',
            'starts_at'  => now(),
            'ends_at'    => now()->addDays(14),
        ]);

        return response()->json([
            'message' => 'Tenant created successfully',
            'tenant'  => $tenant->load('plan'),
        ], 201);
    }

    // POST /api/superadmin/tenants/{tenant}/suspend
    public function suspend(Tenant $tenant)
    {
        $tenant->update(['status' => 'suspended']);
        return response()->json([
            'message' => 'Tenant suspended successfully'
        ]);
    }

    // POST /api/superadmin/tenants/{tenant}/activate
    public function activate(Tenant $tenant)
    {
        $tenant->update(['status' => 'active']);
        return response()->json([
            'message' => 'Tenant activated successfully'
        ]);
    }

    // DELETE /api/superadmin/tenants/{tenant}
    public function destroy(Tenant $tenant)
    {
        // DB permanently delete karo
        $this->manager->deleteDatabase($tenant);

        // Central DB record delete karo
        $tenant->delete();

        return response()->json([
            'message' => 'Tenant permanently deleted'
        ]);
    }
}
