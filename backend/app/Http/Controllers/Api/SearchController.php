<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('q');
        $branchId = $request->header('X-Branch-Id');

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];

        // 1. Customers
        $customersQuery = Customer::where(function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%");
        });
        if ($branchId) $customersQuery->where('branch_id', $branchId);
        
        $customers = $customersQuery->limit(5)->get();
        foreach ($customers as $c) {
            $results[] = [
                'type' => 'Customer',
                'id' => $c->id,
                'title' => $c->name,
                'subtitle' => $c->email ?? $c->phone ?? 'No contact info',
                'url' => "/dashboard/customers/{$c->id}"
            ];
        }

        // 2. Suppliers
        $suppliersQuery = Supplier::where(function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%");
        });
        if ($branchId) $suppliersQuery->where('branch_id', $branchId);
        
        $suppliers = $suppliersQuery->limit(5)->get();
        foreach ($suppliers as $s) {
            $results[] = [
                'type' => 'Supplier',
                'id' => $s->id,
                'title' => $s->name,
                'subtitle' => $s->email ?? $s->phone ?? 'No contact info',
                'url' => "/dashboard/suppliers/{$s->id}"
            ];
        }

        // 3. Products
        $products = Product::where('name', 'like', "%{$query}%")
                           ->orWhere('sku', 'like', "%{$query}%")
                           ->limit(5)->get();
        foreach ($products as $p) {
            $results[] = [
                'type' => 'Product',
                'id' => $p->id,
                'title' => $p->name,
                'subtitle' => "SKU: {$p->sku}",
                'url' => "/dashboard/products"
            ];
        }

        // 4. Invoices
        $invoicesQuery = Invoice::where('invoice_number', 'like', "%{$query}%");
        if ($branchId) $invoicesQuery->where('branch_id', $branchId);
        
        $invoices = $invoicesQuery->limit(5)->get();
        foreach ($invoices as $i) {
            $results[] = [
                'type' => 'Invoice',
                'id' => $i->id,
                'title' => $i->invoice_number,
                'subtitle' => "Status: {$i->status}",
                'url' => "/dashboard/invoices/{$i->id}"
            ];
        }

        // 5. Sales Orders
        $salesOrdersQuery = SalesOrder::where('order_number', 'like', "%{$query}%");
        if ($branchId) $salesOrdersQuery->where('branch_id', $branchId);
        
        $salesOrders = $salesOrdersQuery->limit(5)->get();
        foreach ($salesOrders as $so) {
            $results[] = [
                'type' => 'Sales Order',
                'id' => $so->id,
                'title' => $so->order_number,
                'subtitle' => "Status: {$so->status}",
                'url' => "/dashboard/sales"
            ];
        }

        // 6. Purchase Orders
        $purchaseOrdersQuery = PurchaseOrder::where('po_number', 'like', "%{$query}%");
        if ($branchId) $purchaseOrdersQuery->where('branch_id', $branchId);
        
        $purchaseOrders = $purchaseOrdersQuery->limit(5)->get();
        foreach ($purchaseOrders as $po) {
            $results[] = [
                'type' => 'Purchase Order',
                'id' => $po->id,
                'title' => $po->po_number,
                'subtitle' => "Status: {$po->status}",
                'url' => "/dashboard/purchases"
            ];
        }

        return response()->json($results);
    }
}
