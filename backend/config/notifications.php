<?php

/*
|--------------------------------------------------------------------------
| Notification catalogue
|--------------------------------------------------------------------------
|
| Every notification the application can emit is declared here, so wording,
| severity, routing and recipients live in one reviewable place instead of
| being scattered across controllers and observers.
|
| Fields per type:
|
|   module      Grouping shown in the UI / used for filtering.
|   severity    info | success | warning | error. Drives the badge colour.
|   title       Short headline. Supports :placeholders.
|   message     Optional detail line. Supports :placeholders.
|   permission  Canonical {module}.{action} name from config/access.php.
|               Everyone holding it receives the notification. When nobody
|               holds it, super admins are notified instead so the event
|               never falls into a black hole. Set to null to require an
|               explicit recipient list at the call site.
|   link        In-app destination. Supports :placeholders.
|   dedupe      true to suppress repeats for the same record while an
|               unread copy already exists (used by scheduled scans).
|
| Placeholders are filled from the $replace array passed to
| NotificationService::notify(). Unknown placeholders render as an em dash
| rather than leaking the raw token.
|
*/

return [

    'types' => [

        /*
        |----------------------------------------------------------------------
        | HR - Attendance
        |----------------------------------------------------------------------
        */
        'attendance.missing' => [
            'module'     => 'Attendance',
            'severity'   => 'warning',
            'title'      => 'Attendance not marked',
            'message'    => ':employee has no attendance record for :date.',
            'permission' => 'attendance.view',
            'link'       => '/dashboard/attendance',
            'dedupe'     => true,
        ],

        'attendance.late' => [
            'module'     => 'Attendance',
            'severity'   => 'warning',
            'title'      => 'Late check-in',
            'message'    => ':employee checked in at :time on :date.',
            'permission' => 'attendance.view',
            'link'       => '/dashboard/attendance',
        ],

        'attendance.auto_closed' => [
            'module'     => 'Attendance',
            'severity'   => 'info',
            'title'      => 'Attendance auto-closed',
            'message'    => ':employee\'s attendance for :date was closed automatically at :time.',
            'permission' => 'attendance.view',
            'link'       => '/dashboard/attendance',
        ],

        /*
        |----------------------------------------------------------------------
        | HR - Daily reports
        |----------------------------------------------------------------------
        */
        'daily_report.submitted' => [
            'module'     => 'Daily Reports',
            'severity'   => 'info',
            'title'      => 'New daily report',
            'message'    => ':employee submitted a daily report for :date.',
            'permission' => 'daily-reports.approve',
            'link'       => '/dashboard/daily-reports/:id',
        ],

        'daily_report.approved' => [
            'module'     => 'Daily Reports',
            'severity'   => 'success',
            'title'      => 'Daily report approved',
            'message'    => 'Your daily report for :date was approved.',
            'permission' => null,
            'link'       => '/dashboard/daily-reports/:id',
        ],

        'daily_report.rejected' => [
            'module'     => 'Daily Reports',
            'severity'   => 'error',
            'title'      => 'Daily report rejected',
            'message'    => 'Your daily report for :date was rejected. :reason',
            'permission' => null,
            'link'       => '/dashboard/daily-reports/:id',
        ],

        'daily_report.rework' => [
            'module'     => 'Daily Reports',
            'severity'   => 'warning',
            'title'      => 'Daily report needs rework',
            'message'    => 'Your daily report for :date needs rework. :reason',
            'permission' => null,
            'link'       => '/dashboard/daily-reports/:id',
        ],

        /*
        |----------------------------------------------------------------------
        | HR - Leaves
        |----------------------------------------------------------------------
        */
        'leave.requested' => [
            'module'     => 'Leaves',
            'severity'   => 'info',
            'title'      => 'New leave request',
            'message'    => ':employee has requested :days day(s) of :leave_type from :from to :to.',
            'permission' => 'leaves.approve',
            'link'       => '/dashboard/leave-management/requests/:id',
        ],

        'leave.approved' => [
            'module'     => 'Leaves',
            'severity'   => 'success',
            'title'      => 'Leave approved',
            'message'    => 'Your :leave_type request from :from to :to was approved.',
            'permission' => null,
            'link'       => '/dashboard/my-leaves/:id',
        ],

        'leave.rejected' => [
            'module'     => 'Leaves',
            'severity'   => 'error',
            'title'      => 'Leave rejected',
            'message'    => 'Your :leave_type request from :from to :to was rejected. :reason',
            'permission' => null,
            'link'       => '/dashboard/my-leaves/:id',
        ],

        'leave.cancelled' => [
            'module'     => 'Leaves',
            'severity'   => 'warning',
            'title'      => 'Leave request cancelled',
            'message'    => ':employee cancelled their :leave_type request from :from to :to.',
            'permission' => 'leaves.approve',
            'link'       => '/dashboard/leave-management/requests/:id',
        ],

        'leave.balance_low' => [
            'module'     => 'Leaves',
            'severity'   => 'warning',
            'title'      => 'Leave balance running low',
            'message'    => 'You have :remaining day(s) of :leave_type left for this year.',
            'permission' => null,
            'link'       => '/dashboard/leave-management/balances',
            'dedupe'     => true,
        ],

        /*
        |----------------------------------------------------------------------
        | HR - Payroll
        |----------------------------------------------------------------------
        */
        'payroll.period_ready' => [
            'module'     => 'Payroll',
            'severity'   => 'info',
            'title'      => 'Payroll period ready to process',
            'message'    => 'Payroll for :period can now be processed.',
            'permission' => 'process-payroll.view',
            'link'       => '/dashboard/process-payroll',
            'dedupe'     => true,
        ],

        'payroll.generated' => [
            'module'     => 'Payroll',
            'severity'   => 'success',
            'title'      => 'Payroll generated',
            'message'    => 'Payroll for :period was generated for :count employee(s).',
            'permission' => 'process-payroll.view',
            'link'       => '/dashboard/process-payroll',
        ],

        'payroll.completed_with_warnings' => [
            'module'     => 'Payroll',
            'severity'   => 'warning',
            'title'      => 'Payroll generated with warnings',
            'message'    => 'Payroll for :period was generated but :count employee(s) had no paid days.',
            'permission' => 'process-payroll.view',
            'link'       => '/dashboard/process-payroll',
        ],

        'payroll.approved' => [
            'module'     => 'Payroll',
            'severity'   => 'success',
            'title'      => 'Payroll approved',
            'message'    => 'Payroll for :period was approved.',
            'permission' => 'payroll.manage',
            'link'       => '/dashboard/payroll',
        ],

        'payslip.published' => [
            'module'     => 'Payroll',
            'severity'   => 'info',
            'title'      => 'Payslip available',
            'message'    => 'Your payslip for :period is now available.',
            'permission' => null,
            'link'       => '/dashboard/my-payroll',
        ],

        /*
        |----------------------------------------------------------------------
        | Finance - Cash vouchers
        |----------------------------------------------------------------------
        */
        'cash_voucher.submitted' => [
            'module'     => 'Cash Vouchers',
            'severity'   => 'info',
            'title'      => 'New cash voucher',
            'message'    => ':employee submitted a cash voucher for :amount (:purpose).',
            'permission' => 'cash-vouchers.approve',
            'link'       => '/dashboard/cash-vouchers',
        ],

        'cash_voucher.approved' => [
            'module'     => 'Cash Vouchers',
            'severity'   => 'success',
            'title'      => 'Cash voucher approved',
            'message'    => 'Your cash voucher for :amount was approved.',
            'permission' => null,
            'link'       => '/dashboard/cash-vouchers',
        ],

        'cash_voucher.rejected' => [
            'module'     => 'Cash Vouchers',
            'severity'   => 'error',
            'title'      => 'Cash voucher rejected',
            'message'    => 'Your cash voucher for :amount was rejected. :reason',
            'permission' => null,
            'link'       => '/dashboard/cash-vouchers',
        ],

        /*
        |----------------------------------------------------------------------
        | Sales pipeline
        |----------------------------------------------------------------------
        */
        'quotation.created' => [
            'module'     => 'Quotations',
            'severity'   => 'info',
            'title'      => 'New quotation',
            'message'    => ':employee created quotation :quotation_no for :building.',
            'permission' => 'quotations.view',
            'link'       => '/dashboard/quotations',
        ],

        'quotation.accepted' => [
            'module'     => 'Quotations',
            'severity'   => 'success',
            'title'      => 'Quotation accepted',
            'message'    => 'Quotation :quotation_no was marked as accepted.',
            'permission' => 'quotations.view',
            'link'       => '/dashboard/quotations',
        ],

        'quotation.rejected' => [
            'module'     => 'Quotations',
            'severity'   => 'warning',
            'title'      => 'Quotation rejected',
            'message'    => 'Quotation :quotation_no was marked as rejected.',
            'permission' => 'quotations.view',
            'link'       => '/dashboard/quotations',
        ],

        'opportunity.created' => [
            'module'     => 'Opportunities',
            'severity'   => 'info',
            'title'      => 'New opportunity',
            'message'    => ':employee created an opportunity: :name for :building.',
            'permission' => 'opportunities.view',
            'link'       => '/dashboard/opportunities/:id',
        ],

        'opportunity.stage_changed' => [
            'module'     => 'Opportunities',
            'severity'   => 'info',
            'title'      => 'Opportunity stage changed',
            'message'    => ':name moved from :from to :to.',
            'permission' => null,
            'link'       => '/dashboard/opportunities/:id',
        ],

        'opportunity.won' => [
            'module'     => 'Opportunities',
            'severity'   => 'success',
            'title'      => 'Opportunity won',
            'message'    => ':name was won (:value).',
            'permission' => 'opportunities.view',
            'link'       => '/dashboard/opportunities/:id',
        ],

        'opportunity.lost' => [
            'module'     => 'Opportunities',
            'severity'   => 'warning',
            'title'      => 'Opportunity lost',
            'message'    => ':name was lost. :reason',
            'permission' => 'opportunities.view',
            'link'       => '/dashboard/opportunities/:id',
        ],

        'followup.created' => [
            'module'     => 'Follow-ups',
            'severity'   => 'info',
            'title'      => 'New follow-up scheduled',
            'message'    => ':employee scheduled a :type follow-up on :date.',
            'permission' => 'follow-ups.view',
            'link'       => '/dashboard/follow-ups',
        ],

        'followup.due_today' => [
            'module'     => 'Follow-ups',
            'severity'   => 'warning',
            'title'      => 'Follow-up due today',
            'message'    => ':type follow-up on :date is due today.',
            'permission' => null,
            'link'       => '/dashboard/follow-ups',
            'dedupe'     => true,
        ],

        'followup.overdue' => [
            'module'     => 'Follow-ups',
            'severity'   => 'error',
            'title'      => 'Follow-up overdue',
            'message'    => ':type follow-up scheduled for :date is overdue.',
            'permission' => null,
            'link'       => '/dashboard/follow-ups',
            'dedupe'     => true,
        ],

        'site_visit.scheduled' => [
            'module'     => 'Site Visits',
            'severity'   => 'info',
            'title'      => 'New site visit',
            'message'    => ':employee scheduled a visit to :building on :date.',
            'permission' => 'site-visits.view',
            'link'       => '/dashboard/site-visits/:id',
        ],

        /*
        |----------------------------------------------------------------------
        | Sales documents
        |----------------------------------------------------------------------
        */
        'sales_order.created' => [
            'module'     => 'Sales Orders',
            'severity'   => 'info',
            'title'      => 'New sales order',
            'message'    => ':employee created sales order :so_number.',
            'permission' => 'sales-orders.approve',
            'link'       => '/dashboard/sales/:id',
        ],

        'sales_order.approved' => [
            'module'     => 'Sales Orders',
            'severity'   => 'success',
            'title'      => 'Sales order approved',
            'message'    => 'Sales order :so_number was approved.',
            'permission' => 'sales-orders.view',
            'link'       => '/dashboard/sales/:id',
        ],

        'sales_order.rejected' => [
            'module'     => 'Sales Orders',
            'severity'   => 'error',
            'title'      => 'Sales order rejected',
            'message'    => 'Sales order :so_number was rejected. :reason',
            'permission' => 'sales-orders.view',
            'link'       => '/dashboard/sales/:id',
        ],

        'invoice.created' => [
            'module'     => 'Invoices',
            'severity'   => 'info',
            'title'      => 'New invoice',
            'message'    => 'Invoice :invoice_number was created for :customer (:amount).',
            'permission' => 'invoices.view',
            'link'       => '/dashboard/invoices/:id',
        ],

        'invoice.overdue' => [
            'module'     => 'Invoices',
            'severity'   => 'error',
            'title'      => 'Invoice overdue',
            'message'    => 'Invoice :invoice_number for :customer is overdue since :due_date (:amount).',
            'permission' => 'invoices.view',
            'link'       => '/dashboard/invoices/:id',
            'dedupe'     => true,
        ],

        'payment.received' => [
            'module'     => 'Payments',
            'severity'   => 'success',
            'title'      => 'Payment received',
            'message'    => 'A payment of :amount was recorded against :invoice.',
            'permission' => 'payments.view',
            'link'       => '/dashboard/invoices/:id',
        ],

        /*
        |----------------------------------------------------------------------
        | Purchase
        |----------------------------------------------------------------------
        */
        'purchase_order.created' => [
            'module'     => 'Purchase Orders',
            'severity'   => 'info',
            'title'      => 'New purchase order',
            'message'    => ':employee created purchase order :po_number for :supplier.',
            'permission' => 'purchase-orders.approve',
            'link'       => '/dashboard/purchases/:id',
        ],

        'purchase_order.approved' => [
            'module'     => 'Purchase Orders',
            'severity'   => 'success',
            'title'      => 'Purchase order approved',
            'message'    => 'Purchase order :po_number was approved.',
            'permission' => 'purchase-orders.view',
            'link'       => '/dashboard/purchases/:id',
        ],

        'purchase_order.rejected' => [
            'module'     => 'Purchase Orders',
            'severity'   => 'error',
            'title'      => 'Purchase order rejected',
            'message'    => 'Purchase order :po_number was rejected. :reason',
            'permission' => 'purchase-orders.view',
            'link'       => '/dashboard/purchases/:id',
        ],

        'goods_receipt.created' => [
            'module'     => 'Goods Receipts',
            'severity'   => 'info',
            'title'      => 'Goods received',
            'message'    => 'Goods were received against purchase order :po_number.',
            'permission' => 'goods-receipt-notes.view',
            'link'       => '/dashboard/purchases/:id',
        ],

        /*
        |----------------------------------------------------------------------
        | Inventory
        |----------------------------------------------------------------------
        */
        'stock.low' => [
            'module'     => 'Inventory',
            'severity'   => 'warning',
            'title'      => 'Stock below reorder level',
            'message'    => ':product is down to :quantity :unit (reorder level :reorder_level).',
            'permission' => 'inventory.manage',
            'link'       => '/dashboard/inventory/stock',
            'dedupe'     => true,
        ],

        'stock_request.created' => [
            'module'     => 'Inventory',
            'severity'   => 'info',
            'title'      => 'New stock request',
            'message'    => ':employee requested stock for :product.',
            'permission' => 'stock-requests.approve',
            'link'       => '/dashboard/inventory/requests/:id',
        ],

        'stock_request.approved' => [
            'module'     => 'Inventory',
            'severity'   => 'success',
            'title'      => 'Stock request approved',
            'message'    => 'Your stock request for :product was approved.',
            'permission' => null,
            'link'       => '/dashboard/inventory/requests/:id',
        ],

        'stock_request.issued' => [
            'module'     => 'Inventory',
            'severity'   => 'success',
            'title'      => 'Stock request issued',
            'message'    => 'Stock for your request (:product) has been issued.',
            'permission' => null,
            'link'       => '/dashboard/inventory/requests/:id',
        ],

        'stock_transfer.created' => [
            'module'     => 'Inventory',
            'severity'   => 'info',
            'title'      => 'New stock transfer',
            'message'    => ':employee requested a stock transfer of :product.',
            'permission' => 'stock-transfers.approve',
            'link'       => '/dashboard/inventory/transfers',
        ],

        'stock_transfer.approved' => [
            'module'     => 'Inventory',
            'severity'   => 'success',
            'title'      => 'Stock transfer approved',
            'message'    => 'Your stock transfer of :product was approved.',
            'permission' => null,
            'link'       => '/dashboard/inventory/transfers',
        ],

        /*
        |----------------------------------------------------------------------
        | Compliance
        |----------------------------------------------------------------------
        */
        'amc.expiring' => [
            'module'     => 'Compliance',
            'severity'   => 'warning',
            'title'      => 'AMC expiring soon',
            'message'    => 'The AMC for :building expires on :end_date.',
            'permission' => 'buildings.view',
            'link'       => '/dashboard/buildings/:id',
            'dedupe'     => true,
        ],

        'extinguisher.status_changed' => [
            'module'     => 'Compliance',
            'severity'   => 'warning',
            'title'      => 'Extinguisher status changed',
            'message'    => 'Extinguisher :serial moved to :status.',
            'permission' => 'extinguishers.view',
            'link'       => '/dashboard/extinguishers',
        ],

        'document.expiring' => [
            'module'     => 'Compliance',
            'severity'   => 'warning',
            'title'      => 'Document expiring soon',
            'message'    => 'The document ":document" for :employee expires on :expiry_date.',
            'permission' => 'employees.view',
            'link'       => '/dashboard/employees/:id',
            'dedupe'     => true,
        ],

        /*
        |----------------------------------------------------------------------
        | Support & system
        |----------------------------------------------------------------------
        */
        'issue_report.created' => [
            'module'     => 'Issue Reports',
            'severity'   => 'warning',
            'title'      => 'New issue report',
            'message'    => ':employee reported: :subject',
            'permission' => 'issue-reports.update',
            'link'       => '/dashboard/issue-reports',
        ],

        'issue_report.resolved' => [
            'module'     => 'Issue Reports',
            'severity'   => 'success',
            'title'      => 'Issue report resolved',
            'message'    => 'Your issue report ":subject" was resolved.',
            'permission' => null,
            'link'       => '/dashboard/issue-reports',
        ],

        'access.changed' => [
            'module'     => 'Access Control',
            'severity'   => 'info',
            'title'      => 'Your access was updated',
            'message'    => ':actor updated your system access.',
            'permission' => null,
            'link'       => '/dashboard/my-dashboard',
        ],

        'chat.mention' => [
            'module'     => 'Chat',
            'severity'   => 'info',
            'title'      => 'You were mentioned',
            'message'    => ':actor mentioned you in :channel. :preview',
            'permission' => null,
            'link'       => '/dashboard/chat',
        ],

        'chat.replied' => [
            'module'     => 'Chat',
            'severity'   => 'info',
            'title'      => 'New reply from :actor',
            'message'    => ':actor replied to your message in :channel. :preview',
            'permission' => null,
            'link'       => '/dashboard/chat',
        ],

        'report.generated' => [
            'module'     => 'Reports',
            'severity'   => 'info',
            'title'      => 'Scheduled report ready',
            'message'    => 'The scheduled report ":report" is ready to download.',
            'permission' => 'reports.view',
            'link'       => '/dashboard/reports',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduled scans
    |--------------------------------------------------------------------------
    |
    | The proactive alerts above are produced by `php artisan notifications:scan`,
    | which the scheduler runs daily. Horizon is how long a de-duplicated alert
    | stays silent after the user has read it.
    |
    */
    'schedule' => [
        'scan_at'       => '09:00',
        'dedupe_hours'  => 168,
        'expiry_days'   => 30,
    ],
];
