<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        return response()->json([
            'plans' => Plan::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string',
            'price'          => 'required|numeric',
            'max_users'      => 'required|integer',
            'max_quotations' => 'required|integer',
        ]);

        $plan = Plan::create($request->all());

        return response()->json([
            'message' => 'Plan created',
            'plan'    => $plan
        ], 201);
    }

    public function update(Request $request, Plan $plan)
    {
        $plan->update($request->all());
        return response()->json([
            'message' => 'Plan updated',
            'plan'    => $plan
        ]);
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return response()->json([
            'message' => 'Plan deleted'
        ]);
    }
}