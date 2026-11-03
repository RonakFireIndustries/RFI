<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\InventoryService;
use App\Services\UnitConversionService;
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

    public function store(StoreProductRequest $request, InventoryService $inventoryService, UnitConversionService $unitConversion)
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
        $openingStockUnitId = ! empty($data['opening_stock_unit_id']) ? (int) $data['opening_stock_unit_id'] : null;
        // site_id only drives the stock seeding below, but opening_stock stays on
        // the product row so the catalog card/detail can display it.
        unset($data['site_id'], $data['opening_stock_unit_id']);

        $product = Product::create($data);

        // Whatever unit the user keyed, the stored figure is the product's own
        // unit — reports multiply it by the base-unit cost price. An unknown
        // conversion stays 1:1 (the form flags it before the save).
        $openingStockBase = $unitConversion->convertOrSame($openingStock, $openingStockUnitId, $product->unit_id);
        if ($openingStockBase !== $openingStock) {
            $product->update(['opening_stock' => $openingStockBase]);
        }

        if ($openingStock > 0 && $siteId) {
            $inventoryService->addStock(
                productId: $product->id,
                locationType: 'App\Models\Site',
                locationId: $siteId,
                quantity: $openingStock,
                referenceType: 'opening_stock',
                referenceId: $product->id,
                notes: 'Opening stock on product creation',
                unitId: $openingStockUnitId
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

    public function update(UpdateProductRequest $request, Product $product, UnitConversionService $unitConversion, InventoryService $inventoryService)
    {
        $this->authorize('products.update');

        $data = $request->validated();
        if (!ProductResource::canManageSalesPrice($request)) {
            unset($data['selling_price']);
        }

        $enteredOpeningStock = null;
        $openingStockUnitId = null;
        if (array_key_exists('opening_stock', $data)) {
            $openingStockUnitId = ! empty($data['opening_stock_unit_id']) ? (int) $data['opening_stock_unit_id'] : null;
            $enteredOpeningStock = (float) $data['opening_stock'];
            // Same rule as store(): the column holds the product's own unit.
            $data['opening_stock'] = $unitConversion->convertOrSame(
                $enteredOpeningStock,
                $openingStockUnitId,
                $product->unit_id
            );
        }
        $siteId = (int) ($data['site_id'] ?? 0);
        unset($data['opening_stock_unit_id'], $data['site_id']);

        $product->update($data);

        // Opening stock normally seeds product_stocks at creation time. If the
        // product was saved without one (e.g. opening stock entered later via
        // the edit form), seed it now — but only when the product has no
        // stock rows at all, so we never double-count after transactions.
        if ($siteId && $enteredOpeningStock > 0 && ! $product->stock()->exists()) {
            $inventoryService->addStock(
                productId: $product->id,
                locationType: 'App\Models\Site',
                locationId: $siteId,
                quantity: $enteredOpeningStock,
                referenceType: 'opening_stock',
                referenceId: $product->id,
                notes: 'Opening stock on product update',
                unitId: $openingStockUnitId
            );
        }

        return new ProductResource($product->load(['category', 'supplier', 'unit', 'stock.locationable']));
    }

    public function destroy(Product $product)
    {
        $this->authorize('products.delete');

        $product->delete();
        return response()->noContent();
    }
}
