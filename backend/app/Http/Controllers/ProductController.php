<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index() { 
        return Product::with('category')->withSum('inventories', 'quantity')->get();
    }
    public function store(Request $request) {
        $validated = $request->validate([
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'gst_percentage' => 'nullable|numeric',
            'status' => 'nullable|string'
        ]);
        return Product::create($validated);
    }
    public function show(Product $product) { return $product->load('category'); }
    public function update(Request $request, Product $product) {
        $validated = $request->validate([
            'sku' => 'sometimes|string|unique:products,sku,'.$product->id,
            'name' => 'sometimes|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'purchase_price' => 'sometimes|numeric',
            'selling_price' => 'sometimes|numeric',
            'gst_percentage' => 'nullable|numeric',
            'status' => 'nullable|string'
        ]);
        $product->update($validated);
        return $product;
    }
    public function destroy(Product $product) {
        $product->delete();
        return response()->noContent();
    }
}
