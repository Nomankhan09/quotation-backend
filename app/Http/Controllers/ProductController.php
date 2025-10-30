<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request){

        $userId = auth()->id();

        $query = Product::with('category')->where('user_id', $userId);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('category', function ($cat) use ($search) {
                    $cat->where('category_name', 'like', "%{$search}%");
                });
            });
        }

        $limit = $request->get('limit', 5);
        $products = $query->latest()->paginate($limit);

        return response()->json($products);
    }

    public function store(Request $request) {
        $product = Product::create([
            'user_id'     => auth()->id(),
            'category_id' => $request->category_id,
            'product_name'=> $request->product_name,
            'unit_price'  => $request->unit_price,
            'description' => $request->description,
        ]);
        return response()->json($product, 201);
    }

    public function destroy($id) {
        $product = Product::where('user_id', auth()->id())->findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Product deleted']);
    }
}