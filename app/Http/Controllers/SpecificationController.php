<?php

namespace App\Http\Controllers;

use App\Models\Specification;
use Illuminate\Http\Request;

class SpecificationController extends Controller
{
    public function addSpecification(Request $request)
    {
        $request->validate([
            'item' => 'required|string',
            'description' => 'required|array',
            'description.*.description' => 'required|string',
        ]);

        $specification = new Specification();
        $specification->user_id = auth()->id();
        $specification->item = $request->item;
        $specification->description = $request->description;
        $specification->save();

        return response()->json([
            'status' => 201,
            'message' => 'Specification added successfully',
            'data' => $specification
        ], 201);
    }

    public function getSpecifications(Request $request)
    {
        $userId = auth()->id();
        $query = Specification::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('item', 'like', "%{$search}%")
                    ->orWhereRaw("JSON_SEARCH(description, 'one', ?) IS NOT NULL", ["%{$search}%"]);
            });
        }

        $limit = $request->get('limit', 5);
        $specifications = $query->latest()->paginate($limit);
        return response()->json(['status' => 200, 'data' => $specifications], 200);
    }

    public function deleteSpecification($id)
    {
        $specification = Specification::find($id);
        if (!$specification) {
            return response()->json(['status' => 404, 'message' => 'Specification not found'], 404);
        }

        $specification->delete();
        return response()->json(['status' => 200, 'message' => 'Specification deleted successfully'], 200);
    }

    public function updateSpecification(Request $request, $id)
    {
        $request->validate([
            'item' => 'required|string',
            'description' => 'required|array',
            'description.*.description' => 'required|string',
        ]);

        $specification = Specification::find($id);
        if (!$specification) {
            return response()->json(['status' => 404, 'message' => 'Specification not found'], 404);
        }

        $specification->item = $request->item;
        $specification->description = $request->description;
        $specification->save();

        return response()->json(['status' => 200, 'message' => 'Specification updated successfully'], 200);
    }
}
