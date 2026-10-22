<?php

namespace App\Support\Reports\Modules;

use App\Support\Reports\Spec;

/**
 * Sales and purchasing reports.
 *
 * The commercial half of the business: the customers and suppliers it trades
 * with, the quotations that turn into orders, the orders that turn into
 * goods receipts, deliveries and invoices, and the money that comes back.
 */
class SalesPurchases
{
    public static function modules(): array
    {
        return [
            'customers' => self::customers(),
            'suppliers' => self::suppliers(),
            'purchase-orders' => self::purchaseOrders(),
            'purchase-quotations' => self::purchaseQuotations(),
            'purchase-returns' => self::purchaseReturns(),
            'goods-receipt-notes' => self::goodsReceiptNotes(),
            'delivery-notes' => self::deliveryNotes(),
            'sales-orders' => self::salesOrders(),
            'sales-quotations' => self::salesQuotations(),
            'sales-returns' => self::salesReturns(),
            'invoices' => self::invoices(),
            'payments' => self::payments(),
            'stock-receipts' => self::stockReceipts(),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Customers                                                           */
    /* ------------------------------------------------------------------ */

    private static function customers(): array
    {
        return [
            'label' => 'Customers',
            'category' => 'Sales & Purchases',
            'permission' => 'customers.view',
            'reports' => [

                'master' => [
                    'label' => 'Customer Master',
                    'description' => 'Every customer on the books.',
                    'base' => 'customers',
                    'columns' => [
                        Spec::col('name', 'Customer', 'customers.name', ['primary' => true]),
                        Spec::col('email', 'Email', 'customers.email'),
                        Spec::col('phone', 'Phone', 'customers.phone'),
                        Spec::col('gst_number', 'GST number', 'customers.gst_number'),
                        Spec::num('credit_days', 'Credit days', 'customers.credit_days'),
                        Spec::col('address', 'Address', 'customers.address'),
                        Spec::date('added_on', 'Added on', 'customers.created_at'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['customers.name', 'customers.email', 'customers.phone', 'customers.gst_number']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Customers', 'count(*)'),
                        Spec::summary('gst_registered', 'GST registered', "sum(case when customers.gst_number is not null and customers.gst_number <> '' then 1 else 0 end)"),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'directory' => [
                    'label' => 'Customer Contact Directory',
                    'description' => 'Who to call, and where they are.',
                    'base' => 'customers',
                    'columns' => [
                        Spec::col('name', 'Customer', 'customers.name', ['primary' => true]),
                        Spec::col('contact', 'Phone', 'customers.phone'),
                        Spec::col('email', 'Email', 'customers.email'),
                        Spec::col('address', 'Address', 'customers.address'),
                        Spec::num('credit_days', 'Credit days', 'customers.credit_days'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['customers.name', 'customers.phone', 'customers.email']),
                    ],
                    'summary' => [Spec::summary('total', 'Customers', 'count(*)')],
                    'order' => [Spec::order('name')],
                ],

                'credit-terms' => [
                    'label' => 'Customers by Credit Terms',
                    'description' => 'How long credit each customer is given.',
                    'base' => 'customers',
                    'group_by' => ['customers.credit_days'],
                    'columns' => [
                        Spec::num('credit_days', 'Credit days', 'customers.credit_days', ['primary' => true]),
                        Spec::num('customers', 'Customers', 'count(*)'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['customers.name']),
                    ],
                    'summary' => [
                        Spec::summary('customers', 'Customers', 'count(*)'),
                        Spec::summary('terms', 'Credit bands', 'count(distinct customers.credit_days)'),
                    ],
                    'order' => [Spec::order('credit_days')],
                    'chart' => Spec::chart('bar', 'credit_days', 'customers'),
                ],

                'gst-register' => [
                    'label' => 'Customer GST Register',
                    'description' => 'The GST numbers the company bills against.',
                    'base' => 'customers',
                    'columns' => [
                        Spec::col('name', 'Customer', 'customers.name', ['primary' => true]),
                        Spec::col('gst_number', 'GST number', 'customers.gst_number'),
                        Spec::col('phone', 'Phone', 'customers.phone'),
                        Spec::col('email', 'Email', 'customers.email'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['customers.name', 'customers.gst_number']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Customers', 'count(*)'),
                        Spec::summary('registered', 'With a GST number', "sum(case when customers.gst_number is not null and customers.gst_number <> '' then 1 else 0 end)"),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'billing-summary' => [
                    'label' => 'Billing by Customer',
                    'description' => 'What each customer has been invoiced, and what is still outstanding.',
                    'base' => 'invoices',
                    'joins' => [Spec::join('customers', 'invoices.customer_id', 'customers.id')],
                    'group_by' => ['customers.id', 'customers.name'],
                    'columns' => [
                        Spec::col('customer', 'Customer', 'customers.name', ['primary' => true]),
                        Spec::num('invoices', 'Invoices', 'count(*)'),
                        Spec::money('billed', 'Billed', 'sum(invoices.grand_total)'),
                        Spec::money('paid', 'Paid', 'sum(invoices.paid_amount)'),
                        Spec::money('outstanding', 'Outstanding', 'sum(coalesce(invoices.grand_total, 0) - coalesce(invoices.paid_amount, 0))'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Issued from', 'invoices.issue_date', '>='),
                        Spec::dateFilter('to', 'Issued to', 'invoices.issue_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('customers', 'Customers', 'count(distinct customers.id)'),
                        Spec::summary('billed', 'Total billed', 'sum(invoices.grand_total)', 'money'),
                        Spec::summary('outstanding', 'Total outstanding', 'sum(coalesce(invoices.grand_total, 0) - coalesce(invoices.paid_amount, 0))', 'money'),
                    ],
                    'order' => [Spec::order('billed', 'desc')],
                    'chart' => Spec::chart('bar', 'customer', 'billed'),
                ],

                'payment-history' => [
                    'label' => 'Customer Payment History',
                    'description' => 'Every payment received, and who made it.',
                    'base' => 'payments',
                    'joins' => [Spec::join('customers', 'payments.customer_id', 'customers.id')],
                    'columns' => [
                        Spec::date('payment_date', 'Date', 'payments.payment_date', ['primary' => true]),
                        Spec::col('customer', 'Customer', 'customers.name'),
                        Spec::money('amount', 'Amount', 'payments.amount'),
                        Spec::col('method', 'Method', 'payments.payment_method'),
                        Spec::col('reference', 'Reference', 'payments.reference_number'),
                        Spec::col('type', 'Type', 'payments.type'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'payments.payment_date', '>='),
                        Spec::dateFilter('to', 'To', 'payments.payment_date', '<='),
                        Spec::sourceFilter('customer_id', 'Customer', 'customers', 'id', 'payments.customer_id'),
                        Spec::sourceFilter('method', 'Method', 'payments', 'payment_method', 'payments.payment_method'),
                    ],
                    'summary' => [
                        Spec::summary('payments', 'Payments', 'count(*)'),
                        Spec::summary('amount', 'Received', 'sum(payments.amount)', 'money'),
                    ],
                    'order' => [Spec::order('payment_date', 'desc')],
                ],

                'onboarding' => [
                    'label' => 'Customer Onboarding Trend',
                    'description' => 'How the customer list grew month by month.',
                    'base' => 'customers',
                    'group_by' => ["concat(year(customers.created_at), '-', lpad(month(customers.created_at), 2, '0'))"],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(year(customers.created_at), '-', lpad(month(customers.created_at), 2, '0'))", ['primary' => true]),
                        Spec::num('added', 'Customers added', 'count(*)'),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('added', 'Customers added', 'count(*)'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'added'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Suppliers                                                           */
    /* ------------------------------------------------------------------ */

    private static function suppliers(): array
    {
        return [
            'label' => 'Suppliers',
            'category' => 'Sales & Purchases',
            'permission' => 'suppliers.view',
            'reports' => [

                'master' => [
                    'label' => 'Supplier Master',
                    'description' => 'Every supplier the company buys from.',
                    'base' => 'suppliers',
                    'columns' => [
                        Spec::col('name', 'Supplier', 'suppliers.name', ['primary' => true]),
                        Spec::col('email', 'Email', 'suppliers.email'),
                        Spec::col('phone', 'Phone', 'suppliers.phone'),
                        Spec::col('gst_number', 'GST number', 'suppliers.gst_number'),
                        Spec::num('credit_days', 'Credit days', 'suppliers.credit_days'),
                        Spec::col('address', 'Address', 'suppliers.address'),
                        Spec::date('added_on', 'Added on', 'suppliers.created_at'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['suppliers.name', 'suppliers.email', 'suppliers.phone', 'suppliers.gst_number']),
                    ],
                    'summary' => [Spec::summary('total', 'Suppliers', 'count(*)')],
                    'order' => [Spec::order('name')],
                ],

                'directory' => [
                    'label' => 'Supplier Contact Directory',
                    'description' => 'Who to call at each supplier.',
                    'base' => 'suppliers',
                    'columns' => [
                        Spec::col('name', 'Supplier', 'suppliers.name', ['primary' => true]),
                        Spec::col('contact', 'Phone', 'suppliers.phone'),
                        Spec::col('email', 'Email', 'suppliers.email'),
                        Spec::col('address', 'Address', 'suppliers.address'),
                        Spec::num('credit_days', 'Credit days', 'suppliers.credit_days'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['suppliers.name', 'suppliers.phone', 'suppliers.email']),
                    ],
                    'summary' => [Spec::summary('total', 'Suppliers', 'count(*)')],
                    'order' => [Spec::order('name')],
                ],

                'credit-terms' => [
                    'label' => 'Suppliers by Credit Terms',
                    'description' => 'How long credit each supplier allows.',
                    'base' => 'suppliers',
                    'group_by' => ['suppliers.credit_days'],
                    'columns' => [
                        Spec::num('credit_days', 'Credit days', 'suppliers.credit_days', ['primary' => true]),
                        Spec::num('suppliers', 'Suppliers', 'count(*)'),
                    ],
                    'summary' => [
                        Spec::summary('suppliers', 'Suppliers', 'count(*)'),
                        Spec::summary('terms', 'Credit bands', 'count(distinct suppliers.credit_days)'),
                    ],
                    'order' => [Spec::order('credit_days')],
                    'chart' => Spec::chart('bar', 'credit_days', 'suppliers'),
                ],

                'gst-register' => [
                    'label' => 'Supplier GST Register',
                    'description' => 'The GST numbers the company purchases against.',
                    'base' => 'suppliers',
                    'columns' => [
                        Spec::col('name', 'Supplier', 'suppliers.name', ['primary' => true]),
                        Spec::col('gst_number', 'GST number', 'suppliers.gst_number'),
                        Spec::col('phone', 'Phone', 'suppliers.phone'),
                        Spec::col('email', 'Email', 'suppliers.email'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['suppliers.name', 'suppliers.gst_number']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Suppliers', 'count(*)'),
                        Spec::summary('registered', 'With a GST number', "sum(case when suppliers.gst_number is not null and suppliers.gst_number <> '' then 1 else 0 end)"),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'purchase-summary' => [
                    'label' => 'Purchase Spend by Supplier',
                    'description' => 'How much the company buys from each supplier.',
                    'base' => 'purchase_orders',
                    'joins' => [Spec::join('suppliers', 'purchase_orders.supplier_id', 'suppliers.id')],
                    'group_by' => ['suppliers.id', 'suppliers.name'],
                    'columns' => [
                        Spec::col('supplier', 'Supplier', 'suppliers.name', ['primary' => true]),
                        Spec::num('orders', 'Orders', 'count(*)'),
                        Spec::money('total', 'Purchased', 'sum(purchase_orders.total_amount)'),
                        Spec::money('tax', 'Tax', 'sum(purchase_orders.tax_amount)'),
                        Spec::money('average', 'Average order', 'round(avg(purchase_orders.total_amount), 2)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'purchase_orders.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'purchase_orders.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('suppliers', 'Suppliers', 'count(distinct suppliers.id)'),
                        Spec::summary('total', 'Total purchased', 'sum(purchase_orders.total_amount)', 'money'),
                    ],
                    'order' => [Spec::order('total', 'desc')],
                    'chart' => Spec::chart('bar', 'supplier', 'total'),
                ],

                'payment-history' => [
                    'label' => 'Supplier Payment History',
                    'description' => 'Every payment made out to a supplier.',
                    'base' => 'payments',
                    'joins' => [Spec::join('suppliers', 'payments.supplier_id', 'suppliers.id')],
                    'columns' => [
                        Spec::date('payment_date', 'Date', 'payments.payment_date', ['primary' => true]),
                        Spec::col('supplier', 'Supplier', 'suppliers.name'),
                        Spec::money('amount', 'Amount', 'payments.amount'),
                        Spec::col('method', 'Method', 'payments.payment_method'),
                        Spec::col('reference', 'Reference', 'payments.reference_number'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'payments.payment_date', '>='),
                        Spec::dateFilter('to', 'To', 'payments.payment_date', '<='),
                        Spec::sourceFilter('supplier_id', 'Supplier', 'suppliers', 'id', 'payments.supplier_id'),
                    ],
                    'summary' => [
                        Spec::summary('payments', 'Payments', 'count(*)'),
                        Spec::summary('amount', 'Paid out', 'sum(payments.amount)', 'money'),
                    ],
                    'order' => [Spec::order('payment_date', 'desc')],
                ],

                'system-suppliers' => [
                    'label' => 'System Suppliers',
                    'description' => 'The internal vendor records kept alongside the real ones.',
                    'base' => 'suppliers',
                    'columns' => [
                        Spec::col('name', 'Supplier', 'suppliers.name', ['primary' => true]),
                        Spec::badge('is_system', 'System', 'suppliers.is_system'),
                        Spec::col('phone', 'Phone', 'suppliers.phone'),
                        Spec::col('gst_number', 'GST number', 'suppliers.gst_number'),
                    ],
                    'filters' => [
                        Spec::sourceMultiFilter('is_system', 'System', 'suppliers', 'is_system', 'suppliers.is_system'),
                        Spec::textFilter('q', 'Search', ['suppliers.name']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Suppliers', 'count(*)'),
                        Spec::summary('system', 'System suppliers', 'sum(case when suppliers.is_system = 1 then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('name')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Purchase orders                                                     */
    /* ------------------------------------------------------------------ */

    private static function purchaseOrders(): array
    {
        return [
            'label' => 'Purchase Orders',
            'category' => 'Sales & Purchases',
            'permission' => 'purchase-orders.view',
            'reports' => [

                'master' => [
                    'label' => 'Purchase Order Register',
                    'description' => 'Every purchase order, and the supplier it sits with.',
                    'base' => 'purchase_orders',
                    'joins' => [
                        Spec::join('suppliers', 'purchase_orders.supplier_id', 'suppliers.id'),
                        Spec::join('sites', 'purchase_orders.site_id', 'sites.id', 'left', 'site'),
                    ],
                    'columns' => [
                        Spec::col('po_number', 'PO number', 'purchase_orders.po_number', ['primary' => true]),
                        Spec::date('created', 'Raised on', 'purchase_orders.created_at'),
                        Spec::col('supplier', 'Supplier', 'suppliers.name'),
                        Spec::col('site', 'Site', 'site.name'),
                        Spec::badge('status', 'Status', 'purchase_orders.status'),
                        Spec::money('total', 'Total', 'purchase_orders.total_amount'),
                        Spec::money('tax', 'Tax', 'purchase_orders.tax_amount'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'purchase_orders.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'purchase_orders.created_at', '<='),
                        Spec::sourceMultiFilter('status', 'Status', 'purchase_orders', 'status', 'purchase_orders.status'),
                        Spec::sourceFilter('supplier_id', 'Supplier', 'suppliers', 'id', 'purchase_orders.supplier_id'),
                        Spec::sourceFilter('site_id', 'Site', 'sites', 'id', 'purchase_orders.site_id'),
                        Spec::textFilter('q', 'Search', ['purchase_orders.po_number', 'suppliers.name']),
                    ],
                    'summary' => [
                        Spec::summary('orders', 'Orders', 'count(*)'),
                        Spec::summary('total', 'Total value', 'sum(purchase_orders.total_amount)', 'money'),
                        Spec::summary('tax', 'Total tax', 'sum(purchase_orders.tax_amount)', 'money'),
                    ],
                    'order' => [Spec::order('created', 'desc')],
                ],

                'by-supplier' => [
                    'label' => 'Purchase Orders by Supplier',
                    'description' => 'How much is bought from each supplier.',
                    'base' => 'purchase_orders',
                    'joins' => [Spec::join('suppliers', 'purchase_orders.supplier_id', 'suppliers.id')],
                    'group_by' => ['suppliers.id', 'suppliers.name'],
                    'columns' => [
                        Spec::col('supplier', 'Supplier', 'suppliers.name', ['primary' => true]),
                        Spec::num('orders', 'Orders', 'count(*)'),
                        Spec::money('total', 'Value', 'sum(purchase_orders.total_amount)'),
                        Spec::money('tax', 'Tax', 'sum(purchase_orders.tax_amount)'),
                        Spec::money('average', 'Average order', 'round(avg(purchase_orders.total_amount), 2)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'purchase_orders.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'purchase_orders.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('suppliers', 'Suppliers', 'count(distinct suppliers.id)'),
                        Spec::summary('total', 'Total value', 'sum(purchase_orders.total_amount)', 'money'),
                    ],
                    'order' => [Spec::order('total', 'desc')],
                    'chart' => Spec::chart('bar', 'supplier', 'total'),
                ],

                'by-status' => [
                    'label' => 'Purchase Orders by Status',
                    'description' => 'Where the orders sit in the approval cycle.',
                    'base' => 'purchase_orders',
                    'group_by' => ['purchase_orders.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'purchase_orders.status', ['primary' => true]),
                        Spec::num('orders', 'Orders', 'count(*)'),
                        Spec::money('total', 'Value', 'sum(purchase_orders.total_amount)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'purchase_orders.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'purchase_orders.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('statuses', 'Statuses', 'count(distinct purchase_orders.status)'),
                        Spec::summary('orders', 'Orders', 'count(*)'),
                    ],
                    'order' => [Spec::order('orders', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'orders'),
                ],

                'monthly-trend' => [
                    'label' => 'Purchase Order Monthly Trend',
                    'description' => 'How the purchase value moved month by month.',
                    'base' => 'purchase_orders',
                    'group_by' => ['year(purchase_orders.created_at)', 'month(purchase_orders.created_at)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(purchase_orders.created_at), '/', year(purchase_orders.created_at))", ['primary' => true]),
                        Spec::num('orders', 'Orders', 'count(*)'),
                        Spec::money('total', 'Value', 'sum(purchase_orders.total_amount)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'purchase_orders.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'purchase_orders.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('total', 'Total value', 'sum(purchase_orders.total_amount)', 'money'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'total'),
                ],

                'items' => [
                    'label' => 'Purchase Order Items',
                    'description' => 'The lines behind every purchase order.',
                    'base' => 'purchase_order_items',
                    'joins' => [
                        Spec::join('purchase_orders', 'purchase_order_items.purchase_order_id', 'purchase_orders.id'),
                        Spec::join('suppliers', 'purchase_orders.supplier_id', 'suppliers.id', 'left', 'supplier'),
                        Spec::join('products', 'purchase_order_items.product_id', 'products.id', 'left', 'product'),
                    ],
                    'columns' => [
                        Spec::col('po_number', 'PO number', 'purchase_orders.po_number', ['primary' => true]),
                        Spec::col('product', 'Product', "coalesce(product.name, purchase_order_items.custom_product_name)"),
                        Spec::col('hsn_code', 'HSN', 'purchase_order_items.hsn_code'),
                        Spec::num('quantity', 'Qty', 'purchase_order_items.quantity'),
                        Spec::num('received', 'Received', 'purchase_order_items.received_quantity'),
                        Spec::money('unit_cost', 'Unit cost', 'purchase_order_items.unit_cost'),
                        Spec::percent('gst_rate', 'GST %', 'purchase_order_items.gst_rate'),
                        Spec::money('total', 'Line total', 'purchase_order_items.total'),
                        Spec::col('supplier', 'Supplier', 'supplier.name'),
                        Spec::badge('status', 'Status', 'purchase_orders.status'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'purchase_orders.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'purchase_orders.created_at', '<='),
                        Spec::sourceFilter('supplier_id', 'Supplier', 'suppliers', 'id', 'purchase_orders.supplier_id'),
                        Spec::sourceFilter('product_id', 'Product', 'products', 'id', 'purchase_order_items.product_id'),
                        Spec::textFilter('q', 'Search', ['purchase_orders.po_number', 'products.name', 'purchase_order_items.custom_product_name']),
                    ],
                    'summary' => [
                        Spec::summary('lines', 'Lines', 'count(*)'),
                        Spec::summary('quantity', 'Total qty', 'sum(purchase_order_items.quantity)'),
                        Spec::summary('total', 'Line value', 'sum(purchase_order_items.total)', 'money'),
                    ],
                    'order' => [Spec::order('po_number')],
                ],

                'tax-breakdown' => [
                    'label' => 'Purchase Tax Breakdown',
                    'description' => 'How purchases split across GST rates.',
                    'base' => 'purchase_order_items',
                    'joins' => [Spec::join('purchase_orders', 'purchase_order_items.purchase_order_id', 'purchase_orders.id')],
                    'group_by' => ['purchase_order_items.gst_rate'],
                    'columns' => [
                        Spec::percent('gst_rate', 'GST rate', 'purchase_order_items.gst_rate', ['primary' => true]),
                        Spec::num('lines', 'Lines', 'count(*)'),
                        Spec::num('quantity', 'Quantity', 'sum(purchase_order_items.quantity)'),
                        Spec::money('base', 'Taxable value', 'sum(purchase_order_items.total)'),
                    ],
                    'summary' => [
                        Spec::summary('rates', 'GST rates', 'count(distinct purchase_order_items.gst_rate)'),
                        Spec::summary('base', 'Taxable value', 'sum(purchase_order_items.total)', 'money'),
                    ],
                    'order' => [Spec::order('gst_rate')],
                    'chart' => Spec::chart('bar', 'gst_rate', 'base'),
                ],

                'receipt-progress' => [
                    'label' => 'Purchase Order Receipt Progress',
                    'description' => 'How much of each order has actually arrived.',
                    'base' => 'purchase_order_items',
                    'joins' => [Spec::join('purchase_orders', 'purchase_order_items.purchase_order_id', 'purchase_orders.id')],
                    'group_by' => ['purchase_orders.id', 'purchase_orders.po_number'],
                    'columns' => [
                        Spec::col('po_number', 'PO number', 'purchase_orders.po_number', ['primary' => true]),
                        Spec::num('ordered', 'Ordered qty', 'sum(purchase_order_items.quantity)'),
                        Spec::num('received', 'Received qty', 'sum(coalesce(purchase_order_items.received_quantity, 0))'),
                        Spec::num('pending', 'Pending qty', 'sum(purchase_order_items.quantity - coalesce(purchase_order_items.received_quantity, 0))'),
                        Spec::badge('status', 'Status', 'purchase_orders.status'),
                    ],
                    'summary' => [
                        Spec::summary('orders', 'Orders', 'count(distinct purchase_orders.id)'),
                        Spec::summary('ordered', 'Ordered qty', 'sum(purchase_order_items.quantity)'),
                        Spec::summary('received', 'Received qty', 'sum(coalesce(purchase_order_items.received_quantity, 0))'),
                    ],
                    'order' => [Spec::order('pending', 'desc')],
                ],

                'site-wise' => [
                    'label' => 'Purchase Orders by Site',
                    'description' => 'Which sites the company is spending on.',
                    'base' => 'purchase_orders',
                    'joins' => [Spec::join('sites', 'purchase_orders.site_id', 'sites.id', 'left', 'site')],
                    'group_by' => ['site.id', 'site.name'],
                    'columns' => [
                        Spec::col('site', 'Site', 'site.name', ['primary' => true]),
                        Spec::num('orders', 'Orders', 'count(*)'),
                        Spec::money('total', 'Value', 'sum(purchase_orders.total_amount)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'purchase_orders.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'purchase_orders.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('sites', 'Sites', 'count(distinct site.id)'),
                        Spec::summary('total', 'Total value', 'sum(purchase_orders.total_amount)', 'money'),
                    ],
                    'order' => [Spec::order('total', 'desc')],
                    'chart' => Spec::chart('bar', 'site', 'total'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Purchase quotations                                                 */
    /* ------------------------------------------------------------------ */

    private static function purchaseQuotations(): array
    {
        return [
            'label' => 'Purchase Quotations',
            'category' => 'Sales & Purchases',
            'permission' => 'purchase-quotations.view',
            'reports' => [

                'master' => [
                    'label' => 'Purchase Quotation Register',
                    'description' => 'Every supplier quotation on the table.',
                    'base' => 'supplier_quotations',
                    'joins' => [Spec::join('suppliers', 'supplier_quotations.supplier_id', 'suppliers.id', 'left', 'supplier')],
                    'columns' => [
                        Spec::col('quotation_number', 'Quotation', 'supplier_quotations.quotation_number', ['primary' => true]),
                        Spec::date('created', 'Raised on', 'supplier_quotations.created_at'),
                        Spec::col('supplier', 'Supplier', 'supplier.name'),
                        Spec::badge('status', 'Status', 'supplier_quotations.status'),
                        Spec::money('total', 'Total', 'supplier_quotations.total_amount'),
                        Spec::money('tax', 'Tax', 'supplier_quotations.tax_amount'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'supplier_quotations.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'supplier_quotations.created_at', '<='),
                        Spec::sourceMultiFilter('status', 'Status', 'supplier_quotations', 'status', 'supplier_quotations.status'),
                        Spec::sourceFilter('supplier_id', 'Supplier', 'suppliers', 'id', 'supplier_quotations.supplier_id'),
                        Spec::textFilter('q', 'Search', ['supplier_quotations.quotation_number', 'suppliers.name']),
                    ],
                    'summary' => [
                        Spec::summary('quotations', 'Quotations', 'count(*)'),
                        Spec::summary('total', 'Total value', 'sum(supplier_quotations.total_amount)', 'money'),
                    ],
                    'order' => [Spec::order('created', 'desc')],
                ],

                'by-supplier' => [
                    'label' => 'Purchase Quotations by Supplier',
                    'description' => 'Who is quoting, and for how much.',
                    'base' => 'supplier_quotations',
                    'joins' => [Spec::join('suppliers', 'supplier_quotations.supplier_id', 'suppliers.id', 'left', 'supplier')],
                    'group_by' => ['supplier.id', 'supplier.name'],
                    'columns' => [
                        Spec::col('supplier', 'Supplier', 'supplier.name', ['primary' => true]),
                        Spec::num('quotations', 'Quotations', 'count(*)'),
                        Spec::money('total', 'Quoted', 'sum(supplier_quotations.total_amount)'),
                        Spec::money('average', 'Average quote', 'round(avg(supplier_quotations.total_amount), 2)'),
                    ],
                    'summary' => [
                        Spec::summary('suppliers', 'Suppliers', 'count(distinct supplier.id)'),
                        Spec::summary('total', 'Total quoted', 'sum(supplier_quotations.total_amount)', 'money'),
                    ],
                    'order' => [Spec::order('total', 'desc')],
                    'chart' => Spec::chart('bar', 'supplier', 'total'),
                ],

                'by-status' => [
                    'label' => 'Purchase Quotations by Status',
                    'description' => 'Where the quotations sit.',
                    'base' => 'supplier_quotations',
                    'group_by' => ['supplier_quotations.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'supplier_quotations.status', ['primary' => true]),
                        Spec::num('quotations', 'Quotations', 'count(*)'),
                        Spec::money('total', 'Quoted', 'sum(supplier_quotations.total_amount)'),
                    ],
                    'summary' => [Spec::summary('quotations', 'Quotations', 'count(*)')],
                    'order' => [Spec::order('quotations', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'quotations'),
                ],

                'items' => [
                    'label' => 'Purchase Quotation Items',
                    'description' => 'The lines inside each supplier quotation.',
                    'base' => 'supplier_quotation_items',
                    'joins' => [
                        Spec::join('supplier_quotations', 'supplier_quotation_items.supplier_quotation_id', 'supplier_quotations.id'),
                        Spec::join('products', 'supplier_quotation_items.product_id', 'products.id', 'left', 'product'),
                    ],
                    'columns' => [
                        Spec::col('quotation_number', 'Quotation', 'supplier_quotations.quotation_number', ['primary' => true]),
                        Spec::col('product', 'Product', 'product.name'),
                        Spec::num('quantity', 'Qty', 'supplier_quotation_items.quantity'),
                        Spec::money('unit_price', 'Unit price', 'supplier_quotation_items.unit_price'),
                        Spec::money('total', 'Line total', 'supplier_quotation_items.total'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('product_id', 'Product', 'products', 'id', 'supplier_quotation_items.product_id'),
                        Spec::textFilter('q', 'Search', ['supplier_quotations.quotation_number', 'products.name']),
                    ],
                    'summary' => [
                        Spec::summary('lines', 'Lines', 'count(*)'),
                        Spec::summary('quantity', 'Total qty', 'sum(supplier_quotation_items.quantity)'),
                        Spec::summary('total', 'Line value', 'sum(supplier_quotation_items.total)', 'money'),
                    ],
                    'order' => [Spec::order('quotation_number')],
                ],

                'monthly-trend' => [
                    'label' => 'Purchase Quotation Trend',
                    'description' => 'How the quoted value moved month by month.',
                    'base' => 'supplier_quotations',
                    'group_by' => ['year(supplier_quotations.created_at)', 'month(supplier_quotations.created_at)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(supplier_quotations.created_at), '/', year(supplier_quotations.created_at))", ['primary' => true]),
                        Spec::num('quotations', 'Quotations', 'count(*)'),
                        Spec::money('total', 'Quoted', 'sum(supplier_quotations.total_amount)'),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('total', 'Total quoted', 'sum(supplier_quotations.total_amount)', 'money'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'total'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Purchase returns                                                    */
    /* ------------------------------------------------------------------ */

    private static function purchaseReturns(): array
    {
        return [
            'label' => 'Purchase Returns',
            'category' => 'Sales & Purchases',
            'permission' => 'purchase-returns.view',
            'reports' => [

                'master' => [
                    'label' => 'Purchase Return Register',
                    'description' => 'Everything sent back to a supplier.',
                    'base' => 'purchase_returns',
                    'joins' => [Spec::join('suppliers', 'purchase_returns.supplier_id', 'suppliers.id', 'left', 'supplier')],
                    'columns' => [
                        Spec::col('return_number', 'Return', 'purchase_returns.return_number', ['primary' => true]),
                        Spec::date('return_date', 'Return date', 'purchase_returns.return_date'),
                        Spec::col('supplier', 'Supplier', 'supplier.name'),
                        Spec::col('supplier_reference', 'Their reference', 'purchase_returns.supplier_reference'),
                        Spec::badge('status', 'Status', 'purchase_returns.status'),
                        Spec::col('reason', 'Reason', 'purchase_returns.return_reason'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'purchase_returns.return_date', '>='),
                        Spec::dateFilter('to', 'To', 'purchase_returns.return_date', '<='),
                        Spec::sourceMultiFilter('status', 'Status', 'purchase_returns', 'status', 'purchase_returns.status'),
                        Spec::sourceFilter('supplier_id', 'Supplier', 'suppliers', 'id', 'purchase_returns.supplier_id'),
                        Spec::textFilter('q', 'Search', ['purchase_returns.return_number', 'suppliers.name']),
                    ],
                    'summary' => [Spec::summary('returns', 'Returns', 'count(*)')],
                    'order' => [Spec::order('return_date', 'desc')],
                ],

                'by-supplier' => [
                    'label' => 'Purchase Returns by Supplier',
                    'description' => 'Who gets goods sent back.',
                    'base' => 'purchase_returns',
                    'joins' => [Spec::join('suppliers', 'purchase_returns.supplier_id', 'suppliers.id', 'left', 'supplier')],
                    'group_by' => ['supplier.id', 'supplier.name'],
                    'columns' => [
                        Spec::col('supplier', 'Supplier', 'supplier.name', ['primary' => true]),
                        Spec::num('returns', 'Returns', 'count(*)'),
                    ],
                    'summary' => [
                        Spec::summary('suppliers', 'Suppliers', 'count(distinct supplier.id)'),
                        Spec::summary('returns', 'Returns', 'count(*)'),
                    ],
                    'order' => [Spec::order('returns', 'desc')],
                    'chart' => Spec::chart('bar', 'supplier', 'returns'),
                ],

                'by-status' => [
                    'label' => 'Purchase Returns by Status',
                    'description' => 'How the returns are progressing.',
                    'base' => 'purchase_returns',
                    'group_by' => ['purchase_returns.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'purchase_returns.status', ['primary' => true]),
                        Spec::num('returns', 'Returns', 'count(*)'),
                    ],
                    'summary' => [Spec::summary('returns', 'Returns', 'count(*)')],
                    'order' => [Spec::order('returns', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'returns'),
                ],

                'items' => [
                    'label' => 'Purchase Return Items',
                    'description' => 'The products that went back, and how many.',
                    'base' => 'purchase_return_items',
                    'joins' => [
                        Spec::join('purchase_returns', 'purchase_return_items.purchase_return_id', 'purchase_returns.id'),
                        Spec::join('products', 'purchase_return_items.product_id', 'products.id', 'left', 'product'),
                    ],
                    'columns' => [
                        Spec::col('return_number', 'Return', 'purchase_returns.return_number', ['primary' => true]),
                        Spec::date('return_date', 'Date', 'purchase_returns.return_date'),
                        Spec::col('product', 'Product', 'product.name'),
                        Spec::num('quantity', 'Qty returned', 'purchase_return_items.return_quantity'),
                        Spec::badge('status', 'Status', 'purchase_returns.status'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('product_id', 'Product', 'products', 'id', 'purchase_return_items.product_id'),
                        Spec::textFilter('q', 'Search', ['purchase_returns.return_number', 'products.name']),
                    ],
                    'summary' => [
                        Spec::summary('lines', 'Lines', 'count(*)'),
                        Spec::summary('quantity', 'Total qty', 'sum(purchase_return_items.return_quantity)'),
                    ],
                    'order' => [Spec::order('return_date', 'desc')],
                ],

                'monthly-trend' => [
                    'label' => 'Purchase Return Trend',
                    'description' => 'How the return volume moved month by month.',
                    'base' => 'purchase_returns',
                    'group_by' => ['year(purchase_returns.return_date)', 'month(purchase_returns.return_date)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(purchase_returns.return_date), '/', year(purchase_returns.return_date))", ['primary' => true]),
                        Spec::num('returns', 'Returns', 'count(*)'),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('returns', 'Returns', 'count(*)'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'returns'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Goods receipt notes                                                 */
    /* ------------------------------------------------------------------ */

    private static function goodsReceiptNotes(): array
    {
        return [
            'label' => 'Goods Receipt Notes',
            'category' => 'Sales & Purchases',
            'permission' => 'goods-receipt-notes.view',
            'reports' => [

                'master' => [
                    'label' => 'Goods Receipt Register',
                    'description' => 'Every consignment that has been received.',
                    'base' => 'goods_receipt_notes',
                    'joins' => [
                        Spec::join('purchase_orders', 'goods_receipt_notes.purchase_order_id', 'purchase_orders.id', 'left', 'po'),
                        Spec::join('suppliers', 'purchase_orders.supplier_id', 'suppliers.id', 'left', 'supplier'),
                    ],
                    'columns' => [
                        Spec::col('grn_number', 'GRN', 'goods_receipt_notes.grn_number', ['primary' => true]),
                        Spec::date('receipt_date', 'Received on', 'goods_receipt_notes.receipt_date'),
                        Spec::col('po_number', 'PO number', 'po.po_number'),
                        Spec::col('supplier', 'Supplier', 'supplier.name'),
                        Spec::col('notes', 'Notes', 'goods_receipt_notes.notes'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'goods_receipt_notes.receipt_date', '>='),
                        Spec::dateFilter('to', 'To', 'goods_receipt_notes.receipt_date', '<='),
                        Spec::sourceFilter('supplier_id', 'Supplier', 'suppliers', 'id', 'purchase_orders.supplier_id'),
                        Spec::textFilter('q', 'Search', ['goods_receipt_notes.grn_number', 'po.po_number']),
                    ],
                    'summary' => [Spec::summary('receipts', 'Receipts', 'count(*)')],
                    'order' => [Spec::order('receipt_date', 'desc')],
                ],

                'items' => [
                    'label' => 'Goods Receipt Items',
                    'description' => 'The products received, and how many.',
                    'base' => 'grn_items',
                    'joins' => [
                        Spec::join('goods_receipt_notes', 'grn_items.goods_receipt_note_id', 'goods_receipt_notes.id'),
                        Spec::join('products', 'grn_items.product_id', 'products.id', 'left', 'product'),
                    ],
                    'columns' => [
                        Spec::col('grn_number', 'GRN', 'goods_receipt_notes.grn_number', ['primary' => true]),
                        Spec::date('receipt_date', 'Received on', 'goods_receipt_notes.receipt_date'),
                        Spec::col('product', 'Product', 'product.name'),
                        Spec::num('quantity', 'Qty received', 'grn_items.received_quantity'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'goods_receipt_notes.receipt_date', '>='),
                        Spec::dateFilter('to', 'To', 'goods_receipt_notes.receipt_date', '<='),
                        Spec::sourceFilter('product_id', 'Product', 'products', 'id', 'grn_items.product_id'),
                        Spec::textFilter('q', 'Search', ['goods_receipt_notes.grn_number', 'products.name']),
                    ],
                    'summary' => [
                        Spec::summary('lines', 'Lines', 'count(*)'),
                        Spec::summary('quantity', 'Total qty', 'sum(grn_items.received_quantity)'),
                    ],
                    'order' => [Spec::order('receipt_date', 'desc')],
                ],

                'by-supplier' => [
                    'label' => 'Goods Receipts by Supplier',
                    'description' => 'How much each supplier has delivered.',
                    'base' => 'grn_items',
                    'joins' => [
                        Spec::join('goods_receipt_notes', 'grn_items.goods_receipt_note_id', 'goods_receipt_notes.id'),
                        Spec::join('purchase_orders', 'goods_receipt_notes.purchase_order_id', 'purchase_orders.id', 'left', 'po'),
                        Spec::join('suppliers', 'purchase_orders.supplier_id', 'suppliers.id', 'left', 'supplier'),
                    ],
                    'group_by' => ['supplier.id', 'supplier.name'],
                    'columns' => [
                        Spec::col('supplier', 'Supplier', 'supplier.name', ['primary' => true]),
                        Spec::num('receipts', 'Receipts', 'count(distinct goods_receipt_notes.id)'),
                        Spec::num('lines', 'Lines', 'count(*)'),
                        Spec::num('quantity', 'Qty received', 'sum(grn_items.received_quantity)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'goods_receipt_notes.receipt_date', '>='),
                        Spec::dateFilter('to', 'To', 'goods_receipt_notes.receipt_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('suppliers', 'Suppliers', 'count(distinct supplier.id)'),
                        Spec::summary('quantity', 'Total qty', 'sum(grn_items.received_quantity)'),
                    ],
                    'order' => [Spec::order('quantity', 'desc')],
                    'chart' => Spec::chart('bar', 'supplier', 'quantity'),
                ],

                'monthly-trend' => [
                    'label' => 'Goods Receipt Trend',
                    'description' => 'How the inbound volume moved month by month.',
                    'base' => 'goods_receipt_notes',
                    'group_by' => ['year(goods_receipt_notes.receipt_date)', 'month(goods_receipt_notes.receipt_date)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(goods_receipt_notes.receipt_date), '/', year(goods_receipt_notes.receipt_date))", ['primary' => true]),
                        Spec::num('receipts', 'Receipts', 'count(*)'),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('receipts', 'Receipts', 'count(*)'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'receipts'),
                ],

                'pending-receipt' => [
                    'label' => 'Purchase Orders Awaiting Receipt',
                    'description' => 'Approved orders that are not fully in yet.',
                    'base' => 'purchase_order_items',
                    'joins' => [
                        Spec::join('purchase_orders', 'purchase_order_items.purchase_order_id', 'purchase_orders.id'),
                        Spec::join('suppliers', 'purchase_orders.supplier_id', 'suppliers.id', 'left', 'supplier'),
                    ],
                    'group_by' => ['purchase_orders.id', 'purchase_orders.po_number'],
                    'columns' => [
                        Spec::col('po_number', 'PO number', 'purchase_orders.po_number', ['primary' => true]),
                        Spec::col('supplier', 'Supplier', 'supplier.name'),
                        Spec::num('ordered', 'Ordered', 'sum(purchase_order_items.quantity)'),
                        Spec::num('received', 'Received', 'sum(coalesce(purchase_order_items.received_quantity, 0))'),
                        Spec::num('pending', 'Pending', 'sum(purchase_order_items.quantity - coalesce(purchase_order_items.received_quantity, 0))'),
                        Spec::badge('status', 'Status', 'purchase_orders.status'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('supplier_id', 'Supplier', 'suppliers', 'id', 'purchase_orders.supplier_id'),
                    ],
                    'summary' => [
                        Spec::summary('orders', 'Orders', 'count(distinct purchase_orders.id)'),
                        Spec::summary('pending', 'Pending qty', 'sum(purchase_order_items.quantity - coalesce(purchase_order_items.received_quantity, 0))'),
                    ],
                    'order' => [Spec::order('pending', 'desc')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Delivery notes                                                      */
    /* ------------------------------------------------------------------ */

    private static function deliveryNotes(): array
    {
        return [
            'label' => 'Delivery Notes',
            'category' => 'Sales & Purchases',
            'permission' => 'delivery-notes.view',
            'reports' => [

                'master' => [
                    'label' => 'Delivery Note Register',
                    'description' => 'Every consignment that has gone out.',
                    'base' => 'delivery_notes',
                    'joins' => [Spec::join('sales_orders', 'delivery_notes.sales_order_id', 'sales_orders.id', 'left', 'so')],
                    'columns' => [
                        Spec::col('delivery_number', 'Delivery', 'delivery_notes.delivery_number', ['primary' => true]),
                        Spec::date('delivery_date', 'Delivered on', 'delivery_notes.delivery_date'),
                        Spec::col('so_number', 'SO number', 'so.so_number'),
                        Spec::col('notes', 'Notes', 'delivery_notes.notes'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'delivery_notes.delivery_date', '>='),
                        Spec::dateFilter('to', 'To', 'delivery_notes.delivery_date', '<='),
                        Spec::textFilter('q', 'Search', ['delivery_notes.delivery_number', 'so.so_number']),
                    ],
                    'summary' => [Spec::summary('deliveries', 'Deliveries', 'count(*)')],
                    'order' => [Spec::order('delivery_date', 'desc')],
                ],

                'items' => [
                    'label' => 'Delivery Note Items',
                    'description' => 'The products that went out, and how many.',
                    'base' => 'delivery_note_items',
                    'joins' => [
                        Spec::join('delivery_notes', 'delivery_note_items.delivery_note_id', 'delivery_notes.id'),
                        Spec::join('products', 'delivery_note_items.product_id', 'products.id', 'left', 'product'),
                    ],
                    'columns' => [
                        Spec::col('delivery_number', 'Delivery', 'delivery_notes.delivery_number', ['primary' => true]),
                        Spec::date('delivery_date', 'Delivered on', 'delivery_notes.delivery_date'),
                        Spec::col('product', 'Product', 'product.name'),
                        Spec::num('quantity', 'Qty delivered', 'delivery_note_items.delivered_quantity'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'delivery_notes.delivery_date', '>='),
                        Spec::dateFilter('to', 'To', 'delivery_notes.delivery_date', '<='),
                        Spec::sourceFilter('product_id', 'Product', 'products', 'id', 'delivery_note_items.product_id'),
                        Spec::textFilter('q', 'Search', ['delivery_notes.delivery_number', 'products.name']),
                    ],
                    'summary' => [
                        Spec::summary('lines', 'Lines', 'count(*)'),
                        Spec::summary('quantity', 'Total qty', 'sum(delivery_note_items.delivered_quantity)'),
                    ],
                    'order' => [Spec::order('delivery_date', 'desc')],
                ],

                'by-customer' => [
                    'label' => 'Deliveries by Customer',
                    'description' => 'How much has been shipped to each customer.',
                    'base' => 'delivery_note_items',
                    'joins' => [
                        Spec::join('delivery_notes', 'delivery_note_items.delivery_note_id', 'delivery_notes.id'),
                        Spec::join('sales_orders', 'delivery_notes.sales_order_id', 'sales_orders.id', 'left', 'so'),
                        Spec::join('customers', 'sales_orders.customer_id', 'customers.id', 'left', 'customer'),
                    ],
                    'group_by' => ['customer.id', 'customer.name'],
                    'columns' => [
                        Spec::col('customer', 'Customer', 'customer.name', ['primary' => true]),
                        Spec::num('deliveries', 'Deliveries', 'count(distinct delivery_notes.id)'),
                        Spec::num('quantity', 'Qty delivered', 'sum(delivery_note_items.delivered_quantity)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'delivery_notes.delivery_date', '>='),
                        Spec::dateFilter('to', 'To', 'delivery_notes.delivery_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('customers', 'Customers', 'count(distinct customer.id)'),
                        Spec::summary('quantity', 'Total qty', 'sum(delivery_note_items.delivered_quantity)'),
                    ],
                    'order' => [Spec::order('quantity', 'desc')],
                    'chart' => Spec::chart('bar', 'customer', 'quantity'),
                ],

                'monthly-trend' => [
                    'label' => 'Delivery Trend',
                    'description' => 'How the outbound volume moved month by month.',
                    'base' => 'delivery_notes',
                    'group_by' => ['year(delivery_notes.delivery_date)', 'month(delivery_notes.delivery_date)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(delivery_notes.delivery_date), '/', year(delivery_notes.delivery_date))", ['primary' => true]),
                        Spec::num('deliveries', 'Deliveries', 'count(*)'),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('deliveries', 'Deliveries', 'count(*)'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'deliveries'),
                ],

                'pending-delivery' => [
                    'label' => 'Sales Orders Awaiting Delivery',
                    'description' => 'Approved orders that are not fully shipped.',
                    'base' => 'sales_order_items',
                    'joins' => [
                        Spec::join('sales_orders', 'sales_order_items.sales_order_id', 'sales_orders.id'),
                        Spec::join('customers', 'sales_orders.customer_id', 'customers.id', 'left', 'customer'),
                    ],
                    'group_by' => ['sales_orders.id', 'sales_orders.so_number'],
                    'columns' => [
                        Spec::col('so_number', 'SO number', 'sales_orders.so_number', ['primary' => true]),
                        Spec::col('customer', 'Customer', 'customer.name'),
                        Spec::num('ordered', 'Ordered', 'sum(sales_order_items.quantity)'),
                        Spec::num('delivered', 'Delivered', 'sum(coalesce(sales_order_items.delivered_quantity, 0))'),
                        Spec::num('pending', 'Pending', 'sum(sales_order_items.quantity - coalesce(sales_order_items.delivered_quantity, 0))'),
                        Spec::badge('status', 'Status', 'sales_orders.status'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('customer_id', 'Customer', 'customers', 'id', 'sales_orders.customer_id'),
                    ],
                    'summary' => [
                        Spec::summary('orders', 'Orders', 'count(distinct sales_orders.id)'),
                        Spec::summary('pending', 'Pending qty', 'sum(sales_order_items.quantity - coalesce(sales_order_items.delivered_quantity, 0))'),
                    ],
                    'order' => [Spec::order('pending', 'desc')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Sales orders                                                        */
    /* ------------------------------------------------------------------ */

    private static function salesOrders(): array
    {
        return [
            'label' => 'Sales Orders',
            'category' => 'Sales & Purchases',
            'permission' => 'sales-orders.view',
            'reports' => [

                'master' => [
                    'label' => 'Sales Order Register',
                    'description' => 'Every sales order, and the customer it sits with.',
                    'base' => 'sales_orders',
                    'joins' => [
                        Spec::join('customers', 'sales_orders.customer_id', 'customers.id'),
                        Spec::join('sites', 'sales_orders.site_id', 'sites.id', 'left', 'site'),
                    ],
                    'columns' => [
                        Spec::col('so_number', 'SO number', 'sales_orders.so_number', ['primary' => true]),
                        Spec::date('created', 'Raised on', 'sales_orders.created_at'),
                        Spec::col('customer', 'Customer', 'customers.name'),
                        Spec::col('site', 'Site', 'site.name'),
                        Spec::badge('status', 'Status', 'sales_orders.status'),
                        Spec::money('total', 'Total', 'sales_orders.total_amount'),
                        Spec::money('tax', 'Tax', 'sales_orders.tax_amount'),
                        Spec::money('discount', 'Discount', 'sales_orders.discount_amount'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'sales_orders.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'sales_orders.created_at', '<='),
                        Spec::sourceMultiFilter('status', 'Status', 'sales_orders', 'status', 'sales_orders.status'),
                        Spec::sourceFilter('customer_id', 'Customer', 'customers', 'id', 'sales_orders.customer_id'),
                        Spec::sourceFilter('site_id', 'Site', 'sites', 'id', 'sales_orders.site_id'),
                        Spec::textFilter('q', 'Search', ['sales_orders.so_number', 'customers.name']),
                    ],
                    'summary' => [
                        Spec::summary('orders', 'Orders', 'count(*)'),
                        Spec::summary('total', 'Order value', 'sum(sales_orders.total_amount)', 'money'),
                        Spec::summary('tax', 'Total tax', 'sum(sales_orders.tax_amount)', 'money'),
                    ],
                    'order' => [Spec::order('created', 'desc')],
                ],

                'by-customer' => [
                    'label' => 'Sales Orders by Customer',
                    'description' => 'Who is buying, and for how much.',
                    'base' => 'sales_orders',
                    'joins' => [Spec::join('customers', 'sales_orders.customer_id', 'customers.id')],
                    'group_by' => ['customers.id', 'customers.name'],
                    'columns' => [
                        Spec::col('customer', 'Customer', 'customers.name', ['primary' => true]),
                        Spec::num('orders', 'Orders', 'count(*)'),
                        Spec::money('total', 'Value', 'sum(sales_orders.total_amount)'),
                        Spec::money('tax', 'Tax', 'sum(sales_orders.tax_amount)'),
                        Spec::money('average', 'Average order', 'round(avg(sales_orders.total_amount), 2)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'sales_orders.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'sales_orders.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('customers', 'Customers', 'count(distinct customers.id)'),
                        Spec::summary('total', 'Total value', 'sum(sales_orders.total_amount)', 'money'),
                    ],
                    'order' => [Spec::order('total', 'desc')],
                    'chart' => Spec::chart('bar', 'customer', 'total'),
                ],

                'by-status' => [
                    'label' => 'Sales Orders by Status',
                    'description' => 'Where the orders sit in the approval cycle.',
                    'base' => 'sales_orders',
                    'group_by' => ['sales_orders.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'sales_orders.status', ['primary' => true]),
                        Spec::num('orders', 'Orders', 'count(*)'),
                        Spec::money('total', 'Value', 'sum(sales_orders.total_amount)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'sales_orders.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'sales_orders.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('statuses', 'Statuses', 'count(distinct sales_orders.status)'),
                        Spec::summary('orders', 'Orders', 'count(*)'),
                    ],
                    'order' => [Spec::order('orders', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'orders'),
                ],

                'monthly-trend' => [
                    'label' => 'Sales Order Monthly Trend',
                    'description' => 'How the order value moved month by month.',
                    'base' => 'sales_orders',
                    'group_by' => ['year(sales_orders.created_at)', 'month(sales_orders.created_at)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(sales_orders.created_at), '/', year(sales_orders.created_at))", ['primary' => true]),
                        Spec::num('orders', 'Orders', 'count(*)'),
                        Spec::money('total', 'Value', 'sum(sales_orders.total_amount)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'sales_orders.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'sales_orders.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('total', 'Total value', 'sum(sales_orders.total_amount)', 'money'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'total'),
                ],

                'items' => [
                    'label' => 'Sales Order Items',
                    'description' => 'The lines behind every sales order.',
                    'base' => 'sales_order_items',
                    'joins' => [
                        Spec::join('sales_orders', 'sales_order_items.sales_order_id', 'sales_orders.id'),
                        Spec::join('customers', 'sales_orders.customer_id', 'customers.id', 'left', 'customer'),
                        Spec::join('products', 'sales_order_items.product_id', 'products.id', 'left', 'product'),
                    ],
                    'columns' => [
                        Spec::col('so_number', 'SO number', 'sales_orders.so_number', ['primary' => true]),
                        Spec::col('product', 'Product', "coalesce(product.name, sales_order_items.custom_product_name)"),
                        Spec::col('hsn_code', 'HSN', 'sales_order_items.hsn_code'),
                        Spec::num('quantity', 'Qty', 'sales_order_items.quantity'),
                        Spec::num('delivered', 'Delivered', 'sales_order_items.delivered_quantity'),
                        Spec::money('unit_price', 'Unit price', 'sales_order_items.unit_price'),
                        Spec::percent('gst_rate', 'GST %', 'sales_order_items.gst_rate'),
                        Spec::money('discount', 'Discount', 'sales_order_items.discount'),
                        Spec::money('total', 'Line total', 'sales_order_items.total'),
                        Spec::col('customer', 'Customer', 'customer.name'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'sales_orders.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'sales_orders.created_at', '<='),
                        Spec::sourceFilter('customer_id', 'Customer', 'customers', 'id', 'sales_orders.customer_id'),
                        Spec::sourceFilter('product_id', 'Product', 'products', 'id', 'sales_order_items.product_id'),
                        Spec::textFilter('q', 'Search', ['sales_orders.so_number', 'products.name', 'sales_order_items.custom_product_name']),
                    ],
                    'summary' => [
                        Spec::summary('lines', 'Lines', 'count(*)'),
                        Spec::summary('quantity', 'Total qty', 'sum(sales_order_items.quantity)'),
                        Spec::summary('total', 'Line value', 'sum(sales_order_items.total)', 'money'),
                    ],
                    'order' => [Spec::order('so_number')],
                ],

                'tax-breakdown' => [
                    'label' => 'Sales Tax Breakdown',
                    'description' => 'How sales split across GST rates.',
                    'base' => 'sales_order_items',
                    'joins' => [Spec::join('sales_orders', 'sales_order_items.sales_order_id', 'sales_orders.id')],
                    'group_by' => ['sales_order_items.gst_rate'],
                    'columns' => [
                        Spec::percent('gst_rate', 'GST rate', 'sales_order_items.gst_rate', ['primary' => true]),
                        Spec::num('lines', 'Lines', 'count(*)'),
                        Spec::num('quantity', 'Quantity', 'sum(sales_order_items.quantity)'),
                        Spec::money('base', 'Taxable value', 'sum(sales_order_items.total)'),
                    ],
                    'summary' => [
                        Spec::summary('rates', 'GST rates', 'count(distinct sales_order_items.gst_rate)'),
                        Spec::summary('base', 'Taxable value', 'sum(sales_order_items.total)', 'money'),
                    ],
                    'order' => [Spec::order('gst_rate')],
                    'chart' => Spec::chart('bar', 'gst_rate', 'base'),
                ],

                'delivery-progress' => [
                    'label' => 'Sales Order Delivery Progress',
                    'description' => 'How much of each order has actually shipped.',
                    'base' => 'sales_order_items',
                    'joins' => [Spec::join('sales_orders', 'sales_order_items.sales_order_id', 'sales_orders.id')],
                    'group_by' => ['sales_orders.id', 'sales_orders.so_number'],
                    'columns' => [
                        Spec::col('so_number', 'SO number', 'sales_orders.so_number', ['primary' => true]),
                        Spec::num('ordered', 'Ordered qty', 'sum(sales_order_items.quantity)'),
                        Spec::num('delivered', 'Delivered qty', 'sum(coalesce(sales_order_items.delivered_quantity, 0))'),
                        Spec::num('pending', 'Pending qty', 'sum(sales_order_items.quantity - coalesce(sales_order_items.delivered_quantity, 0))'),
                        Spec::badge('status', 'Status', 'sales_orders.status'),
                    ],
                    'summary' => [
                        Spec::summary('orders', 'Orders', 'count(distinct sales_orders.id)'),
                        Spec::summary('ordered', 'Ordered qty', 'sum(sales_order_items.quantity)'),
                        Spec::summary('delivered', 'Delivered qty', 'sum(coalesce(sales_order_items.delivered_quantity, 0))'),
                    ],
                    'order' => [Spec::order('pending', 'desc')],
                ],

                'site-wise' => [
                    'label' => 'Sales Orders by Site',
                    'description' => 'Which sites the company is selling into.',
                    'base' => 'sales_orders',
                    'joins' => [Spec::join('sites', 'sales_orders.site_id', 'sites.id', 'left', 'site')],
                    'group_by' => ['site.id', 'site.name'],
                    'columns' => [
                        Spec::col('site', 'Site', 'site.name', ['primary' => true]),
                        Spec::num('orders', 'Orders', 'count(*)'),
                        Spec::money('total', 'Value', 'sum(sales_orders.total_amount)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'sales_orders.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'sales_orders.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('sites', 'Sites', 'count(distinct site.id)'),
                        Spec::summary('total', 'Total value', 'sum(sales_orders.total_amount)', 'money'),
                    ],
                    'order' => [Spec::order('total', 'desc')],
                    'chart' => Spec::chart('bar', 'site', 'total'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Sales quotations                                                    */
    /* ------------------------------------------------------------------ */

    private static function salesQuotations(): array
    {
        return [
            'label' => 'Sales Quotations',
            'category' => 'Sales & Purchases',
            'permission' => 'sales-quotations.view',
            'reports' => [

                'master' => [
                    'label' => 'Sales Quotation Register',
                    'description' => 'Every quotation sent out.',
                    'base' => 'customer_quotations',
                    'joins' => [Spec::join('customers', 'customer_quotations.customer_id', 'customers.id', 'left', 'customer')],
                    'columns' => [
                        Spec::col('quotation_number', 'Quotation', 'customer_quotations.quotation_number', ['primary' => true]),
                        Spec::date('created', 'Raised on', 'customer_quotations.created_at'),
                        Spec::col('customer', 'Customer', 'customer.name'),
                        Spec::badge('status', 'Status', 'customer_quotations.status'),
                        Spec::money('total', 'Total', 'customer_quotations.total_amount'),
                        Spec::money('tax', 'Tax', 'customer_quotations.tax_amount'),
                        Spec::money('discount', 'Discount', 'customer_quotations.discount_amount'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'customer_quotations.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'customer_quotations.created_at', '<='),
                        Spec::sourceMultiFilter('status', 'Status', 'customer_quotations', 'status', 'customer_quotations.status'),
                        Spec::sourceFilter('customer_id', 'Customer', 'customers', 'id', 'customer_quotations.customer_id'),
                        Spec::textFilter('q', 'Search', ['customer_quotations.quotation_number', 'customers.name']),
                    ],
                    'summary' => [
                        Spec::summary('quotations', 'Quotations', 'count(*)'),
                        Spec::summary('total', 'Total value', 'sum(customer_quotations.total_amount)', 'money'),
                    ],
                    'order' => [Spec::order('created', 'desc')],
                ],

                'by-customer' => [
                    'label' => 'Sales Quotations by Customer',
                    'description' => 'Who has been quoted, and for how much.',
                    'base' => 'customer_quotations',
                    'joins' => [Spec::join('customers', 'customer_quotations.customer_id', 'customers.id', 'left', 'customer')],
                    'group_by' => ['customer.id', 'customer.name'],
                    'columns' => [
                        Spec::col('customer', 'Customer', 'customer.name', ['primary' => true]),
                        Spec::num('quotations', 'Quotations', 'count(*)'),
                        Spec::money('total', 'Quoted', 'sum(customer_quotations.total_amount)'),
                        Spec::money('average', 'Average quote', 'round(avg(customer_quotations.total_amount), 2)'),
                    ],
                    'summary' => [
                        Spec::summary('customers', 'Customers', 'count(distinct customer.id)'),
                        Spec::summary('total', 'Total quoted', 'sum(customer_quotations.total_amount)', 'money'),
                    ],
                    'order' => [Spec::order('total', 'desc')],
                    'chart' => Spec::chart('bar', 'customer', 'total'),
                ],

                'by-status' => [
                    'label' => 'Sales Quotations by Status',
                    'description' => 'How the quotations are progressing.',
                    'base' => 'customer_quotations',
                    'group_by' => ['customer_quotations.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'customer_quotations.status', ['primary' => true]),
                        Spec::num('quotations', 'Quotations', 'count(*)'),
                        Spec::money('total', 'Quoted', 'sum(customer_quotations.total_amount)'),
                    ],
                    'summary' => [Spec::summary('quotations', 'Quotations', 'count(*)')],
                    'order' => [Spec::order('quotations', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'quotations'),
                ],

                'items' => [
                    'label' => 'Sales Quotation Items',
                    'description' => 'The lines inside each customer quotation.',
                    'base' => 'customer_quotation_items',
                    'joins' => [
                        Spec::join('customer_quotations', 'customer_quotation_items.customer_quotation_id', 'customer_quotations.id'),
                        Spec::join('products', 'customer_quotation_items.product_id', 'products.id', 'left', 'product'),
                    ],
                    'columns' => [
                        Spec::col('quotation_number', 'Quotation', 'customer_quotations.quotation_number', ['primary' => true]),
                        Spec::col('product', 'Product', 'product.name'),
                        Spec::num('quantity', 'Qty', 'customer_quotation_items.quantity'),
                        Spec::money('unit_price', 'Unit price', 'customer_quotation_items.unit_price'),
                        Spec::money('discount', 'Discount', 'customer_quotation_items.discount'),
                        Spec::money('total', 'Line total', 'customer_quotation_items.total'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('product_id', 'Product', 'products', 'id', 'customer_quotation_items.product_id'),
                        Spec::textFilter('q', 'Search', ['customer_quotations.quotation_number', 'products.name']),
                    ],
                    'summary' => [
                        Spec::summary('lines', 'Lines', 'count(*)'),
                        Spec::summary('quantity', 'Total qty', 'sum(customer_quotation_items.quantity)'),
                        Spec::summary('total', 'Line value', 'sum(customer_quotation_items.total)', 'money'),
                    ],
                    'order' => [Spec::order('quotation_number')],
                ],

                'monthly-trend' => [
                    'label' => 'Sales Quotation Trend',
                    'description' => 'How the quoted value moved month by month.',
                    'base' => 'customer_quotations',
                    'group_by' => ['year(customer_quotations.created_at)', 'month(customer_quotations.created_at)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(customer_quotations.created_at), '/', year(customer_quotations.created_at))", ['primary' => true]),
                        Spec::num('quotations', 'Quotations', 'count(*)'),
                        Spec::money('total', 'Quoted', 'sum(customer_quotations.total_amount)'),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('total', 'Total quoted', 'sum(customer_quotations.total_amount)', 'money'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'total'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Sales returns                                                       */
    /* ------------------------------------------------------------------ */

    private static function salesReturns(): array
    {
        return [
            'label' => 'Sales Returns',
            'category' => 'Sales & Purchases',
            'permission' => 'sales-returns.view',
            'reports' => [

                'master' => [
                    'label' => 'Sales Return Register',
                    'description' => 'Everything that came back from a customer.',
                    'base' => 'sales_returns',
                    'joins' => [Spec::join('customers', 'sales_returns.customer_id', 'customers.id', 'left', 'customer')],
                    'columns' => [
                        Spec::col('return_number', 'Return', 'sales_returns.return_number', ['primary' => true]),
                        Spec::date('return_date', 'Return date', 'sales_returns.return_date'),
                        Spec::col('customer', 'Customer', 'customer.name'),
                        Spec::col('customer_reference', 'Their reference', 'sales_returns.customer_reference'),
                        Spec::badge('status', 'Status', 'sales_returns.status'),
                        Spec::col('reason', 'Reason', 'sales_returns.return_reason'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'sales_returns.return_date', '>='),
                        Spec::dateFilter('to', 'To', 'sales_returns.return_date', '<='),
                        Spec::sourceMultiFilter('status', 'Status', 'sales_returns', 'status', 'sales_returns.status'),
                        Spec::sourceFilter('customer_id', 'Customer', 'customers', 'id', 'sales_returns.customer_id'),
                        Spec::textFilter('q', 'Search', ['sales_returns.return_number', 'customers.name']),
                    ],
                    'summary' => [Spec::summary('returns', 'Returns', 'count(*)')],
                    'order' => [Spec::order('return_date', 'desc')],
                ],

                'by-customer' => [
                    'label' => 'Sales Returns by Customer',
                    'description' => 'Which customers send goods back.',
                    'base' => 'sales_returns',
                    'joins' => [Spec::join('customers', 'sales_returns.customer_id', 'customers.id', 'left', 'customer')],
                    'group_by' => ['customer.id', 'customer.name'],
                    'columns' => [
                        Spec::col('customer', 'Customer', 'customer.name', ['primary' => true]),
                        Spec::num('returns', 'Returns', 'count(*)'),
                    ],
                    'summary' => [
                        Spec::summary('customers', 'Customers', 'count(distinct customer.id)'),
                        Spec::summary('returns', 'Returns', 'count(*)'),
                    ],
                    'order' => [Spec::order('returns', 'desc')],
                    'chart' => Spec::chart('bar', 'customer', 'returns'),
                ],

                'by-status' => [
                    'label' => 'Sales Returns by Status',
                    'description' => 'How the returns are progressing.',
                    'base' => 'sales_returns',
                    'group_by' => ['sales_returns.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'sales_returns.status', ['primary' => true]),
                        Spec::num('returns', 'Returns', 'count(*)'),
                    ],
                    'summary' => [Spec::summary('returns', 'Returns', 'count(*)')],
                    'order' => [Spec::order('returns', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'returns'),
                ],

                'items' => [
                    'label' => 'Sales Return Items',
                    'description' => 'The products that came back, and how many.',
                    'base' => 'sales_return_items',
                    'joins' => [
                        Spec::join('sales_returns', 'sales_return_items.sales_return_id', 'sales_returns.id'),
                        Spec::join('products', 'sales_return_items.product_id', 'products.id', 'left', 'product'),
                    ],
                    'columns' => [
                        Spec::col('return_number', 'Return', 'sales_returns.return_number', ['primary' => true]),
                        Spec::date('return_date', 'Date', 'sales_returns.return_date'),
                        Spec::col('product', 'Product', 'product.name'),
                        Spec::num('quantity', 'Qty returned', 'sales_return_items.return_quantity'),
                        Spec::badge('status', 'Status', 'sales_returns.status'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('product_id', 'Product', 'products', 'id', 'sales_return_items.product_id'),
                        Spec::textFilter('q', 'Search', ['sales_returns.return_number', 'products.name']),
                    ],
                    'summary' => [
                        Spec::summary('lines', 'Lines', 'count(*)'),
                        Spec::summary('quantity', 'Total qty', 'sum(sales_return_items.return_quantity)'),
                    ],
                    'order' => [Spec::order('return_date', 'desc')],
                ],

                'monthly-trend' => [
                    'label' => 'Sales Return Trend',
                    'description' => 'How the return volume moved month by month.',
                    'base' => 'sales_returns',
                    'group_by' => ['year(sales_returns.return_date)', 'month(sales_returns.return_date)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(sales_returns.return_date), '/', year(sales_returns.return_date))", ['primary' => true]),
                        Spec::num('returns', 'Returns', 'count(*)'),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('returns', 'Returns', 'count(*)'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'returns'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Invoices                                                            */
    /* ------------------------------------------------------------------ */

    private static function invoices(): array
    {
        return [
            'label' => 'Invoices',
            'category' => 'Sales & Purchases',
            'permission' => 'invoices.view',
            'reports' => [

                'master' => [
                    'label' => 'Invoice Register',
                    'description' => 'Every invoice raised, and how it stands.',
                    'base' => 'invoices',
                    'joins' => [Spec::join('customers', 'invoices.customer_id', 'customers.id', 'left', 'customer')],
                    'columns' => [
                        Spec::col('invoice_number', 'Invoice', 'invoices.invoice_number', ['primary' => true]),
                        Spec::date('issue_date', 'Issued on', 'invoices.issue_date'),
                        Spec::date('due_date', 'Due on', 'invoices.due_date'),
                        Spec::col('customer', 'Customer', 'customer.name'),
                        Spec::badge('status', 'Status', 'invoices.status'),
                        Spec::money('subtotal', 'Subtotal', 'invoices.subtotal'),
                        Spec::money('tax', 'Tax', 'invoices.cgst_total + invoices.sgst_total + invoices.igst_total'),
                        Spec::money('grand_total', 'Grand total', 'invoices.grand_total'),
                        Spec::money('paid', 'Paid', 'invoices.paid_amount'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Issued from', 'invoices.issue_date', '>='),
                        Spec::dateFilter('to', 'Issued to', 'invoices.issue_date', '<='),
                        Spec::sourceMultiFilter('status', 'Status', 'invoices', 'status', 'invoices.status'),
                        Spec::sourceFilter('customer_id', 'Customer', 'customers', 'id', 'invoices.customer_id'),
                        Spec::textFilter('q', 'Search', ['invoices.invoice_number', 'customers.name']),
                    ],
                    'summary' => [
                        Spec::summary('invoices', 'Invoices', 'count(*)'),
                        Spec::summary('total', 'Total invoiced', 'sum(invoices.grand_total)', 'money'),
                        Spec::summary('paid', 'Total received', 'sum(invoices.paid_amount)', 'money'),
                    ],
                    'order' => [Spec::order('issue_date', 'desc')],
                ],

                'by-customer' => [
                    'label' => 'Invoices by Customer',
                    'description' => 'What each customer has been billed.',
                    'base' => 'invoices',
                    'joins' => [Spec::join('customers', 'invoices.customer_id', 'customers.id', 'left', 'customer')],
                    'group_by' => ['customer.id', 'customer.name'],
                    'columns' => [
                        Spec::col('customer', 'Customer', 'customer.name', ['primary' => true]),
                        Spec::num('invoices', 'Invoices', 'count(*)'),
                        Spec::money('billed', 'Billed', 'sum(invoices.grand_total)'),
                        Spec::money('paid', 'Paid', 'sum(invoices.paid_amount)'),
                        Spec::money('average', 'Average invoice', 'round(avg(invoices.grand_total), 2)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Issued from', 'invoices.issue_date', '>='),
                        Spec::dateFilter('to', 'Issued to', 'invoices.issue_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('customers', 'Customers', 'count(distinct customer.id)'),
                        Spec::summary('billed', 'Total billed', 'sum(invoices.grand_total)', 'money'),
                    ],
                    'order' => [Spec::order('billed', 'desc')],
                    'chart' => Spec::chart('bar', 'customer', 'billed'),
                ],

                'by-status' => [
                    'label' => 'Invoices by Status',
                    'description' => 'Paid, unpaid, overdue - the state of the book.',
                    'base' => 'invoices',
                    'group_by' => ['invoices.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'invoices.status', ['primary' => true]),
                        Spec::num('invoices', 'Invoices', 'count(*)'),
                        Spec::money('total', 'Value', 'sum(invoices.grand_total)'),
                        Spec::money('paid', 'Paid', 'sum(invoices.paid_amount)'),
                    ],
                    'summary' => [
                        Spec::summary('statuses', 'Statuses', 'count(distinct invoices.status)'),
                        Spec::summary('invoices', 'Invoices', 'count(*)'),
                    ],
                    'order' => [Spec::order('invoices', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'invoices'),
                ],

                'aging' => [
                    'label' => 'Invoice Ageing',
                    'description' => 'Who owes what, and for how long it has been outstanding.',
                    'base' => 'invoices',
                    'joins' => [Spec::join('customers', 'invoices.customer_id', 'customers.id', 'left', 'customer')],
                    'group_by' => ['customer.id', 'customer.name'],
                    'columns' => [
                        Spec::col('customer', 'Customer', 'customer.name', ['primary' => true]),
                        Spec::money('not_due', 'Not due', "sum(case when ifnull(invoices.status, '') <> 'Paid' and (invoices.due_date is null or invoices.due_date >= curdate()) then coalesce(invoices.grand_total, 0) - coalesce(invoices.paid_amount, 0) else 0 end)"),
                        Spec::money('d1_30', '1-30 days', "sum(case when ifnull(invoices.status, '') <> 'Paid' and invoices.due_date < curdate() and invoices.due_date >= date_sub(curdate(), interval 30 day) then coalesce(invoices.grand_total, 0) - coalesce(invoices.paid_amount, 0) else 0 end)"),
                        Spec::money('d31_60', '31-60 days', "sum(case when ifnull(invoices.status, '') <> 'Paid' and invoices.due_date < date_sub(curdate(), interval 30 day) and invoices.due_date >= date_sub(curdate(), interval 60 day) then coalesce(invoices.grand_total, 0) - coalesce(invoices.paid_amount, 0) else 0 end)"),
                        Spec::money('d60_plus', 'Over 60 days', "sum(case when ifnull(invoices.status, '') <> 'Paid' and invoices.due_date < date_sub(curdate(), interval 60 day) then coalesce(invoices.grand_total, 0) - coalesce(invoices.paid_amount, 0) else 0 end)"),
                        Spec::money('total', 'Total outstanding', 'sum(coalesce(invoices.grand_total, 0) - coalesce(invoices.paid_amount, 0))'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Issued from', 'invoices.issue_date', '>='),
                        Spec::dateFilter('to', 'Issued to', 'invoices.issue_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('customers', 'Customers', 'count(distinct customer.id)'),
                        Spec::summary('outstanding', 'Total outstanding', 'sum(coalesce(invoices.grand_total, 0) - coalesce(invoices.paid_amount, 0))', 'money'),
                    ],
                    'order' => [Spec::order('total', 'desc')],
                    'chart' => Spec::chart('bar', 'customer', 'total'),
                ],

                'tax-breakdown' => [
                    'label' => 'Invoice Tax Breakdown',
                    'description' => 'How the billed value splits across tax heads.',
                    'base' => 'invoices',
                    'group_by' => ['invoices.gst_type'],
                    'columns' => [
                        Spec::col('gst_type', 'GST type', 'invoices.gst_type', ['primary' => true]),
                        Spec::num('invoices', 'Invoices', 'count(*)'),
                        Spec::money('subtotal', 'Taxable value', 'sum(invoices.subtotal)'),
                        Spec::money('cgst', 'CGST', 'sum(invoices.cgst_total)'),
                        Spec::money('sgst', 'SGST', 'sum(invoices.sgst_total)'),
                        Spec::money('igst', 'IGST', 'sum(invoices.igst_total)'),
                        Spec::money('grand_total', 'Total', 'sum(invoices.grand_total)'),
                    ],
                    'summary' => [
                        Spec::summary('types', 'GST types', 'count(distinct invoices.gst_type)'),
                        Spec::summary('total', 'Total invoiced', 'sum(invoices.grand_total)', 'money'),
                    ],
                    'order' => [Spec::order('grand_total', 'desc')],
                    'chart' => Spec::chart('bar', 'gst_type', 'grand_total'),
                ],

                'monthly-trend' => [
                    'label' => 'Invoice Monthly Trend',
                    'description' => 'How the billed value moved month by month.',
                    'base' => 'invoices',
                    'group_by' => ['year(invoices.issue_date)', 'month(invoices.issue_date)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(invoices.issue_date), '/', year(invoices.issue_date))", ['primary' => true]),
                        Spec::num('invoices', 'Invoices', 'count(*)'),
                        Spec::money('total', 'Invoiced', 'sum(invoices.grand_total)'),
                        Spec::money('paid', 'Received', 'sum(invoices.paid_amount)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Issued from', 'invoices.issue_date', '>='),
                        Spec::dateFilter('to', 'Issued to', 'invoices.issue_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('total', 'Total invoiced', 'sum(invoices.grand_total)', 'money'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'total'),
                ],

                'items' => [
                    'label' => 'Invoice Items',
                    'description' => 'The lines behind every invoice.',
                    'base' => 'invoice_items',
                    'joins' => [
                        Spec::join('invoices', 'invoice_items.invoice_id', 'invoices.id'),
                        Spec::join('customers', 'invoices.customer_id', 'customers.id', 'left', 'customer'),
                        Spec::join('products', 'invoice_items.product_id', 'products.id', 'left', 'product'),
                    ],
                    'columns' => [
                        Spec::col('invoice_number', 'Invoice', 'invoices.invoice_number', ['primary' => true]),
                        Spec::date('issue_date', 'Issued on', 'invoices.issue_date'),
                        Spec::col('product', 'Product', "coalesce(product.name, invoice_items.item_description)"),
                        Spec::col('hsn_code', 'HSN', 'invoice_items.hsn_code'),
                        Spec::num('quantity', 'Qty', 'invoice_items.quantity'),
                        Spec::money('unit_price', 'Unit price', 'invoice_items.unit_price'),
                        Spec::money('discount', 'Discount', 'invoice_items.discount'),
                        Spec::money('total', 'Line total', 'invoice_items.total'),
                        Spec::col('customer', 'Customer', 'customer.name'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Issued from', 'invoices.issue_date', '>='),
                        Spec::dateFilter('to', 'Issued to', 'invoices.issue_date', '<='),
                        Spec::sourceFilter('customer_id', 'Customer', 'customers', 'id', 'invoices.customer_id'),
                        Spec::sourceFilter('product_id', 'Product', 'products', 'id', 'invoice_items.product_id'),
                        Spec::textFilter('q', 'Search', ['invoices.invoice_number', 'products.name', 'invoice_items.item_description']),
                    ],
                    'summary' => [
                        Spec::summary('lines', 'Lines', 'count(*)'),
                        Spec::summary('quantity', 'Total qty', 'sum(invoice_items.quantity)'),
                        Spec::summary('total', 'Line value', 'sum(invoice_items.total)', 'money'),
                    ],
                    'order' => [Spec::order('issue_date', 'desc')],
                ],

                'outstanding' => [
                    'label' => 'Outstanding Invoices',
                    'description' => 'The invoices that have not been paid in full.',
                    'base' => 'invoices',
                    'joins' => [Spec::join('customers', 'invoices.customer_id', 'customers.id', 'left', 'customer')],
                    'constraints' => [
                        ['column' => 'coalesce(invoices.grand_total, 0) - coalesce(invoices.paid_amount, 0)', 'operator' => '>', 'value' => 0],
                    ],
                    'columns' => [
                        Spec::col('invoice_number', 'Invoice', 'invoices.invoice_number', ['primary' => true]),
                        Spec::date('issue_date', 'Issued on', 'invoices.issue_date'),
                        Spec::date('due_date', 'Due on', 'invoices.due_date'),
                        Spec::col('customer', 'Customer', 'customer.name'),
                        Spec::money('total', 'Total', 'invoices.grand_total'),
                        Spec::money('paid', 'Paid', 'invoices.paid_amount'),
                        Spec::money('balance', 'Balance', 'coalesce(invoices.grand_total, 0) - coalesce(invoices.paid_amount, 0)'),
                        Spec::badge('status', 'Status', 'invoices.status'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Issued from', 'invoices.issue_date', '>='),
                        Spec::dateFilter('to', 'Issued to', 'invoices.issue_date', '<='),
                        Spec::sourceFilter('customer_id', 'Customer', 'customers', 'id', 'invoices.customer_id'),
                    ],
                    'summary' => [
                        Spec::summary('invoices', 'Invoices', 'count(*)'),
                        Spec::summary('balance', 'Total outstanding', 'sum(coalesce(invoices.grand_total, 0) - coalesce(invoices.paid_amount, 0))', 'money'),
                    ],
                    'order' => [Spec::order('due_date')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Payments                                                            */
    /* ------------------------------------------------------------------ */

    private static function payments(): array
    {
        return [
            'label' => 'Payments',
            'category' => 'Sales & Purchases',
            'permission' => 'payments.view',
            'reports' => [

                'master' => [
                    'label' => 'Payment Register',
                    'description' => 'Every payment made and received.',
                    'base' => 'payments',
                    'columns' => [
                        Spec::date('payment_date', 'Date', 'payments.payment_date', ['primary' => true]),
                        Spec::money('amount', 'Amount', 'payments.amount'),
                        Spec::col('method', 'Method', 'payments.payment_method'),
                        Spec::col('type', 'Type', 'payments.type'),
                        Spec::col('reference', 'Reference', 'payments.reference_number'),
                        Spec::col('notes', 'Notes', 'payments.notes'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'payments.payment_date', '>='),
                        Spec::dateFilter('to', 'To', 'payments.payment_date', '<='),
                        Spec::sourceFilter('method', 'Method', 'payments', 'payment_method', 'payments.payment_method'),
                        Spec::sourceFilter('type', 'Type', 'payments', 'type', 'payments.type'),
                        Spec::textFilter('q', 'Search', ['payments.reference_number', 'payments.notes']),
                    ],
                    'summary' => [
                        Spec::summary('payments', 'Payments', 'count(*)'),
                        Spec::summary('amount', 'Total value', 'sum(payments.amount)', 'money'),
                    ],
                    'order' => [Spec::order('payment_date', 'desc')],
                ],

                'by-method' => [
                    'label' => 'Payments by Method',
                    'description' => 'How the money comes in and goes out.',
                    'base' => 'payments',
                    'group_by' => ['payments.payment_method'],
                    'columns' => [
                        Spec::col('method', 'Method', 'payments.payment_method', ['primary' => true]),
                        Spec::num('payments', 'Payments', 'count(*)'),
                        Spec::money('amount', 'Total', 'sum(payments.amount)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'payments.payment_date', '>='),
                        Spec::dateFilter('to', 'To', 'payments.payment_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('methods', 'Methods', 'count(distinct payments.payment_method)'),
                        Spec::summary('amount', 'Total', 'sum(payments.amount)', 'money'),
                    ],
                    'order' => [Spec::order('amount', 'desc')],
                    'chart' => Spec::chart('pie', 'method', 'amount'),
                ],

                'by-type' => [
                    'label' => 'Payments by Type',
                    'description' => 'Receipts against payments.',
                    'base' => 'payments',
                    'group_by' => ['payments.type'],
                    'columns' => [
                        Spec::col('type', 'Type', 'payments.type', ['primary' => true]),
                        Spec::num('payments', 'Payments', 'count(*)'),
                        Spec::money('amount', 'Total', 'sum(payments.amount)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'payments.payment_date', '>='),
                        Spec::dateFilter('to', 'To', 'payments.payment_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('types', 'Types', 'count(distinct payments.type)'),
                        Spec::summary('amount', 'Total', 'sum(payments.amount)', 'money'),
                    ],
                    'order' => [Spec::order('amount', 'desc')],
                    'chart' => Spec::chart('pie', 'type', 'amount'),
                ],

                'monthly-trend' => [
                    'label' => 'Payment Monthly Trend',
                    'description' => 'How the payment flow moved month by month.',
                    'base' => 'payments',
                    'group_by' => ['year(payments.payment_date)', 'month(payments.payment_date)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(payments.payment_date), '/', year(payments.payment_date))", ['primary' => true]),
                        Spec::num('payments', 'Payments', 'count(*)'),
                        Spec::money('amount', 'Total', 'sum(payments.amount)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'payments.payment_date', '>='),
                        Spec::dateFilter('to', 'To', 'payments.payment_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('amount', 'Total', 'sum(payments.amount)', 'money'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'amount'),
                ],

                'by-customer' => [
                    'label' => 'Payments by Customer',
                    'description' => 'Who paid, and how much.',
                    'base' => 'payments',
                    'joins' => [Spec::join('customers', 'payments.customer_id', 'customers.id', 'left', 'customer')],
                    'group_by' => ['customer.id', 'customer.name'],
                    'columns' => [
                        Spec::col('customer', 'Customer', 'customer.name', ['primary' => true]),
                        Spec::num('payments', 'Payments', 'count(*)'),
                        Spec::money('amount', 'Received', 'sum(payments.amount)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'payments.payment_date', '>='),
                        Spec::dateFilter('to', 'To', 'payments.payment_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('customers', 'Customers', 'count(distinct customer.id)'),
                        Spec::summary('amount', 'Received', 'sum(payments.amount)', 'money'),
                    ],
                    'order' => [Spec::order('amount', 'desc')],
                    'chart' => Spec::chart('bar', 'customer', 'amount'),
                ],

                'by-supplier' => [
                    'label' => 'Payments by Supplier',
                    'description' => 'Who was paid, and how much.',
                    'base' => 'payments',
                    'joins' => [Spec::join('suppliers', 'payments.supplier_id', 'suppliers.id', 'left', 'supplier')],
                    'group_by' => ['supplier.id', 'supplier.name'],
                    'columns' => [
                        Spec::col('supplier', 'Supplier', 'supplier.name', ['primary' => true]),
                        Spec::num('payments', 'Payments', 'count(*)'),
                        Spec::money('amount', 'Paid out', 'sum(payments.amount)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'payments.payment_date', '>='),
                        Spec::dateFilter('to', 'To', 'payments.payment_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('suppliers', 'Suppliers', 'count(distinct supplier.id)'),
                        Spec::summary('amount', 'Paid out', 'sum(payments.amount)', 'money'),
                    ],
                    'order' => [Spec::order('amount', 'desc')],
                    'chart' => Spec::chart('bar', 'supplier', 'amount'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Stock receipts                                                      */
    /* ------------------------------------------------------------------ */

    private static function stockReceipts(): array
    {
        return [
            'label' => 'Stock Receipts',
            'category' => 'Sales & Purchases',
            'permission' => 'stock-receipts.view',
            'reports' => [

                'master' => [
                    'label' => 'Stock Receipt Register',
                    'description' => 'Every receipt posted into stock.',
                    'base' => 'stock_receipts',
                    'columns' => [
                        Spec::col('receipt_no', 'Receipt', 'stock_receipts.receipt_no', ['primary' => true]),
                        Spec::date('receipt_date', 'Received on', 'stock_receipts.receipt_date'),
                        Spec::col('voucher_type', 'Voucher type', 'stock_receipts.voucher_type'),
                        Spec::badge('status', 'Status', 'stock_receipts.status'),
                        Spec::col('location_type', 'Location type', 'stock_receipts.location_type'),
                        Spec::col('supplier_bill_no', 'Supplier bill', 'stock_receipts.supplier_bill_no'),
                        Spec::col('notes', 'Notes', 'stock_receipts.notes'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'stock_receipts.receipt_date', '>='),
                        Spec::dateFilter('to', 'To', 'stock_receipts.receipt_date', '<='),
                        Spec::sourceMultiFilter('status', 'Status', 'stock_receipts', 'status', 'stock_receipts.status'),
                        Spec::sourceFilter('voucher_type', 'Voucher type', 'stock_receipts', 'voucher_type', 'stock_receipts.voucher_type'),
                        Spec::textFilter('q', 'Search', ['stock_receipts.receipt_no', 'stock_receipts.supplier_bill_no']),
                    ],
                    'summary' => [Spec::summary('receipts', 'Receipts', 'count(*)')],
                    'order' => [Spec::order('receipt_date', 'desc')],
                ],

                'items' => [
                    'label' => 'Stock Receipt Items',
                    'description' => 'The products received, and the stock they moved from and to.',
                    'base' => 'stock_receipt_items',
                    'joins' => [
                        Spec::join('stock_receipts', 'stock_receipt_items.stock_receipt_id', 'stock_receipts.id'),
                        Spec::join('products', 'stock_receipt_items.product_id', 'products.id', 'left', 'product'),
                    ],
                    'columns' => [
                        Spec::col('receipt_no', 'Receipt', 'stock_receipts.receipt_no', ['primary' => true]),
                        Spec::date('receipt_date', 'Received on', 'stock_receipts.receipt_date'),
                        Spec::col('product', 'Product', 'product.name'),
                        Spec::num('quantity', 'Quantity', 'stock_receipt_items.quantity'),
                        Spec::num('before', 'Stock before', 'stock_receipt_items.quantity_before'),
                        Spec::num('after', 'Stock after', 'stock_receipt_items.quantity_after'),
                        Spec::col('batch_no', 'Batch', 'stock_receipt_items.batch_no'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'stock_receipts.receipt_date', '>='),
                        Spec::dateFilter('to', 'To', 'stock_receipts.receipt_date', '<='),
                        Spec::sourceFilter('product_id', 'Product', 'products', 'id', 'stock_receipt_items.product_id'),
                        Spec::textFilter('q', 'Search', ['stock_receipts.receipt_no', 'products.name']),
                    ],
                    'summary' => [
                        Spec::summary('lines', 'Lines', 'count(*)'),
                        Spec::summary('quantity', 'Total qty', 'sum(stock_receipt_items.quantity)'),
                    ],
                    'order' => [Spec::order('receipt_date', 'desc')],
                ],

                'by-status' => [
                    'label' => 'Stock Receipts by Status',
                    'description' => 'How the receipts stand.',
                    'base' => 'stock_receipts',
                    'group_by' => ['stock_receipts.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'stock_receipts.status', ['primary' => true]),
                        Spec::num('receipts', 'Receipts', 'count(*)'),
                    ],
                    'summary' => [Spec::summary('receipts', 'Receipts', 'count(*)')],
                    'order' => [Spec::order('receipts', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'receipts'),
                ],

                'by-voucher-type' => [
                    'label' => 'Stock Receipts by Voucher Type',
                    'description' => 'Which document the stock came in against.',
                    'base' => 'stock_receipts',
                    'group_by' => ['stock_receipts.voucher_type'],
                    'columns' => [
                        Spec::col('voucher_type', 'Voucher type', 'stock_receipts.voucher_type', ['primary' => true]),
                        Spec::num('receipts', 'Receipts', 'count(*)'),
                    ],
                    'summary' => [Spec::summary('receipts', 'Receipts', 'count(*)')],
                    'order' => [Spec::order('receipts', 'desc')],
                    'chart' => Spec::chart('pie', 'voucher_type', 'receipts'),
                ],

                'monthly-trend' => [
                    'label' => 'Stock Receipt Trend',
                    'description' => 'How the inbound volume moved month by month.',
                    'base' => 'stock_receipts',
                    'group_by' => ['year(stock_receipts.receipt_date)', 'month(stock_receipts.receipt_date)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(stock_receipts.receipt_date), '/', year(stock_receipts.receipt_date))", ['primary' => true]),
                        Spec::num('receipts', 'Receipts', 'count(*)'),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('receipts', 'Receipts', 'count(*)'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'receipts'),
                ],
            ],
        ];
    }
}
