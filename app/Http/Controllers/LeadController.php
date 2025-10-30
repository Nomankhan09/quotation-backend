<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request){
        $userId = auth()->id();

        $query = Lead::where('user_id', $userId);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $limit = $request->get('limit', 5);
        $leads = $query->latest()->paginate($limit);

        return response()->json($leads);
    }

    public function store(Request $request) {
        $lead = Lead::create([
            'user_id'      => auth()->id(),
            'full_name'    => $request->full_name,
            'company_name' => $request->company_name,
            'email'        => $request->email,
            'phone'        => $request->phone,
            'notes'        => $request->notes,
        ]);
        return response()->json($lead, 201);
    }

    public function destroy($id) {
        $lead = Lead::where('user_id', auth()->id())->findOrFail($id);
        $lead->delete();
        return response()->json(['message' => 'Lead deleted']);
    }
}

