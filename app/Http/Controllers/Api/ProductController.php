<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse 
    {
        $products = Product::where('is_active',true)
            ->when($request->filled('game_id'), function ($query) use ($request) {
                $query->where('game_id', $request->game_id);
            })
            ->orderBy('sort_order')
            ->get();

            return response()->json([
                'data' => ProductResource::collection($products)
            ]);
    }
}
