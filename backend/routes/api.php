<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\DesignationController;
use App\Http\Controllers\Api\V1\ShiftController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\DailyReportController;
use App\Http\Controllers\Api\V1\LeaveTypeController;
use App\Http\Controllers\Api\V1\LeaveBalanceController;
use App\Http\Controllers\Api\V1\LeaveController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\CompanySettingController;
use App\Http\Controllers\Api\RoleConfigController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ReportsModuleController;
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
    Route::post('/login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/forgot-password', [\App\Http\Controllers\Api\V1\PasswordResetController::class, 'sendResetLinkEmail'])->middleware('throttle:login');
    Route::post('/reset-password', [\App\Http\Controllers\Api\V1\PasswordResetController::class, 'reset'])->middleware('throttle:login');
    
    Route::get('/test', [TestController::class, 'test'])->middleware('role:Admin');

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::get('/user', [\App\Http\Controllers\Api\V1\AuthController::class, 'user']);
        Route::post('/logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
        Route::put('/profile', [\App\Http\Controllers\Api\V1\ProfileController::class, 'update']);
        Route::post('/change-password', [\App\Http\Controllers\Api\V1\AuthController::class, 'changePassword']);
        Route::post('/push-subscriptions', [\App\Http\Controllers\Api\V1\PushSubscriptionController::class, 'store']);
        
        Route::get('/search', [SearchController::class, 'search']);

    // Company Settings
    Route::get('/company-settings', [CompanySettingController::class, 'show']);
    Route::post('/company-settings', [CompanySettingController::class, 'update']);

    // Dashboard
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Core HR
    // Role & Permission Management
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);

    // Role Configuration (Settings)
    Route::get('/role-config/scopes', [RoleConfigController::class, 'scopes']);
    Route::get('/role-config/templates', [RoleConfigController::class, 'templates']);
    Route::post('/role-config/roles/{role}/apply-template', [RoleConfigController::class, 'applyTemplate']);
    Route::get('/role-config/exceptions', [RoleConfigController::class, 'exceptions']);
    Route::put('/role-config/exceptions/{exception}', [RoleConfigController::class, 'updateException']);
    Route::get('/role-config/roles/{role}/audit', [RoleConfigController::class, 'auditSettings']);
    Route::put('/role-config/roles/{role}/audit', [RoleConfigController::class, 'updateAuditSettings']);
    Route::get('/role-config/roles/{role}/summary', [RoleConfigController::class, 'roleSummary']);
    Route::get('/role-config/permissions-by-module', [RoleConfigController::class, 'permissionsByModule']);
    Route::get('/role-config/dependencies', [RoleConfigController::class, 'dependencies']);

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
    Route::apiResource('buildings', \App\Http\Controllers\Api\V1\BuildingController::class);
    Route::get('employees/{employee}/sites', [\App\Http\Controllers\Api\V1\EmployeeSiteAssignmentController::class, 'index']);
    Route::get('employees/{employee}/sites/history', [\App\Http\Controllers\Api\V1\EmployeeSiteAssignmentController::class, 'history']);
    Route::post('employees/{employee}/sites', [\App\Http\Controllers\Api\V1\EmployeeSiteAssignmentController::class, 'assign']);
    Route::post('employees/{employee}/sites/transfer', [\App\Http\Controllers\Api\V1\EmployeeSiteAssignmentController::class, 'transfer']);
    Route::delete('employees/{employee}/sites/{site}', [\App\Http\Controllers\Api\V1\EmployeeSiteAssignmentController::class, 'remove']);
    Route::get('employees/{employee}/current-site', [\App\Http\Controllers\Api\V1\EmployeeSiteAssignmentController::class, 'currentSite']);
    Route::get('sites/{site}/employees', [\App\Http\Controllers\Api\V1\EmployeeSiteAssignmentController::class, 'siteEmployees']);

    Route::apiResource('employee-sites', \App\Http\Controllers\EmployeeSiteController::class)->only(['index','store','destroy']);

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
    Route::apiResource('categories', \App\Http\Controllers\Api\CategoryController::class);
    Route::apiResource('products', ProductController::class);

    // Inventory Locations (using sites - polymorphic)
    Route::get('/locations', [InventoryLocationController::class, 'index']);
    Route::post('/locations', [InventoryLocationController::class, 'store']);

    // Units
    Route::apiResource('units', UnitController::class);

    // Unit Conversions
    Route::apiResource('unit-conversions', UnitConversionController::class);
    Route::get('/unit-conversions/convert/{from}/{to}/{quantity}', [UnitConversionController::class, 'convert']);

    // Transaction Ledger (must be before wildcard {stock})
    Route::get('/stock/transactions', [TransactionLedgerController::class, 'index']);
    Route::post('/stock/transactions', [TransactionLedgerController::class, 'store']);
    Route::get('/stock/transactions/{transactionLedger}', [TransactionLedgerController::class, 'show']);
    Route::get('/stock/transactions-summary', [TransactionLedgerController::class, 'summary']);

    // Stock Requests (must be before wildcard {stock})
    Route::get('/stock/requests', [StockRequestController::class, 'index']);
    Route::post('/stock/requests', [StockRequestController::class, 'store']);
    Route::get('/stock/requests/{stockRequest}', [StockRequestController::class, 'show']);
    Route::post('/stock/requests/{stockRequest}/approve', [StockRequestController::class, 'approve']);
    Route::post('/stock/requests/{stockRequest}/issue', [StockRequestController::class, 'issue']);
    Route::post('/stock/requests/{stockRequest}/receive', [StockRequestController::class, 'receive']);

    // Product Stock (wildcard {stock} — keep at end)
    Route::get('/stock/by-product/{product}', [ProductStockController::class, 'byProduct']);
    Route::get('/stock/by-location/{locationType}/{locationId}', [ProductStockController::class, 'byLocation']);
    Route::get('/stock', [ProductStockController::class, 'index']);
    Route::get('/stock/{stock}', [ProductStockController::class, 'show']);

    // CRM & Sales
    // Purchases Management
    Route::name('purchases.')->group(function () {
        Route::apiResource('purchases/quotations', \App\Http\Controllers\PurchaseQuotationController::class);
        Route::post('purchases/quotations/{id}/convert', [\App\Http\Controllers\PurchaseQuotationController::class, 'convertToPo'])->name('quotations.convert');
        
        Route::apiResource('purchases/orders', \App\Http\Controllers\PurchaseOrderController::class);
        Route::post('purchases/orders/{id}/approve', [\App\Http\Controllers\PurchaseOrderController::class, 'approve'])->name('orders.approve');
        Route::post('purchases/orders/{id}/reject', [\App\Http\Controllers\PurchaseOrderController::class, 'reject'])->name('orders.reject');
        Route::post('purchases/orders/{id}/confirm-receipt', [\App\Http\Controllers\PurchaseOrderController::class, 'confirmReceipt'])->name('orders.confirm-receipt');
        
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
        Route::post('sales/orders/{id}/reject', [\App\Http\Controllers\SalesOrderController::class, 'reject'])->name('orders.reject');
        Route::post('sales/orders/{id}/confirm-delivery', [\App\Http\Controllers\SalesOrderController::class, 'confirmDelivery'])->name('orders.confirm-delivery');

        Route::apiResource('sales/deliveries', \App\Http\Controllers\DeliveryNoteController::class);
        Route::apiResource('sales/returns', \App\Http\Controllers\SalesReturnController::class);
    });

    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('invoices', \App\Http\Controllers\Api\InvoiceController::class);
    Route::get('/invoices/{invoice}/pdf', [\App\Http\Controllers\Api\InvoiceController::class, 'generatePDF']);
    Route::get('/invoices/{invoice}/preview', [\App\Http\Controllers\Api\InvoicePreviewController::class, 'preview']);
    Route::get('/invoices/{invoice}/validate', [\App\Http\Controllers\Api\InvoicePreviewController::class, 'validateInvoice']);
    Route::post('/invoices/{invoice}/email', [\App\Http\Controllers\Api\InvoicePreviewController::class, 'email'])->middleware('throttle:sensitive');

    // HR Organization Structure
    Route::apiResource('departments', \App\Http\Controllers\Api\V1\DepartmentController::class);
    Route::apiResource('designations', \App\Http\Controllers\Api\V1\DesignationController::class);
    // Payroll Management System
    Route::apiResource('salary-structures', SalaryStructureController::class);
    Route::apiResource('payroll-periods', PayrollPeriodController::class);
    
    Route::get('/payroll', [PayrollController::class, 'index']);
    Route::get('/my-payroll', [PayrollController::class, 'myPayroll']);
    Route::get('/payroll/{payroll}', [PayrollController::class, 'show']);
    Route::post('/payroll/generate', [PayrollController::class, 'generate'])->middleware('throttle:sensitive');
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

    // Reports Module (catalog, schedules, generations)
    Route::get('/reports-module/categories', [ReportsModuleController::class, 'categories']);
    Route::get('/reports-module/stats', [ReportsModuleController::class, 'stats']);
    Route::get('/reports-module/reports', [ReportsModuleController::class, 'index']);
    Route::post('/reports-module/reports', [ReportsModuleController::class, 'store']);
    Route::get('/reports-module/reports/{report}', [ReportsModuleController::class, 'show']);
    Route::put('/reports-module/reports/{report}', [ReportsModuleController::class, 'update']);
    Route::delete('/reports-module/reports/{report}', [ReportsModuleController::class, 'destroy']);
    Route::post('/reports-module/reports/{report}/generate', [ReportsModuleController::class, 'generate']);
    Route::get('/reports-module/generations', [ReportsModuleController::class, 'generations']);
    Route::get('/reports-module/generations/{generation}/download', [ReportsModuleController::class, 'downloadGeneration']);
    Route::get('/reports-module/schedules', [ReportsModuleController::class, 'schedules']);
    Route::post('/reports-module/schedules', [ReportsModuleController::class, 'storeSchedule']);
    Route::put('/reports-module/schedules/{schedule}', [ReportsModuleController::class, 'updateSchedule']);
    Route::delete('/reports-module/schedules/{schedule}', [ReportsModuleController::class, 'destroySchedule']);
    Route::post('/reports-module/schedules/{schedule}/toggle', [ReportsModuleController::class, 'toggleSchedule']);

    // Generic Admin Routes (removed - SuperAdmin no longer supported)

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

    // Notifications
    Route::get('/notifications/unread-count', [\App\Http\Controllers\Api\V1\NotificationController::class, 'unreadCount']);
    Route::put('/notifications/read-all', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markAllRead']);
    Route::get('/notifications', [\App\Http\Controllers\Api\V1\NotificationController::class, 'index']);
    Route::post('/notifications/send', [\App\Http\Controllers\Api\V1\NotificationController::class, 'send'])->middleware('throttle:notifications');
    Route::put('/notifications/{notification}/read', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markRead']);
    Route::delete('/notifications/{notification}', [\App\Http\Controllers\Api\V1\NotificationController::class, 'destroy']);

    // Chat
    Route::get('/chat/channels', [\App\Http\Controllers\Api\V1\ChatController::class, 'channels']);
    Route::post('/chat/channels', [\App\Http\Controllers\Api\V1\ChatController::class, 'storeChannel']);
    Route::post('/chat/channels/{channel}/members', [\App\Http\Controllers\Api\V1\ChatController::class, 'addMembers']);
    Route::delete('/chat/channels/{channel}/members/{userId}', [\App\Http\Controllers\Api\V1\ChatController::class, 'removeMember']);
    Route::get('/chat/channels/{channel}/messages', [\App\Http\Controllers\Api\V1\ChatController::class, 'messages']);
    Route::post('/chat/channels/{channel}/messages', [\App\Http\Controllers\Api\V1\ChatController::class, 'sendMessage']);
    Route::post('/chat/channels/{channel}/read', [\App\Http\Controllers\Api\V1\ChatController::class, 'markRead']);
    Route::post('/chat/channels/{channel}/typing', [\App\Http\Controllers\Api\V1\ChatController::class, 'typing']);
    Route::get('/chat/channels/{channel}/members', [\App\Http\Controllers\Api\V1\ChatController::class, 'members']);

    // My Leave Requests (employee self-service)
    Route::get('/my-leave-requests', [LeaveController::class, 'myLeaves']);
    Route::post('/my-leave-requests', [LeaveController::class, 'myStore']);

    // ==================== Sales & Building Survey Module ====================

    // Dashboard Stats
    Route::get('/sales/dashboard', [\App\Http\Controllers\Api\V1\BuildingDetailController::class, 'dashboardStats']);

    // Building Statuses
    Route::get('/building-statuses', [\App\Http\Controllers\Api\V1\BuildingDetailController::class, 'statuses']);
    Route::post('/buildings/{building}/statuses', [\App\Http\Controllers\Api\V1\BuildingDetailController::class, 'assignStatus']);
    Route::delete('/buildings/{building}/statuses/{buildingStatus}', [\App\Http\Controllers\Api\V1\BuildingDetailController::class, 'removeStatus']);

    // Building Wings
    Route::get('/buildings/{building}/wings', [\App\Http\Controllers\Api\V1\BuildingDetailController::class, 'wings']);
    Route::post('/buildings/{building}/wings', [\App\Http\Controllers\Api\V1\BuildingDetailController::class, 'storeWing']);
    Route::put('/buildings/{building}/wings/{wing}', [\App\Http\Controllers\Api\V1\BuildingDetailController::class, 'updateWing']);
    Route::delete('/buildings/{building}/wings/{wing}', [\App\Http\Controllers\Api\V1\BuildingDetailController::class, 'destroyWing']);

    // Fire Systems
    Route::get('/buildings/{building}/fire-systems', [\App\Http\Controllers\Api\V1\BuildingDetailController::class, 'fireSystems']);
    Route::post('/buildings/{building}/fire-systems', [\App\Http\Controllers\Api\V1\BuildingDetailController::class, 'storeFireSystem']);
    Route::put('/buildings/{building}/fire-systems/{fireSystem}', [\App\Http\Controllers\Api\V1\BuildingDetailController::class, 'updateFireSystem']);
    Route::delete('/buildings/{building}/fire-systems/{fireSystem}', [\App\Http\Controllers\Api\V1\BuildingDetailController::class, 'destroyFireSystem']);

    // Building Contacts
    Route::get('/buildings/{building}/contacts', [\App\Http\Controllers\Api\V1\BuildingDetailController::class, 'contacts']);
    Route::post('/buildings/{building}/contacts', [\App\Http\Controllers\Api\V1\BuildingDetailController::class, 'storeContact']);
    Route::put('/buildings/{building}/contacts/{contact}', [\App\Http\Controllers\Api\V1\BuildingDetailController::class, 'updateContact']);
    Route::delete('/buildings/{building}/contacts/{contact}', [\App\Http\Controllers\Api\V1\BuildingDetailController::class, 'destroyContact']);

    // Site Visits
    Route::get('/site-visits', [\App\Http\Controllers\Api\V1\SiteVisitController::class, 'index']);
    Route::post('/site-visits', [\App\Http\Controllers\Api\V1\SiteVisitController::class, 'store']);
    Route::get('/site-visits/{siteVisit}', [\App\Http\Controllers\Api\V1\SiteVisitController::class, 'show']);
    Route::put('/site-visits/{siteVisit}', [\App\Http\Controllers\Api\V1\SiteVisitController::class, 'update']);
    Route::delete('/site-visits/{siteVisit}', [\App\Http\Controllers\Api\V1\SiteVisitController::class, 'destroy']);
    Route::post('/site-visits/{siteVisit}/photos', [\App\Http\Controllers\Api\V1\SiteVisitController::class, 'uploadPhotos']);
    Route::delete('/site-visits/{siteVisit}/photos/{photo}', [\App\Http\Controllers\Api\V1\SiteVisitController::class, 'deletePhoto']);
    Route::post('/site-visits/{siteVisit}/voice-notes', [\App\Http\Controllers\Api\V1\SiteVisitController::class, 'uploadVoiceNotes']);
    Route::delete('/site-visits/{siteVisit}/voice-notes/{voiceNote}', [\App\Http\Controllers\Api\V1\SiteVisitController::class, 'deleteVoiceNote']);

    // Opportunities
    Route::get('/opportunities', [\App\Http\Controllers\Api\V1\OpportunityController::class, 'index']);
    Route::post('/opportunities', [\App\Http\Controllers\Api\V1\OpportunityController::class, 'store']);
    Route::get('/opportunities/{opportunity}', [\App\Http\Controllers\Api\V1\OpportunityController::class, 'show']);
    Route::put('/opportunities/{opportunity}', [\App\Http\Controllers\Api\V1\OpportunityController::class, 'update']);
    Route::delete('/opportunities/{opportunity}', [\App\Http\Controllers\Api\V1\OpportunityController::class, 'destroy']);

    // Follow-ups
    Route::get('/follow-ups', [\App\Http\Controllers\Api\V1\FollowUpController::class, 'index']);
    Route::post('/follow-ups', [\App\Http\Controllers\Api\V1\FollowUpController::class, 'store']);
    Route::get('/follow-ups/{followUp}', [\App\Http\Controllers\Api\V1\FollowUpController::class, 'show']);
    Route::put('/follow-ups/{followUp}', [\App\Http\Controllers\Api\V1\FollowUpController::class, 'update']);
    Route::delete('/follow-ups/{followUp}', [\App\Http\Controllers\Api\V1\FollowUpController::class, 'destroy']);
    Route::post('/follow-ups/{followUp}/complete', [\App\Http\Controllers\Api\V1\FollowUpController::class, 'markComplete']);
    Route::get('/my-follow-ups', [\App\Http\Controllers\Api\V1\FollowUpController::class, 'myFollowUps']);

    // Sales Documents
    Route::get('/sales-documents', [\App\Http\Controllers\Api\V1\SalesDocumentController::class, 'index']);
    Route::post('/sales-documents', [\App\Http\Controllers\Api\V1\SalesDocumentController::class, 'store']);
    Route::get('/sales-documents/{salesDocument}', [\App\Http\Controllers\Api\V1\SalesDocumentController::class, 'show']);
    Route::get('/sales-documents/{salesDocument}/download', [\App\Http\Controllers\Api\V1\SalesDocumentController::class, 'download']);
    Route::delete('/sales-documents/{salesDocument}', [\App\Http\Controllers\Api\V1\SalesDocumentController::class, 'destroy']);

    // Activity Logs
    Route::get('/activity-logs', [\App\Http\Controllers\Api\V1\ActivityLogController::class, 'index']);

    // Reports
    Route::get('/reports/building/{building}', [\App\Http\Controllers\Api\V1\ReportController::class, 'buildingReport']);
    Route::get('/reports/opportunities', [\App\Http\Controllers\Api\V1\ReportController::class, 'opportunityReport']);

});

});
