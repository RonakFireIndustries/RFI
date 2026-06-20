<?php

namespace App\Http\Controllers;

use App\Models\ProductStock;
use App\Http\Resources\ProductStockResource;
use Illuminate\Http\Request;

class ProductStockController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductStock::with(['product.category', 'product.unit', 'locationable']);

        if ($request->has('location_type') && $request->has('location_id')) {
            $query->where('location_type', $request->location_type)->where('location_id', $request->location_id);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('low_stock')) {
            $query->whereHas('product', function ($q) {
                $q->whereColumn('product_stock.quantity', '<=', 'products.reorder_level');
            });
        }

        return ProductStockResource::collection($query->get());
    }

    public function show(ProductStock $stock)
    {
        return new ProductStockResource($stock->load(['product.category', 'product.unit', 'locationable']));
    }

    public function byProduct($productId)
    {
        return ProductStockResource::collection(
            ProductStock::with('locationable')
                ->where('product_id', $productId)
                ->get()
        );
    }

    public function byLocation($locationType, $locationId)
    {
        $typeMap = ['site' => 'App\\Models\\Site'];
        $type = $typeMap[$locationType] ?? $locationType;

        return ProductStockResource::collection(
            ProductStock::with('product.category')
                ->where('location_type', $type)
                ->where('location_id', $locationId)
                ->get()
        );
    }
}
