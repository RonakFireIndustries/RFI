<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Http\Resources\SupplierResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::with(['products.category', 'purchaseOrders', 'payments']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->paginate($request->input('per_page', 10));

        $suppliers->getCollection()->transform(function ($supplier) {
            $poSum = $supplier->purchaseOrders->sum('total_amount');
            $paymentSum = $supplier->payments->where('type', 'Payable')->sum('amount');
            $balance = $poSum - $paymentSum;

            $supplier->category = 'General';
            $supplier->status = 'Active';
            $supplier->balance = $balance;
            $supplier->performance = 5.0;

            return $supplier;
        });

        return SupplierResource::collection($suppliers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'gst_number' => 'nullable|string',
        ]);

        $supplier = Supplier::create($validated);
        return (new SupplierResource($supplier->load(['products.category', 'purchaseOrders'])))->response()->setStatusCode(201);
    }

    public function show($id)
    {
        $supplier = Supplier::with(['products.category', 'purchaseOrders', 'payments', 'notes.creator', 'documents.uploader'])->findOrFail($id);
        
        $poSum = $supplier->purchaseOrders->sum('total_amount');
        $paymentSum = $supplier->payments->where('type', 'Payable')->sum('amount');
        
        $supplier->total_lifetime_spend = $paymentSum;
        $supplier->balance = $poSum - $paymentSum;
        $supplier->active_orders_count = $supplier->purchaseOrders->whereNotIn('status', ['Delivered', 'Completed'])->count();
        
        return new SupplierResource($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'gst_number' => 'nullable|string',
        ]);

        $supplier->update($validated);
        return new SupplierResource($supplier->load(['products.category', 'purchaseOrders']));
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        return response()->noContent();
    }
}
