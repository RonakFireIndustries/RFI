<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// EmployeeController imported from Api\V1 below
use App\Http\Controllers\Api\V1\DesignationController;
use App\Http\Controllers\Api\V1\ShiftController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\DailyReportController;
use App\Http\Controllers\Api\V1\LeaveTypeController;
use App\Http\Controllers\Api\V1\LeaveBalanceController;
use App\Http\Controllers\Api\V1\LeaveController;
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
// Removed old Department/Designation/Employee controller imports
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserAccessController;
use App\Http\Controllers\SalaryStructureController;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\InventoryLocationController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UnitConversionController;
use App\Http\Controllers\ProductStockController;
use App\Http\Controllers\TransactionLedgerController;
use App\Http\Controllers\StockRequestController;
use App\Http\Controllers\InventoryDashboardController;


Route::prefix('v1')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);
    Route::post('/forgot-password', [\App\Http\Controllers\Api\V1\PasswordResetController::class, 'sendResetLinkEmail']);
    Route::post('/reset-password', [\App\Http\Controllers\Api\V1\PasswordResetController::class, 'reset']);
    
    Route::get('/test', [TestController::class, 'test']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [\App\Http\Controllers\Api\V1\AuthController::class, 'user']);
        Route::post('/logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
        Route::put('/profile', [\App\Http\Controllers\Api\V1\ProfileController::class, 'update']);
        
        Route::get('/search', [SearchController::class, 'search']);

    // Dashboard
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Core HR
    // Role & Permission Management
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);

    // User Access Management
    Route::get('/users/{user}/roles', [UserAccessController::class, 'getRoles']);
    Route::post('/users/{user}/roles', [UserAccessController::class, 'assignRole']);
    Route::delete('/users/{user}/roles/{role}', [UserAccessController::class, 'removeRole']);
    
    Route::get('/users/{user}/permissions', [UserAccessController::class, 'getPermissions']);
    Route::post('/users/{user}/permissions', [UserAccessController::class, 'assignPermission']);
    Route::delete('/users/{user}/permissions/{permission}', [UserAccessController::class, 'removePermission']);

    Route::apiResource('employees', \App\Http\Controllers\Api\V1\EmployeeController::class);
    Route::get('employees/{employee}/subordinates', [\App\Http\Controllers\Api\V1\EmployeeController::class, 'subordinates']);
    Route::get('employees/{employee}/manager', [\App\Http\Controllers\Api\V1\EmployeeController::class, 'manager']);

    // Employee Documents
    Route::get('documents/expiring', [\App\Http\Controllers\Api\V1\EmployeeDocumentController::class, 'expiring']);
    Route::get('employees/{employee}/documents', [\App\Http\Controllers\Api\V1\EmployeeDocumentController::class, 'index']);
    Route::post('employees/{employee}/documents', [\App\Http\Controllers\Api\V1\EmployeeDocumentController::class, 'store']);
    Route::get('documents/{document}', [\App\Http\Controllers\Api\V1\EmployeeDocumentController::class, 'show']);
    Route::put('documents/{document}', [\App\Http\Controllers\Api\V1\EmployeeDocumentController::class, 'update']);
    Route::delete('documents/{document}', [\App\Http\Controllers\Api\V1\EmployeeDocumentController::class, 'destroy']);
    Route::get('documents/{document}/download', [\App\Http\Controllers\Api\V1\EmployeeDocumentController::class, 'download']);
    Route::get('documents/{document}/preview', [\App\Http\Controllers\Api\V1\EmployeeDocumentController::class, 'preview']);

    // Attendance & Shifts
    Route::apiResource('shifts', \App\Http\Controllers\Api\V1\ShiftController::class);
    Route::get('/attendances', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'index']);
    Route::post('/attendances', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'store']);
    Route::get('/attendances/{attendance}', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'show']);
    Route::put('/attendances/{attendance}', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'update']);
    Route::delete('/attendances/{attendance}', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'destroy']);
    Route::post('/attendance/check-in', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'checkIn']);
    Route::post('/attendance/check-out', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'checkOut']);
    Route::get('/my-attendance', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'myAttendance']);
    Route::get('/attendance/location-audit', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'locationAudit']);

    // Sites / Construction
    Route::apiResource('sites', \App\Http\Controllers\Api\V1\SiteController::class);
    Route::get('employees/{employee}/sites', [\App\Http\Controllers\Api\V1\EmployeeSiteAssignmentController::class, 'index']);
    Route::get('employees/{employee}/sites/history', [\App\Http\Controllers\Api\V1\EmployeeSiteAssignmentController::class, 'history']);
    Route::post('employees/{employee}/sites', [\App\Http\Controllers\Api\V1\EmployeeSiteAssignmentController::class, 'assign']);
    Route::post('employees/{employee}/sites/transfer', [\App\Http\Controllers\Api\V1\EmployeeSiteAssignmentController::class, 'transfer']);
    Route::delete('employees/{employee}/sites/{site}', [\App\Http\Controllers\Api\V1\EmployeeSiteAssignmentController::class, 'remove']);
    Route::get('employees/{employee}/current-site', [\App\Http\Controllers\Api\V1\EmployeeSiteAssignmentController::class, 'currentSite']);
    Route::get('sites/{site}/employees', [\App\Http\Controllers\Api\V1\EmployeeSiteAssignmentController::class, 'siteEmployees']);

    Route::apiResource('employee-sites', \App\Http\Controllers\EmployeeSiteController::class)->only(['index','store','destroy']);

    // Shifts & Attendance
    Route::apiResource('shifts', ShiftController::class);
    Route::apiResource('attendances', AttendanceController::class);
    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);

    // Daily Reports
    Route::apiResource('daily-reports', DailyReportController::class);
    Route::post('daily-reports/{daily_report}/approve', [DailyReportController::class, 'approve']);
    Route::post('daily-reports/{daily_report}/reject', [DailyReportController::class, 'reject']);
    Route::post('daily-reports/{daily_report}/rework', [DailyReportController::class, 'rework']);

    // Inventory Dashboard (MUST be before /inventory/{inventory} wildcard)
    Route::get('/inventory/dashboard', [InventoryDashboardController::class, 'index']);
    Route::post('/inventory/transaction', [InventoryController::class, 'transaction']);
    Route::get('/inventory/transactions', [InventoryController::class, 'transactions']);
    Route::get('/inventory/transfers', [\App\Http\Controllers\InventoryTransferController::class, 'index']);
    Route::post('/inventory/transfers', [\App\Http\Controllers\InventoryTransferController::class, 'store']);
    Route::put('/inventory/transfers/{transfer}/status', [\App\Http\Controllers\InventoryTransferController::class, 'updateStatus']);
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/inventory', [InventoryController::class, 'store']);
    Route::get('/inventory/{inventory}', [InventoryController::class, 'show']);
    Route::put('/inventory/{inventory}', [InventoryController::class, 'update']);
    Route::patch('/inventory/{inventory}', [InventoryController::class, 'update']);
    Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy']);

    // Inventory Categories & Products
    Route::apiResource('categories', \App\Http\Controllers\CategoryController::class);
    Route::apiResource('products', ProductController::class);

    // Inventory Locations (using sites - polymorphic)
    Route::get('/locations', [InventoryLocationController::class, 'index']);
    Route::post('/locations', [InventoryLocationController::class, 'store']);

    // Units
    Route::apiResource('units', UnitController::class);

    // Unit Conversions
    Route::apiResource('unit-conversions', UnitConversionController::class);
    Route::get('/unit-conversions/convert/{from}/{to}/{quantity}', [UnitConversionController::class, 'convert']);

    // Product Stock (named routes before wildcard {stock})
    Route::get('/stock/by-product/{product}', [ProductStockController::class, 'byProduct']);
    Route::get('/stock/by-location/{locationType}/{locationId}', [ProductStockController::class, 'byLocation']);
    Route::get('/stock', [ProductStockController::class, 'index']);
    Route::get('/stock/{stock}', [ProductStockController::class, 'show']);

    // Transaction Ledger
    Route::get('/stock/transactions', [TransactionLedgerController::class, 'index']);
    Route::post('/stock/transactions', [TransactionLedgerController::class, 'store']);
    Route::get('/stock/transactions/{transactionLedger}', [TransactionLedgerController::class, 'show']);
    Route::get('/stock/transactions-summary', [TransactionLedgerController::class, 'summary']);

    // Stock Requests
    Route::get('/stock/requests', [StockRequestController::class, 'index']);
    Route::post('/stock/requests', [StockRequestController::class, 'store']);
    Route::get('/stock/requests/{stockRequest}', [StockRequestController::class, 'show']);
    Route::post('/stock/requests/{stockRequest}/approve', [StockRequestController::class, 'approve']);
    Route::post('/stock/requests/{stockRequest}/issue', [StockRequestController::class, 'issue']);
    Route::post('/stock/requests/{stockRequest}/receive', [StockRequestController::class, 'receive']);

    // CRM & Sales
    // Purchases Management
    Route::name('purchases.')->group(function () {
        Route::apiResource('purchases/quotations', \App\Http\Controllers\PurchaseQuotationController::class);
        Route::post('purchases/quotations/{id}/convert', [\App\Http\Controllers\PurchaseQuotationController::class, 'convertToPo'])->name('quotations.convert');
        
        Route::apiResource('purchases/orders', \App\Http\Controllers\PurchaseOrderController::class);
        Route::post('purchases/orders/{id}/approve', [\App\Http\Controllers\PurchaseOrderController::class, 'approve'])->name('orders.approve');
        
        Route::apiResource('purchases/grns', \App\Http\Controllers\GoodsReceiptNoteController::class);
        Route::apiResource('purchases/returns', \App\Http\Controllers\PurchaseReturnController::class);
    });

    // Payments
    Route::apiResource('payments', \App\Http\Controllers\PaymentController::class);

    // Notes and Documents
    Route::apiResource('notes', \App\Http\Controllers\NoteController::class)->only(['store', 'destroy']);
    Route::post('documents', [\App\Http\Controllers\DocumentController::class, 'store']);

    // Sales Management
    Route::name('sales.')->group(function () {
        Route::apiResource('sales/quotations', \App\Http\Controllers\SalesQuotationController::class);
        Route::post('sales/quotations/{id}/convert', [\App\Http\Controllers\SalesQuotationController::class, 'convertToSo'])->name('quotations.convert');

        Route::apiResource('sales/orders', \App\Http\Controllers\SalesOrderController::class);
        Route::post('sales/orders/{id}/approve', [\App\Http\Controllers\SalesOrderController::class, 'approve'])->name('orders.approve');

        Route::apiResource('sales/deliveries', \App\Http\Controllers\DeliveryNoteController::class);
        Route::apiResource('sales/returns', \App\Http\Controllers\SalesReturnController::class);
    });

    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('invoices', InvoiceController::class);
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'generatePDF']);

    // HR Additions
    Route::get('/leaves', [HRController::class, 'leaves']);
    Route::post('/leaves', [HRController::class, 'requestLeave']);
    Route::get('/tasks', [HRController::class, 'tasks']);

    // HR Organization Structure
    Route::apiResource('departments', \App\Http\Controllers\Api\V1\DepartmentController::class);
    Route::apiResource('designations', \App\Http\Controllers\Api\V1\DesignationController::class);
    // Payroll Management System
    Route::apiResource('salary-structures', SalaryStructureController::class);
    Route::apiResource('payroll-periods', PayrollPeriodController::class);
    
    Route::get('/payroll', [PayrollController::class, 'index']);
    Route::get('/payroll/{payroll}', [PayrollController::class, 'show']);
    Route::post('/payroll/generate', [PayrollController::class, 'generate']);
    Route::post('/payroll/regenerate', [PayrollController::class, 'regenerate']);
    Route::post('/payroll/{payroll}/approve', [PayrollController::class, 'approve']);
    Route::post('/payroll/{payroll}/lock', [PayrollController::class, 'lock']);
    Route::post('/payroll/{payroll}/unlock', [PayrollController::class, 'unlock']);
    Route::delete('/payroll/{payroll}', [PayrollController::class, 'destroy']);

    Route::get('/payslips', [PayslipController::class, 'index']);
    Route::get('/payslips/{payslip}', [PayslipController::class, 'show']);
    Route::post('/payrolls/{payroll}/payslips/generate', [PayslipController::class, 'generate']);
    Route::get('/payslips/{payslip}/download', [PayslipController::class, 'download']);

    // Reports
    Route::get('/reports/sales', [ReportsController::class, 'salesReport']);
    Route::get('/reports/payments', [ReportsController::class, 'paymentReport']);
    Route::get('/reports/attendance', [ReportsController::class, 'attendanceReport']);
    Route::get('/reports/leaves', [ReportsController::class, 'leaveReport']);
    Route::get('/reports/employees', [ReportsController::class, 'employeeReport']);

    // Generic Admin Routes
    Route::group([], function () {
        Route::get('/admin/tables', [AdminController::class, 'getTables']);
        Route::get('/admin/tables/{table}/schema', [AdminController::class, 'getTableSchema']);
        Route::get('/admin/tables/{table}', [AdminController::class, 'getTableData']);
        Route::post('/admin/tables/{table}', [AdminController::class, 'createRecord']);
        Route::put('/admin/tables/{table}/{id}', [AdminController::class, 'updateRecord']);
        Route::delete('/admin/tables/{table}/{id}', [AdminController::class, 'deleteRecord']);
    });

    // Leave Types
    Route::apiResource('leave-types', LeaveTypeController::class);

        // Leave Balances
        Route::get('leave-balances', [LeaveBalanceController::class, 'index']);
        Route::post('employees/{employee}/leave-balances/initialize', [LeaveBalanceController::class, 'initialize']);

    // Leave Requests
    Route::apiResource('leave-requests', LeaveController::class);
    Route::get('leave-requests/request/{leave}', [LeaveController::class, 'request']);
    Route::post('leave-requests/{leave}/approve', [LeaveController::class, 'approve']);
    Route::post('leave-requests/{leave}/reject', [LeaveController::class, 'reject']);
    Route::post('leave-requests/{leave}/cancel', [LeaveController::class, 'cancel']);

    // My Leave Requests (employee self-service)
    Route::get('/my-leave-requests', [LeaveController::class, 'myLeaves']);
    Route::post('/my-leave-requests', [LeaveController::class, 'myStore']);
});

});
