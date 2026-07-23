<?php

namespace App\Http\Controllers;

use App\Models\ProductStock;
use App\Models\TransactionLedger;
use App\Models\Product;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryDashboardController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('inventory.dashboard.view');
        $locationType = $request->query('location_type');
        $locationId = $request->query('location_id');

        $stockQuery = ProductStock::query()->with('locationable');
        $txnQuery = TransactionLedger::query()->with('locationable');

        if ($locationType && $locationId) {
            $stockQuery->where('location_type', $locationType)->where('location_id', $locationId);
            $txnQuery->where('location_type', $locationType)->where('location_id', $locationId);
        }

        $totalProducts = Product::count();
        $totalLocations = Site::count();
        $totalStockValue = (clone $stockQuery)
            ->join('products', 'product_stock.product_id', '=', 'products.id')
            ->sum(DB::raw('product_stock.quantity * products.cost_price'));

        $lowStockItems = (clone $stockQuery)
            ->whereHas('product', function ($q) {
                $q->where('reorder_level', '>', 0)
                  ->whereColumn('product_stock.quantity', '<=', 'products.reorder_level');
            })
            ->with('product')
            ->count();

        $recentTransactions = $txnQuery
            ->with(['product', 'locationable'])
            ->latest()
            ->take(10)
            ->get();

        $stockByLocation = ProductStock::select('location_type', 'location_id', DB::raw('SUM(quantity) as total_qty'))
            ->with('locationable')
            ->groupBy('location_type', 'location_id')
            ->get();

        $topProducts = ProductStock::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        $transactionsByType = TransactionLedger::select('transaction_type', DB::raw('COUNT(*) as count'))
            ->groupBy('transaction_type')
            ->get();

        return response()->json([
            'total_products' => $totalProducts,
            'total_locations' => $totalLocations,
            'total_stock_value' => (float) $totalStockValue,
            'low_stock_items' => $lowStockItems,
            'recent_transactions' => $recentTransactions,
            'stock_by_location' => $stockByLocation,
            'top_products' => $topProducts,
            'transactions_by_type' => $transactionsByType,
        ]);
    }
}
