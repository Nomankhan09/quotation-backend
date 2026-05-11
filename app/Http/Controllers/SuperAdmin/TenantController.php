<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Services\TenantDatabaseManager;
use App\Models\User;
use Illuminate\Http\Request;
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
        ]);

        // Unique DB name generate karo
        $dbName = 'tenant_'
            . Str::slug($request->name, '_')
            . '_'
            . strtolower(Str::random(6));

        // 1. Central DB mein tenant record banao
        $tenant = Tenant::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'plan_id'       => $request->plan_id,
            'db_name'       => $dbName,
            'status'        => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        // 2. Tenant ka DB banao + migrations run karo
        $this->manager->createDatabase($tenant);

        // 3. Tenant DB mein pehla user (admin) banao
        $this->manager->connect($tenant);
        User::on('tenant')->create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name ?? '',
            'email'      => $request->email,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password),
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