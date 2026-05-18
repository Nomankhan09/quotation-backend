<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $query = Category::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('category_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $limit = $request->get('limit', 5);
        $categories = $query->latest()->paginate($limit);

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $name = strtolower(trim($request->category_name));
        $matchedCategory = Category::where('user_id',auth()->id())->whereRaw('LOWER(category_name) = ?', [$name])->first();

        if ($matchedCategory) {
            return response()->json([
                'status' => 400,
                'message' =>  'Category "' . $request->category_name . '" already exists'
            ]);
        }

        $category = Category::create([
            'user_id'       => auth()->id(),
            'category_name' => $request->category_name,
            'description'   => $request->description,
            'color'   => $request->color,
        ]);
        return response()->json($category, 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::where("id", $id)->first();
        if (!$category) {
            return response()->json([
                'status' => 400,
                'message' =>  'Category not found'
            ]);
        }

        $name = strtolower(trim($request->category_name));
        $matchedCategory = Category::whereRaw('LOWER(category_name) = ?', [$name])
            ->where('id', '!=', $id)->first();

        if ($matchedCategory) {
            return response()->json([
                'status' => 400,
                'message' =>  'Category "' . $request->category_name . '" already exists'
            ]);
        }

        $category->update([
            'user_id'       => auth()->id(),
            'category_name' => $request->category_name,
            'description'   => $request->description,
            // 'color'   => $request->color,
        ]);
        return response()->json($category, 200);
    }

    public function destroy($id)
    {
        $category = Category::where('user_id', auth()->id())->findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Category deleted']);
    }
}
