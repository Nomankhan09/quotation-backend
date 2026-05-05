<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Lead;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $query = Deal::where('user_id', auth()->id())
            ->where('lead_id', $request->lead_id);

        if ($request->stage_id) {
            $query->where('stage_id', $request->stage_id);
        }

        return response()->json(
            $query->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|integer',
            'title' => 'nullable|string|max:255',
            'value' => 'nullable|numeric',
            'expected_close_date' => 'nullable|date',
            'assigned_to' => 'nullable|integer',
            'description' => 'nullable|string',
            'stage_id' => 'nullable|integer'
        ]);

        $validated['user_id'] = auth()->id();

        $lead = Lead::where('id', $validated['lead_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // 🔥 Title fallback (clean)
        if (empty($validated['title'])) {
            $validated['title'] = $lead->full_name . ' - Opportunity';
        }

        $deal = Deal::create($validated);

        return response()->json([
            'message' => 'Deal created',
            'data' => $deal
        ], 201);
    }

    public function show($id)
    {
        $deal = Deal::where('user_id', auth()->id())->findOrFail($id);

        return response()->json($deal);
    }

    public function update(Request $request, $id)
    {
        $deal = Deal::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'stage_id' => 'sometimes|integer',
            'title' => 'sometimes|string|max:255',
            'value' => 'nullable|numeric',
            'expected_close_date' => 'nullable|date',
            'assigned_to' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        $deal->update($validated);

        return response()->json([
            'message' => 'Deal updated',
            'data' => $deal
        ]);
    }

    public function destroy($id)
    {
        $deal = Deal::where('user_id', auth()->id())->findOrFail($id);

        $deal->delete();

        return response()->json([
            'message' => 'Deal deleted'
        ]);
    }
}
