<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Supplier;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\InventoryService;

class ProductController extends Controller
{
    public function index()
    {
        return ProductResource::collection(
            Product::with(['category', 'supplier', 'unit', 'stock.location'])->get()
        );
    }

    public function store(StoreProductRequest $request, InventoryService $inventoryService)
    {
        $data = $request->validated();
        if (!ProductResource::canManageSalesPrice($request)) {
            unset($data['selling_price']);
        }
        $openingStock = (float) ($data['opening_stock'] ?? 0);
        unset($data['opening_stock']);

        $product = Product::create($data);

        if ($openingStock > 0) {
            $supplier = Supplier::where('name', 'InStorage')->first();
            $branch = Branch::first();
            if ($supplier && $branch) {
                $inventoryService->addStock(
                    $product->id,
                    $branch->id,
                    $openingStock,
                    'opening_stock',
                    $product->id,
                    'Opening stock on product creation'
                );
            }
        }

        return new ProductResource($product->load(['category', 'supplier', 'unit', 'stock.location']));
    }

    public function show(Product $product)
    {
        return new ProductResource($product->load(['category', 'supplier', 'unit', 'stock.location']));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        if (!ProductResource::canManageSalesPrice($request)) {
            unset($data['selling_price']);
        }
        $product->update($data);
        return new ProductResource($product->load(['category', 'supplier', 'unit', 'stock.location']));
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->noContent();
    }
}
