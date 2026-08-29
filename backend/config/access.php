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
    ],

    // Category-driven grouping mirrors the sidebar so the admin can navigate
    // the same way. Not used for enforcement — purely organizational.
    'categories' => [
        'Overview' => [
            'dashboard' => [
                'label' => 'Dashboard',
                'actions' => ['view'],
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
                'actions' => ['view', 'create', 'update', 'delete'],
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
                'actions' => ['view', 'manage'],
            ],
            'salary-structures' => [
                'label' => 'Salary Structures',
                'actions' => ['view', 'manage'],
            ],
            'payslips' => [
                'label' => 'Payslips',
                'actions' => ['view', 'manage'],
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
                'actions' => ['view', 'create'],
            ],
            'stock-transactions' => [
                'label' => 'Stock Transactions',
                'actions' => ['view', 'create'],
            ],
            'stock-requests' => [
                'label' => 'Stock Requests',
                'actions' => ['view', 'create', 'approve', 'issue', 'receive'],
            ],
            'stock-transfers' => [
                'label' => 'Stock Transfers',
                'actions' => ['view', 'create', 'approve'],
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
        'attendance.view',
        'attendance.checkin',
        'attendance.checkout',
        'daily-reports.view',
        'daily-reports.create',
        'daily-reports.submit',
        'leaves.view',
        'leaves.create',
        'leaves.update',
        'payroll.view',
    ],
];
