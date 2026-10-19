<?php

/*
|--------------------------------------------------------------------------
| Access Control — Single Source of Truth
|--------------------------------------------------------------------------
|
| Defines every module an employee can be granted access to, along with the
| actions available for that module. This file drives:
|
|   1. The permission seeder (canonical "module.action" permission rows)
|   2. The Access Control admin UI (module + action list)
|   3. Enforcement (permission middleware + controller authorize() names)
|
| Canonical permission name  =  {module}.{action}
|
| Action keys map to user-facing labels in the frontend:
|   view, create, update, delete, export, approve, ...
|
*/

return [

    // Human-readable labels for action keys used by the Access Control UI.
    'actions' => [
        'view'    => 'View',
        'create'  => 'Add',
        'update'  => 'Edit',
        'delete'  => 'Remove',
        'manage'  => 'Manage',
        'approve' => 'Approve',
        'reject'  => 'Reject',
        'submit'  => 'Submit',
        'assign'  => 'Assign',
        'transfer' => 'Transfer',
        'convert' => 'Convert',
        'issue'   => 'Issue',
        'receive' => 'Receive',
        'export'  => 'Export',
        'schedule' => 'Schedule',
        'email'   => 'Email',
        'download' => 'Download',
        'checkin' => 'Check-in',
        'checkout' => 'Check-out',
        // Dashboard-type actions. Combined with the 'dashboard' module these
        // yield dashboard.<type> permissions used to gate each role dashboard.
        'admin'      => 'Admin Dashboard',
        'executive'  => 'Executive Dashboard',
        'hr'         => 'HR Dashboard',
        'finance'    => 'Finance Dashboard',
        'inventory'  => 'Inventory Dashboard',
        'production' => 'Production Dashboard',
        'sales'      => 'Sales Dashboard',
        'it'         => 'IT Dashboard',
        'design'     => 'Design Dashboard',
        'employee'   => 'My Dashboard',
    ],

    // Category-driven grouping mirrors the sidebar so the admin can navigate
    // the same way. Not used for enforcement — purely organizational.
    'categories' => [
        'Overview' => [
            'dashboard' => [
                'label' => 'Dashboard',
                'actions' => ['view', 'admin', 'executive', 'hr', 'finance', 'inventory', 'production', 'sales', 'it', 'design', 'employee'],
            ],
        ],

        'Human Resources' => [
            'employees' => [
                'label' => 'Employees',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'attendance' => [
                'label' => 'Attendance',
                'actions' => ['view', 'create', 'update', 'delete', 'checkin', 'checkout'],
            ],
            'shifts' => [
                'label' => 'Shifts',
                'actions' => ['view', 'create', 'update', 'delete', 'assign', 'transfer'],
            ],
            'daily-reports' => [
                'label' => 'Daily Reports',
                'actions' => ['view', 'create', 'update', 'delete', 'submit', 'approve', 'reject'],
            ],
            'leaves' => [
                'label' => 'Leaves',
                'actions' => ['view', 'create', 'update', 'delete', 'approve', 'reject'],
            ],
            'leave-types' => [
                'label' => 'Leave Types',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
        ],

        'Payroll' => [
            'payroll' => [
                'label' => 'Payroll',
                'actions' => ['view', 'manage', 'update'],
            ],
            'salary-structures' => [
                'label' => 'Salary Structures',
                'actions' => ['view', 'manage', 'update'],
            ],
            'payslips' => [
                'label' => 'Payslips',
                'actions' => ['view', 'manage', 'update'],
            ],
            'payroll-period' => [
                'label' => 'Payroll Period',
                'actions' => ['view', 'create', 'update'],
            ],
            'process-payroll' => [
                'label' => 'Process Payroll',
                'actions' => ['view', 'create', 'update'],
            ],
        ],

        'Finance' => [
            'cash-vouchers' => [
                'label' => 'Cash Vouchers',
                'actions' => ['view', 'create', 'update', 'delete', 'approve', 'reject'],
            ],
            // Tally-style double-entry accounting.
            'account-groups' => [
                'label' => 'Account Groups',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'ledgers' => [
                'label' => 'Ledgers',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'financial-years' => [
                'label' => 'Financial Years',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            // Tally-style vouchers (slice 2).
            'contra' => [
                'label' => 'Contra Vouchers',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'payment-vouchers' => [
                'label' => 'Payment Vouchers',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'receipt-vouchers' => [
                'label' => 'Receipt Vouchers',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'journal' => [
                'label' => 'Journal Vouchers',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'purchase-vouchers' => [
                'label' => 'Purchase Vouchers',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'sales-vouchers' => [
                'label' => 'Sales Vouchers',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'credit-notes' => [
                'label' => 'Credit Notes',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'debit-notes' => [
                'label' => 'Debit Notes',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'bank-reconciliation' => [
                'label' => 'Bank Reconciliation',
                'actions' => ['view', 'update'],
            ],
            'finance-reports' => [
                'label' => 'Finance Reports',
                'actions' => ['view'],
            ],
        ],

        'Organization' => [
            'departments' => [
                'label' => 'Departments',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'designations' => [
                'label' => 'Designations',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'sites' => [
                'label' => 'Sites',
                'actions' => ['view', 'create', 'update', 'delete', 'assign', 'transfer'],
            ],
        ],

        'Inventory' => [
            'inventory' => [
                'label' => 'Inventory',
                'actions' => ['view', 'manage'],
            ],
            'products' => [
                'label' => 'Products',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'categories' => [
                'label' => 'Categories',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'inventory-locations' => [
                'label' => 'Locations',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'units' => [
                'label' => 'Units',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'unit-conversions' => [
                'label' => 'Unit Conversions',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'stock' => [
                'label' => 'Stock',
                'actions' => ['view', 'create', 'update'],
            ],
            'stock-transactions' => [
                'label' => 'Stock Transactions',
                'actions' => ['view', 'create', 'update'],
            ],
            'stock-requests' => [
                'label' => 'Stock Requests',
                'actions' => ['view', 'create', 'update', 'approve', 'issue', 'receive'],
            ],
            'stock-transfers' => [
                'label' => 'Stock Transfers',
                'actions' => ['view', 'create', 'update', 'approve'],
            ],
        ],

        'Sales & Purchases' => [
            'customers' => [
                'label' => 'Customers',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'suppliers' => [
                'label' => 'Suppliers',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'purchase-orders' => [
                'label' => 'Purchase Orders',
                'actions' => ['view', 'create', 'update', 'delete', 'approve', 'reject'],
            ],
            'purchase-quotations' => [
                'label' => 'Purchase Quotations',
                'actions' => ['view', 'create', 'update', 'delete', 'convert'],
            ],
            'purchase-returns' => [
                'label' => 'Purchase Returns',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'delivery-notes' => [
                'label' => 'Delivery Notes',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'sales-orders' => [
                'label' => 'Sales Orders',
                'actions' => ['view', 'create', 'update', 'delete', 'approve', 'reject'],
            ],
            'sales-quotations' => [
                'label' => 'Sales Quotations',
                'actions' => ['view', 'create', 'update', 'delete', 'convert'],
            ],
            'sales-returns' => [
                'label' => 'Sales Returns',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'invoices' => [
                'label' => 'Invoices',
                'actions' => ['view', 'create', 'update', 'delete', 'email', 'download'],
            ],
            'payments' => [
                'label' => 'Payments',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'goods-receipt-notes' => [
                'label' => 'Goods Receipt Notes',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
        ],

        'Sales & Survey' => [
            'buildings' => [
                'label' => 'Buildings',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'extinguishers' => [
                'label' => 'Fire Extinguishers',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'quotations' => [
                'label' => 'Quotations / BOQ',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            // The product master that every BOQ line is picked from. Granted
            // from the BOQ section so a BOQ-only user can fill the product
            // picker, but deliberately a SEPARATE, read-only permission: the
            // sidebar's Inventory › Products entry stays gated on
            // `products.view`, so this grant never reveals that page.
            // ProductController accepts either permission for reads.
            'boq-products' => [
                'label' => 'Products',
                'actions' => ['view'],
            ],
            // The same grant as Organization › Sites, also listed in the BOQ
            // section so a BOQ user can be given the site list from here. Keep
            // the action list IDENTICAL to the Organization entry: Access
            // flattens modules by key, so the two definitions overwrite one
            // another (and mismatch would silently drop actions).
            'sites' => [
                'label' => 'Sites',
                'actions' => ['view', 'create', 'update', 'delete', 'assign', 'transfer'],
            ],
            'site-visits' => [
                'label' => 'Site Visits',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'opportunities' => [
                'label' => 'Opportunities',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'follow-ups' => [
                'label' => 'Follow-ups',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'sales-documents' => [
                'label' => 'Sales Documents',
                'actions' => ['view', 'create', 'delete', 'download'],
            ],
        ],

        'Support' => [
            'issue-reports' => [
                'label' => 'Issue Reports',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
        ],

        'System' => [
            'reports' => [
                'label' => 'Reports',
                'actions' => ['view', 'create', 'update', 'delete', 'export', 'schedule'],
            ],
            'role-config' => [
                'label' => 'Roles & Permissions',
                'actions' => ['manage'],
            ],
            'access-control' => [
                'label' => 'Access Control',
                'actions' => ['manage'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy → canonical alias map
    |--------------------------------------------------------------------------
    |
    | During migration, existing grants stored under legacy permission names
    | (e.g. "view_suppliers") are copied to the canonical "{module}.{action}"
    | names defined above. This map tells the seeder which module/action each
    | legacy permission belongs to so it can replicate the grant.
    |
    */
    'legacy_aliases' => [
        'view_employees'      => 'employees.view',
        'create_employees'    => 'employees.create',
        'update_employees'    => 'employees.update',
        'delete_employees'    => 'employees.delete',
        'view_products'       => 'products.view',
        'create_products'     => 'products.create',
        'update_products'     => 'products.update',
        'delete_products'     => 'products.delete',
        'view_customers'      => 'customers.view',
        'create_customers'    => 'customers.create',
        'update_customers'    => 'customers.update',
        'delete_customers'    => 'customers.delete',
        'view_suppliers'      => 'suppliers.view',
        'create_suppliers'    => 'suppliers.create',
        'update_suppliers'    => 'suppliers.update',
        'delete_suppliers'    => 'suppliers.delete',
        'view_sites'          => 'sites.view',
        'create_sites'        => 'sites.create',
        'update_sites'        => 'sites.update',
        'delete_sites'        => 'sites.delete',
        'view_categories'     => 'categories.view',
        'create_categories'   => 'categories.create',
        'update_categories'   => 'categories.update',
        'delete_categories'   => 'categories.delete',
        'view_attendance'     => 'attendance.view',
        'create_attendance'   => 'attendance.create',
        'update_attendance'   => 'attendance.update',
        'delete_attendance'   => 'attendance.delete',
        'view_invoices'       => 'invoices.view',
        'create_invoices'     => 'invoices.create',
        'update_invoices'     => 'invoices.update',
        'delete_invoices'     => 'invoices.delete',
        'view_payments'       => 'payments.view',
        'create_payments'     => 'payments.create',
        'view_payroll'        => 'payroll.view',
        'manage_payroll'      => 'payroll.manage',
        'view_leaves'         => 'leaves.view',
        'create_leaves'       => 'leaves.create',
        'view_sales_orders'   => 'sales-orders.view',
        'create_sales_orders' => 'sales-orders.create',
        'view_purchase_orders' => 'purchase-orders.view',
        'create_purchase_orders' => 'purchase-orders.create',
        'manage_roles'        => 'role-config.manage',
        'manage_permissions'  => 'role-config.manage',
        'manage_users'        => 'access-control.manage',
        'view_inventory'      => 'inventory.view',
        'manage_inventory'    => 'inventory.manage',
        // site/department/designation/shift/employee dot-notation already canonical
        'site.view'           => 'sites.view',
        'site.create'         => 'sites.create',
        'site.edit'           => 'sites.update',
        'site.delete'         => 'sites.delete',
        'site.assign'         => 'sites.assign',
        'site.transfer'       => 'sites.transfer',
        'department.view'     => 'departments.view',
        'department.create'   => 'departments.create',
        'department.edit'     => 'departments.update',
        'department.delete'   => 'departments.delete',
        'designation.view'    => 'designations.view',
        'designation.create'  => 'designations.create',
        'designation.edit'    => 'designations.update',
        'designation.delete'  => 'designations.delete',
        'employee.view'       => 'employees.view',
        'employee.create'     => 'employees.create',
        'employee.edit'       => 'employees.update',
        'employee.delete'     => 'employees.delete',
        'shift.view'          => 'shifts.view',
        'shift.create'        => 'shifts.create',
        'shift.edit'          => 'shifts.update',
        'shift.delete'        => 'shifts.delete',
        'shifts.assign'       => 'shifts.assign',
        'shifts.transfer'     => 'shifts.transfer',
        'attendance.view'     => 'attendance.view',
        'attendance.create'   => 'attendance.create',
        'attendance.edit'     => 'attendance.update',
        'attendance.delete'   => 'attendance.delete',
        'inventory.stock.view'     => 'stock.view',
        'inventory.stock.create'   => 'stock.create',
        'inventory.dashboard.view' => 'inventory.view',
        'inventory.locations.view' => 'inventory-locations.view',
        'inventory.locations.create' => 'inventory-locations.create',
        'inventory.locations.edit'   => 'inventory-locations.update',
        'inventory.locations.delete' => 'inventory-locations.delete',
        'inventory.units.view'    => 'units.view',
        'inventory.units.create'  => 'units.create',
        'inventory.units.edit'    => 'units.update',
        'inventory.units.delete'  => 'units.delete',
        'inventory.conversions.view'  => 'unit-conversions.view',
        'inventory.conversions.create' => 'unit-conversions.create',
        'inventory.conversions.edit'   => 'unit-conversions.update',
        'inventory.conversions.delete' => 'unit-conversions.delete',
        'inventory.transactions.view'  => 'stock-transactions.view',
        'inventory.transactions.create' => 'stock-transactions.create',
        'inventory.requests.view'   => 'stock-requests.view',
        'inventory.requests.create' => 'stock-requests.create',
        'inventory.requests.approve' => 'stock-requests.approve',
        'inventory.transfers.view'   => 'stock-transfers.view',
        'inventory.transfers.create' => 'stock-transfers.create',
        'inventory.transfers.approve' => 'stock-transfers.approve',
        'building.view'   => 'buildings.view',
        'building.create' => 'buildings.create',
        'building.edit'   => 'buildings.update',
        'building.delete' => 'buildings.delete',
        'document.view'   => 'sales-documents.view',
        'document.create' => 'sales-documents.create',
        'document.edit'   => 'sales-documents.update',
        'document.delete' => 'sales-documents.delete',
        'document.download' => 'sales-documents.download',
        'leave.view'      => 'leaves.view',
        'leave.create'    => 'leaves.create',
        'leave.edit'      => 'leaves.update',
        'leave.delete'    => 'leaves.delete',
        'leave.approve'   => 'leaves.approve',
        'leave.reject'    => 'leaves.reject',
        'leave-type.view'   => 'leave-types.view',
        'leave-type.create' => 'leave-types.create',
        'leave-type.edit'   => 'leave-types.update',
        'leave-type.delete' => 'leave-types.delete',
        'daily-report.view'   => 'daily-reports.view',
        'daily-report.create' => 'daily-reports.create',
        'daily-report.edit'   => 'daily-reports.update',
        'daily-report.delete' => 'daily-reports.delete',
        'daily-report.approve' => 'daily-reports.approve',
        'daily-report.reject'  => 'daily-reports.reject',
        'view reports'     => 'reports.view',
        'create reports'   => 'reports.create',
        'update reports'   => 'reports.update',
        'delete reports'   => 'reports.delete',
        'export reports'   => 'reports.export',
        'schedule reports' => 'reports.schedule',
        'view dashboard'   => 'dashboard.view',
        'manage suppliers' => 'suppliers.update',
        'manage employees' => 'employees.update',
        'manage inventory' => 'inventory.manage',
        'manage settings'  => 'access-control.manage',
        'manage sales'     => 'sales-orders.manage',
        'manage purchases' => 'purchase-orders.manage',
        'manage transfers' => 'stock-transfers.manage',
        'create sales'     => 'sales-orders.create',
        'view warehouses'  => 'inventory.view',
    ],

    /*
    |--------------------------------------------------------------------------
    | Baseline permissions for every (non-super-admin) employee
    |--------------------------------------------------------------------------
    |
    | These core permissions are always granted to an employee directly,
    | regardless of what they get via the Access Control panel. They replace
    | what the old per-designation ROLE used to grant automatically, now that
    | role-based access has been removed in favour of the Access Control panel.
    |
    */
    'baseline' => [
        'dashboard.view',
        'dashboard.employee',
        'attendance.checkin',
        'attendance.checkout',
        'daily-reports.view',
        'daily-reports.create',
        'daily-reports.submit',
        // Petty-cash reimbursement is self-service: every employee may raise a
        // voucher for themselves and see their own claims (see CashVoucherController).
        'cash-vouchers.view',
        'cash-vouchers.create',
        'leaves.view',
        'leaves.create',
        'leaves.update',
        'issue-reports.view',
        'issue-reports.create',                
    ],

    /*
    |--------------------------------------------------------------------------
    | Retired baseline permissions
    |--------------------------------------------------------------------------
    |
    | Permissions that USED to be auto-granted via baseline but should now only
    | be granted explicitly through the Access Control panel. Running
    | `php artisan employees:sync-roles` revokes these from non-super-admin
    | users (unless the admin re-grants them via Access Control once "assigned").
    |
    | Rationale: the management Attendance dashboard ("Attendance" tab) must not
    | appear for basic employees. Employees check in/out on the public
    | "My Attendance" page instead. Show the tab only once attendance.view is
    | explicitly assigned.
    |
    | `payroll.view` is retired for the same reason, but its leak was worse.
    | It is the MANAGEMENT permission: PayrollController::index() and
    | PayslipController::index() use it to list every employee's salary with no
    | self-scope. The `Employee` and `Designer` roles were seeded with the
    | legacy name `view_payroll` (intent: "see my own pay"), and
    | CanonicalPermissionsSeeder copies legacy grants onto canonical rows -
    | so every plain  account ended up able to read everyone's pay AND open the
    | Payroll Dashboard sidebar entry.
    |
    | Self-service is untouched, because none of these bypass the permission:
    |   GET /my-payroll            - not gated
    |   GET /payroll/{id}          - owner passes without payroll.view
    |   GET /payslips/{id}         - owner passes without payroll.view
    |   GET /payslips/{id}/download - owner passes without payroll.view
    |
    | HR / Accounts / Administration / Executive keep payroll.view through
    | `identity_grants`, which is what the ordering fix in
    | SyncEmployeeRoles guarantees (an identity grant outranks a retire).
    |
    */
    'retired_baseline' => [
        'attendance.view',
        'payroll.view',
    ],

    /*
    |--------------------------------------------------------------------------
    | Identity grants — which department / designation gets what
    |--------------------------------------------------------------------------
    |
    | The "who gets extra access by default" file. Edit this section only; the
    | employees:sync-roles command reads it and grants the listed permissions
    | to every matching employee as a DIRECT grant (Access Control stays the
    | source of truth and can always override or revoke).
    |
    | Matching is case-insensitive and ignores surrounding spaces. An employee
    | matches a group when EITHER their designation OR their department is
    | listed - so listing "IT" under departments covers everyone in the IT
    | department regardless of job title.
    |
    | Names must match EXACTLY (no partial/substring matching): "Fitter"
    | contains the letters "it", so substring matching would silently promote
    | every Fitter to IT.
    |
    | Granting here is additive - it never removes anything. To actively strip
    | a permission from everyone on every run, put it in `retired_baseline`.
    |
    | Run after editing:
    |     php artisan db:seed --class=CanonicalPermissionsSeeder   (new permission names)
    |     php artisan employees:sync-roles                        (apply the grants)
    |
    */
    'identity_grants' => [

        /*
        | ------------------------------------------------------------------
        | Principles
        | ------------------------------------------------------------------
        |  - `baseline` above is already granted to everyone; groups below
        |    only ADD department-specific operational access on top of it.
        |  - Matching is OR across designations and departments, so listing a
        |    department covers everyone in it regardless of job title.
        |  - Least privilege. Destructive or system-wide powers are left out
        |    on purpose and must be granted per-user through Access Control:
        |      access-control.manage, role-config.manage,
        |      employees.delete, attendance.delete, leave-types.delete
        |  - Each group also carries its `dashboard.<type>` permission, so the
        |    matching role dashboard actually opens instead of falling back
        |    to the personal one.
        |
        */

        // ── Administration & executive ─────────────────────────────────
        'Administration' => [
            'designations' => ['Admin', 'System Admin', 'Manager'],
            'departments'  => ['Administration'],
            'permissions'  => [
                'dashboard.admin',

                // Organisation-wide read.
                'employees.view',
                'attendance.view',
                'departments.view',
                'designations.view',
                'sites.view',
                'daily-reports.view',
                'leaves.view',
                'payroll.view',
                'reports.view',
                'reports.export',
                'issue-reports.view',

                // Approvals that sit above a single department.
                'leaves.approve',
                'leaves.reject',
                'daily-reports.approve',
                'daily-reports.reject',
                'sites.assign',
                'sites.transfer',
            ],
        ],

        'Executive' => [
            'designations' => ['General Manager'],
            'departments'  => [],
            'permissions'  => [
                'dashboard.executive',

                // Read-only oversight; no writes at all.
                'employees.view',
                'attendance.view',
                'sites.view',
                'departments.view',
                'designations.view',
                'daily-reports.view',
                'leaves.view',
                'payroll.view',
                'reports.view',
                'reports.export',
            ],
        ],

        // ── Human resources ────────────────────────────────────────────
        'Human Resources' => [
            'designations' => ['HR', 'HR Manager'],
            'departments'  => ['HR', 'Human Resources'],
            'permissions'  => [
                'dashboard.hr',

                // People records. Delete is deliberately withheld.
                'employees.view',
                'employees.create',
                'employees.update',
                'departments.view',
                'designations.view',
                'designations.update',

                // HR owns attendance and shift marking.
                'attendance.view',
                'attendance.create',
                'attendance.update',
                'attendance.delete',
                'shifts.view',
                'shifts.create',
                'shifts.update',
                'shifts.delete',
                'shifts.assign',
                'shifts.transfer',

                // Leave administration.
                'leaves.view',
                'leaves.create',
                'leaves.update',
                'leaves.approve',
                'leaves.reject',
                'leave-types.view',
                'leave-types.create',
                'leave-types.update',

                // DPR review.
                'daily-reports.view',
                'daily-reports.approve',
                'daily-reports.reject',

                // Payroll administration.
                'payroll.view',
                'payroll.manage',
                'payroll.update',
                'salary-structures.view',
                'salary-structures.manage',
                'payslips.view',
                'payslips.manage',
                'payroll-period.view',
                'payroll-period.create',
                'process-payroll.view',
                'process-payroll.create',

                'sites.view',
                'reports.view',
                'reports.create',
                'reports.update',
                'reports.export',
                'issue-reports.view',
            ],
        ],

        // ── Accounts & finance ─────────────────────────────────────────
        'Accounts' => [ 
            'designations' => ['Accountant'],
            'departments'  => ['Accounts', 'Finance'],
            'permissions'  => [
                'dashboard.finance',

                // Payroll disbursement.
                'payroll.view',
                'payroll.manage',
                'payslips.view',
                'payslips.manage',
                'payroll-period.view',
                'payroll-period.create',
                'process-payroll.view',
                'process-payroll.create',

                // Procurement.
                'purchase-orders.view',
                'purchase-orders.create',
                'purchase-orders.update',
                'purchase-orders.approve',
                'purchase-quotations.view',
                'purchase-quotations.create',
                'purchase-quotations.update',
                'purchase-quotations.convert',
                'purchase-returns.view',
                'purchase-returns.create',
                'goods-receipt-notes.view',
                'goods-receipt-notes.create',
                'suppliers.view',
                'suppliers.create',
                'suppliers.update',

                // Billing and collections.
                'sales-orders.view',
                'sales-orders.create',
                'sales-orders.update',
                'sales-quotations.view',
                'sales-quotations.create',
                'sales-quotations.update',
                'sales-returns.view',
                'sales-returns.create',
                'invoices.view',
                'invoices.create',
                'invoices.update',
                'invoices.email',
                'invoices.download',
                'payments.view',
                'payments.create',
                'payments.update',
                'delivery-notes.view',
                'customers.view',
                'customers.create',
                'customers.update',

                // Petty cash.
                'cash-vouchers.view',
                'cash-vouchers.create',
                'cash-vouchers.update',
                'cash-vouchers.approve',
                'cash-vouchers.reject',

                // Tally-style double-entry accounting.
                'account-groups.view',
                'account-groups.create',
                'account-groups.update',
                'account-groups.delete',
                'ledgers.view',
                'ledgers.create',
                'ledgers.update',
                'ledgers.delete',
                'financial-years.view',
                'financial-years.create',
                'financial-years.update',
                'financial-years.delete',

                // Tally-style vouchers (slice 2).
                'contra.view',
                'contra.create',
                'contra.update',
                'contra.delete',
                'payment-vouchers.view',
                'payment-vouchers.create',
                'payment-vouchers.update',
                'payment-vouchers.delete',
                'receipt-vouchers.view',
                'receipt-vouchers.create',
                'receipt-vouchers.update',
                'receipt-vouchers.delete',
                'journal.view',
                'journal.create',
                'journal.update',
                'journal.delete',
                'purchase-vouchers.view',
                'purchase-vouchers.create',
                'purchase-vouchers.update',
                'purchase-vouchers.delete',
                'sales-vouchers.view',
                'sales-vouchers.create',
                'sales-vouchers.update',
                'sales-vouchers.delete',
                'credit-notes.view',
                'credit-notes.create',
                'credit-notes.update',
                'credit-notes.delete',
                'debit-notes.view',
                'debit-notes.create',
                'debit-notes.update',
                'debit-notes.delete',
                'bank-reconciliation.view',
                'bank-reconciliation.update',
                'finance-reports.view',
 
                'employees.view',
                'attendance.view',
                'reports.view',
                'reports.create',
                'reports.export',
                'issue-reports.view',
            ],
        ],

        // ── Inventory & warehouse ──────────────────────────────────────
        'Inventory & Warehouse' => [
            'designations' => ['Store Manager'],
            'departments'  => ['Inventory & Warehouse', 'Warehouse'],
            'permissions'  => [
                'dashboard.inventory',

                'inventory.view',
                'inventory.manage',
                'products.view',
                'products.create',
                'products.update',
                'products.delete',
                'categories.view',
                'categories.create',
                'categories.update',
                'inventory-locations.view',
                'inventory-locations.create',
                'inventory-locations.update',
                'units.view',
                'units.create',
                'units.update',
                'unit-conversions.view',
                'unit-conversions.create',
                'unit-conversions.update',

                // Stock on hand and its movements.
                'stock.view',
                'stock.create',
                'stock.update',
                'stock-transactions.view',
                'stock-transactions.create',
                'stock-requests.view',
                'stock-requests.create',
                'stock-requests.approve',
                'stock-requests.issue',
                'stock-requests.receive',
                'stock-transfers.view',
                'stock-transfers.create',
                'stock-transfers.approve',

                // Goods in / out.
                'goods-receipt-notes.view',
                'goods-receipt-notes.create',
                'delivery-notes.view',
                'delivery-notes.create',
                'purchase-orders.view',
                'sales-orders.view',
                'suppliers.view',
                'customers.view',

                'sites.view',
                'attendance.view',
                'reports.view',
                'issue-reports.view',
            ],
        ],

        // ── Sales & survey ─────────────────────────────────────────────
        'Sales' => [
            'designations' => ['Sales'],
            'departments'  => ['Sales'],
            'permissions'  => [
                'dashboard.sales',

                // The survey / pipeline the Sales sidebar is built around.
                'buildings.view',
                'buildings.create',
                'buildings.update',
                'site-visits.view',
                'site-visits.create',
                'site-visits.update',
                'opportunities.view',
                'opportunities.create',
                'opportunities.update',
                'follow-ups.view',
                'follow-ups.create',
                'follow-ups.update',
                'quotations.view',
                'quotations.create',
                'quotations.update',
                'extinguishers.view',
                'sales-documents.view',
                'sales-documents.create',
                'sales-documents.download',


                'sites.view',
                'reports.view',
                'issue-reports.view',
            ],
        ],

        // ── Design ─────────────────────────────────────────────────────
        'Design' => [
            'designations' => ['Designer', 'Design Manager'],
            'departments'  => ['Design', 'Designer'],
            'permissions'  => [
                'dashboard.design',

                'buildings.view',
                'buildings.create',
                'buildings.update',
                'site-visits.view',
                'site-visits.create',
                'site-visits.update',
                'quotations.view',
                'quotations.create',
                'quotations.update',
                'quotations.delete',
                'extinguishers.view',
                'sales-documents.view',
                'sales-documents.create',
                'sales-documents.delete',
                'sales-documents.download',
                'sites.view',
                'reports.view',
                'issue-reports.view',
            ],
        ],

        // ── Production ─────────────────────────────────────────────────
        'Production (supervisors)' => [
            'designations' => ['Production Manager', 'Workshop Supervisor'],
            'departments'  => [],
            'permissions'  => [
                'dashboard.production',

                'employees.view',
                'attendance.view',
                'attendance.create',
                'attendance.update',
                'daily-reports.view',
                'daily-reports.approve',
                'daily-reports.reject',
                'sites.view',
                'stock.view',
                'stock-requests.view',
                'stock-requests.create',
                'stock-requests.approve',
                'reports.view',
                'issue-reports.view',
            ],
        ],

        'Production (field staff)' => [
            'designations' => ['Fitter', 'Welder', 'Electrician', 'Helper'],
            'departments'  => [],
            'permissions'  => [
                // Self-service (attendance, DPRs, leaves, payslips) is already
                // in `baseline`; this only adds what the field needs.
                // 'sites.view',
                // 'stock.view',
                // 'stock-requests.view',
                // 'stock-requests.create',
                // 'extinguishers.view',
                // 'reports.view',
            ],
        ],

        // ── IT ─────────────────────────────────────────────────────────
        'IT' => [
            'designations' => ['IT Manager', 'Developer', 'IT'],
            'departments'  => ['IT'],
            'permissions'  => [
                'dashboard.it',

                // Bug reports are triaged by IT, so they own the whole module.
                // Filing a report is baseline; update/delete is what unhide's
                // the "Issue Reports" sidebar entry.
                'issue-reports.view',
                'issue-reports.create',
                'issue-reports.update',
                'issue-reports.delete',

                // Read-only context for reproducing what a user reported.
                'reports.view',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Implied permissions
    |--------------------------------------------------------------------------
    |
    | Permissions that are AUTO-GRANTED (never auto-revoked) the moment an
    | employee is granted ANY permission belonging to one of the listed
    | trigger modules. A supplier or customer list is almost always required to
    | work with products or with purchase/sales documents, so we grant read
    | access automatically instead of asking admins to remember to add it.
    |
    | Key = the permission to auto-grant when a trigger module is granted.
    | Value = modules whose ANY action permission triggers the grant.
    |
    */
    'implied_permissions' => [
        // The BOQ builder is unusable without the product master, because every
        // BOQ line is picked from it. Granting any part of the Quotations / BOQ
        // module therefore also unlocks the read-only product grant. This does
        // NOT reveal the Inventory › Products sidebar entry, which needs the
        // full `products.view` grant. (config/access.php `boq-products`.)
        'boq-products.view' => [
            'quotations',
        ],
        // Product section / procurement-selling workflows need to pick suppliers.
        'suppliers.view' => [
            'products',
            'stock',
            'purchase-orders',
            'purchase-quotations',
            'purchase-returns',
            'delivery-notes',
            'sales-orders',
            'sales-quotations',
            'sales-returns',
            'invoices',
            'payments',
            'goods-receipt-notes',
            'purchase-vouchers',
            'debit-notes',
        ],
        // Purchase & sales workflows need the customer list too.
        'customers.view' => [
            'purchase-orders',
            'purchase-quotations',
            'purchase-returns',
            'delivery-notes',
            'sales-orders',
            'sales-quotations',
            'sales-returns',
            'invoices',
            'payments',
            'goods-receipt-notes',
            'sales-vouchers',
            'credit-notes',
        ],
    ],
];
