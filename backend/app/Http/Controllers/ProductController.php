<?php

namespace App\Http\Controllers;

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
        $this->authorize('view_products');

        return ProductResource::collection(
            Product::with(['category', 'supplier', 'unit', 'stock.locationable'])->get()
        );
    }

    public function store(StoreProductRequest $request, InventoryService $inventoryService)
    {
        $this->authorize('create_products');

        $data = $request->validated();
        if (!ProductResource::canManageSalesPrice($request)) {
            unset($data['selling_price']);
        }
        $openingStock = (float) ($data['opening_stock'] ?? 0);
        $siteId = (int) ($data['site_id'] ?? 0);
        unset($data['opening_stock'], $data['site_id']);

        $product = Product::create($data);

        if ($openingStock > 0 && $siteId) {
            $inventoryService->addStock(
                productId: $product->id,
                locationType: 'App\Models\Site',
                locationId: $siteId,
                quantity: $openingStock,
                referenceType: 'opening_stock',
                referenceId: $product->id,
                notes: 'Opening stock on product creation'
            );
        }

        return new ProductResource($product->load(['category', 'supplier', 'unit', 'stock.locationable']));
    }

    public function show(Product $product)
    {
        $this->authorize('view_products');

        return new ProductResource($product->load(['category', 'supplier', 'unit', 'stock.locationable']));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update_products');

        $data = $request->validated();
        if (!ProductResource::canManageSalesPrice($request)) {
            unset($data['selling_price']);
        }
        $product->update($data);
        return new ProductResource($product->load(['category', 'supplier', 'unit', 'stock.locationable']));
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete_products');

        $product->delete();
        return response()->noContent();
    }
}
