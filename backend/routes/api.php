<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\HRController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Api\SearchController;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/test', [TestController::class, 'test']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/search', [SearchController::class, 'search']);

    // Dashboard
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

    // Core HR
    Route::apiResource('branches', BranchController::class);
    Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:manage settings,sanctum');
    Route::get('/roles', [PermissionController::class, 'roles'])->middleware('permission:manage settings,sanctum');
    Route::put('/roles/{role}/permissions', [PermissionController::class, 'updateRolePermissions'])->middleware('permission:manage settings,sanctum');
    Route::apiResource('employees', EmployeeController::class)->middleware('permission:manage employees,sanctum');

    // Attendance
    Route::get('/attendances', [AttendanceController::class, 'index']);
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);

    // Inventory
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/inventory/transaction', [InventoryController::class, 'transaction']);
    Route::get('/inventory/transactions', [InventoryController::class, 'transactions']);
    
    // Inventory Transfers
    Route::get('/inventory/transfers', [\App\Http\Controllers\InventoryTransferController::class, 'index'])->middleware('permission:manage transfers,sanctum');
    Route::post('/inventory/transfers', [\App\Http\Controllers\InventoryTransferController::class, 'store'])->middleware('permission:manage transfers,sanctum');
    Route::put('/inventory/transfers/{transfer}/status', [\App\Http\Controllers\InventoryTransferController::class, 'updateStatus'])->middleware('permission:manage transfers,sanctum');

    // CRM & Sales
    // Purchases Management
    Route::apiResource('purchases/quotations', \App\Http\Controllers\PurchaseQuotationController::class);
    Route::post('purchases/quotations/{id}/convert', [\App\Http\Controllers\PurchaseQuotationController::class, 'convertToPo']);
    
    Route::apiResource('purchases/orders', \App\Http\Controllers\PurchaseOrderController::class);
    Route::post('purchases/orders/{id}/approve', [\App\Http\Controllers\PurchaseOrderController::class, 'approve']);
    
    Route::apiResource('purchases/grns', \App\Http\Controllers\GoodsReceiptNoteController::class);
    Route::apiResource('purchases/returns', \App\Http\Controllers\PurchaseReturnController::class);

    // Payments
    Route::apiResource('payments', \App\Http\Controllers\PaymentController::class);

    // Notes and Documents
    Route::apiResource('notes', \App\Http\Controllers\NoteController::class)->only(['store', 'destroy']);
    Route::post('documents', [\App\Http\Controllers\DocumentController::class, 'store']);
    Route::get('documents/{id}/download', [\App\Http\Controllers\DocumentController::class, 'download']);
    Route::delete('documents/{id}', [\App\Http\Controllers\DocumentController::class, 'destroy']);

    // Sales Management
    Route::apiResource('sales/quotations', \App\Http\Controllers\SalesQuotationController::class);
    Route::post('sales/quotations/{id}/convert', [\App\Http\Controllers\SalesQuotationController::class, 'convertToSo']);

    Route::apiResource('sales/orders', \App\Http\Controllers\SalesOrderController::class);
    Route::post('sales/orders/{id}/approve', [\App\Http\Controllers\SalesOrderController::class, 'approve']);

    Route::apiResource('sales/deliveries', \App\Http\Controllers\DeliveryNoteController::class);
    Route::apiResource('sales/returns', \App\Http\Controllers\SalesReturnController::class);

    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('invoices', InvoiceController::class);
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'generatePDF']);

    // HR Additions
    Route::get('/leaves', [HRController::class, 'leaves']);
    Route::post('/leaves', [HRController::class, 'requestLeave']);
    Route::get('/tasks', [HRController::class, 'tasks']);

    // Super Admin Generic Routes
    Route::middleware('role:Super Admin,sanctum')->group(function () {
        Route::get('/admin/tables', [AdminController::class, 'getTables']);
        Route::get('/admin/tables/{table}/schema', [AdminController::class, 'getTableSchema']);
        Route::get('/admin/tables/{table}', [AdminController::class, 'getTableData']);
        Route::post('/admin/tables/{table}', [AdminController::class, 'createRecord']);
        Route::put('/admin/tables/{table}/{id}', [AdminController::class, 'updateRecord']);
        Route::delete('/admin/tables/{table}/{id}', [AdminController::class, 'deleteRecord']);
    });
});
