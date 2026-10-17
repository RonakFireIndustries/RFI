<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeProductRead($request);

        $query = Product::with(['category', 'supplier', 'unit', 'stock.locationable']);

        if ($categoryId = (int) $request->query('category_id')) {
            $query->whereIn('category_id', $this->categoryAndDescendants($categoryId));
        }

        return ProductResource::collection($query->get());
    }

    private function categoryAndDescendants(int $categoryId): array
    {
        $ids = [$categoryId];
        foreach (Category::where('parent_id', $categoryId)->pluck('id')->all() as $childId) {
            $ids = array_merge($ids, $this->categoryAndDescendants($childId));
        }
        return $ids;
    }

    public function store(StoreProductRequest $request, InventoryService $inventoryService)
    {
        $this->authorize('products.create');

        $data = $request->validated();
        if (!ProductResource::canManageSalesPrice($request)) {
            unset($data['selling_price']);
        }
        // purchase_price / selling_price are NOT NULL in the products table without
        // a default, but non-finance roles are never required to send them. Fall
        // back to zero so product creation cannot 500 for those roles.
        $data['purchase_price'] = $data['purchase_price'] ?? 0;
        $data['selling_price'] = $data['selling_price'] ?? 0;
        $data['cost_price'] = $data['cost_price'] ?? 0;
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
        $this->authorizeProductRead(request());

        return new ProductResource($product->load(['category', 'supplier', 'unit', 'stock.locationable']));
    }

    /**
     * Reading the product master is allowed with either grant:
     *
     *   - products.view      — the Inventory › Products module, which also
     *                          reveals its sidebar entry.
     *   - boq-products.view  — the read-only grant surfaced in the BOQ
     *                          (Quotations) section of Access Control, so a
     *                          BOQ user can pick products for BOQ lines.
     *                          Because the sidebar's Products entry is gated on
     *                          `products.view`, this grant never reveals it.
     *
     * Writes still require products.create / products.update / products.delete.
     */
    protected function authorizeProductRead(Request $request): void
    {
        $user = $request->user();

        if ($user && ($user->can('products.view') || $user->can('boq-products.view'))) {
            return;
        }

        abort(403, 'This action is unauthorized.');
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('products.update');

        $data = $request->validated();
        if (!ProductResource::canManageSalesPrice($request)) {
            unset($data['selling_price']);
        }
        $product->update($data);
        return new ProductResource($product->load(['category', 'supplier', 'unit', 'stock.locationable']));
    }

    public function destroy(Product $product)
    {
        $this->authorize('products.delete');

        $product->delete();
        return response()->noContent();
    }
}
