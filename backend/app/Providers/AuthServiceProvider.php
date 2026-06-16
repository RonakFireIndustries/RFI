<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\Employee;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Site;
use App\Models\DailyReport;
use App\Models\EmployeeSite;
use App\Models\Document;
use App\Models\Note;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Category;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Payroll;

use App\Policies\EmployeePolicy;
use App\Policies\ProductPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\InventoryPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\SalesOrderPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\SitePolicy;
use App\Policies\DailyReportPolicy;
use App\Policies\EmployeeSitePolicy;
use App\Policies\DocumentPolicy;
use App\Policies\NotePolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\DesignationPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\LeavePolicy;
use App\Policies\PayrollPolicy;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Global bypass for Super Admin
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Super Admin')) {
                return true;
            }
        });

        $this->policies = [
            Employee::class => EmployeePolicy::class,
            Product::class => ProductPolicy::class,
            Customer::class => CustomerPolicy::class,
            Supplier::class => SupplierPolicy::class,
            Inventory::class => InventoryPolicy::class,
            PurchaseOrder::class => PurchaseOrderPolicy::class,
            SalesOrder::class => SalesOrderPolicy::class,
            Invoice::class => InvoicePolicy::class,
            Payment::class => PaymentPolicy::class,
            Site::class => SitePolicy::class,
            DailyReport::class => DailyReportPolicy::class,
            EmployeeSite::class => EmployeeSitePolicy::class,
            Document::class => DocumentPolicy::class,
            Note::class => NotePolicy::class,
            Department::class => DepartmentPolicy::class,
            Designation::class => DesignationPolicy::class,
            Category::class => CategoryPolicy::class,
            Attendance::class => AttendancePolicy::class,
            Leave::class => LeavePolicy::class,
            Payroll::class => PayrollPolicy::class,
        ];

        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
