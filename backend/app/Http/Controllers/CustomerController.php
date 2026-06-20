<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['salesOrders.products', 'payments']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate($request->input('per_page', 10));

        // Add dynamic stats
        $customers->getCollection()->transform(function ($customer) {
            $soSum = $customer->salesOrders->sum('total_amount');
            $paymentSum = $customer->payments->where('type', 'Receivable')->sum('amount');
            
            $customer->orders_count = $customer->salesOrders->count();
            $customer->last_order_date = $customer->salesOrders->max('created_at');
            $customer->balance = $soSum - $paymentSum;
            $customer->status = 'Active'; // Default
            $customer->segment = 'Enterprise'; // Default for UI purposes
            return $customer;
        });

        return CustomerResource::collection($customers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string',
            'gst_number' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);
        return (new CustomerResource($customer->load(['salesOrders.products'])))->response()->setStatusCode(201);
    }

    public function show($id)
    {
        $customer = Customer::with(['salesOrders.products', 'payments', 'notes.creator', 'documents.uploader'])->findOrFail($id);
        
        $soSum = $customer->salesOrders->sum('total_amount');
        $paymentSum = $customer->payments->where('type', 'Receivable')->sum('amount');
        
        $customer->total_revenue = $paymentSum; 
        $customer->outstanding_balance = $soSum - $paymentSum;
        $customer->total_orders = $customer->salesOrders->count();
        $customer->status = 'Active';
        
        return new CustomerResource($customer);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,'.$id,
            'phone' => 'nullable|string',
            'gst_number' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $customer->update($validated);
        return new CustomerResource($customer->load(['salesOrders.products']));
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return response()->noContent();
    }
}
