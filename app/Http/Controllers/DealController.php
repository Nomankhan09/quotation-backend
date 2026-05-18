<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Quotation;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $query = Deal::with([
            'lead:id,full_name,profile_image,company_name',
            'stage',
            'quotation' => function ($q) {
                $q->select('id', 'deal_id', 'total_amount');
            }
        ])->when($request->stage_id, fn($q) => $q->where('stage_id', $request->stage_id));

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
            'stage_id' => 'nullable|integer',
            'quotation_id' => 'nullable|array',
            'quotation_id.*' => 'integer'
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

        if (!empty($validated['quotation_id'])) {
            $quotations = Quotation::whereIn(
                'id',
                $validated['quotation_id']
            )->get();

            foreach ($quotations as $quotation) {
                $quotation->update([
                    'deal_id' => $deal->id
                ]);
            }
        }

        $deal->load(
            'lead',
            'stage',
            'quotation:id,deal_id,total_amount,status',
        );

        return response()->json([
            'message' => 'Deal created',
            'data' => $deal
        ], 201);
    }

    public function show($id)
    {
        $deal = Deal::with([
            'lead:id,full_name',
            'stage',
            'quotation' => function ($q) {
                $q->select('id', 'deal_id', 'total_amount')
                    ->withCount('products');
            }
        ])
            ->where("id", $id)
            ->first();

        if (!$deal) {
            return response()->json([
                'status' => 404,
                'message' => 'Deal not found',
            ]);
        }

        return response()->json(
            $deal
        );
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
            'quotation_id' => 'nullable|array',
            'quotation_id.*' => 'integer',
        ]);

        // quotation update
        if (!empty($validated['quotation_id'])) {

            // remove old links
            Quotation::where('deal_id', $deal->id)
                ->update(['deal_id' => null]);

            // attach new ones
            Quotation::whereIn(
                'id',
                $validated['quotation_id']
            )->update([
                'deal_id' => $deal->id
            ]);
        }

        $deal->update($validated);

        // for get detail after edit
        $deal->load([
            'lead',
            'stage',
            'quotation' => function ($q) {
                $q->select(
                    'id',
                    'deal_id',
                    'total_amount',
                    'status'
                )->withCount('products');
            }
        ]);

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

    public function dealStageChange(Request $req, $id)
    {
        $deal = Deal::where("id", $id)->first();

        if (!$deal) {
            return response()->json([
                'status' => 404,
                'message' => 'Deal not found',
            ]);
        }

        $deal->update([
            'stage_id' => $req->stage_id
        ]);

        //  after update load details
        $deal->load([
            'lead',
            'stage',
            'quotation' => function ($q) {
                $q->select(
                    'id',
                    'deal_id',
                    'total_amount',
                    'status'
                )->withCount('products');
            }
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Deal stage changed successfully',
            'data' => $deal
        ]);
    }
}
