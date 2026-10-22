<?php

namespace App\Support\Reports\Modules;

use App\Support\Reports\Spec;

/**
 * Finance reports.
 *
 * The double-entry side of the business: the chart of accounts, the ledgers
 * that sit under it, the vouchers that move money, and the cash books the
 * petty cash and the bank are kept in.
 */
class Finance
{
    public static function modules(): array
    {
        return [
            'account-groups' => self::accountGroups(),
            'ledgers' => self::ledgers(),
            'financial-years' => self::financialYears(),
            'cash-vouchers' => self::cashVouchers(),
            'contra' => self::voucherModule('contra', 'Contra Vouchers', 'contra', 'contra.view'),
            'payment-vouchers' => self::voucherModule('payment-vouchers', 'Payment Vouchers', 'payment', 'payment-vouchers.view'),
            'receipt-vouchers' => self::voucherModule('receipt-vouchers', 'Receipt Vouchers', 'receipt', 'receipt-vouchers.view'),
            'journal' => self::voucherModule('journal', 'Journal Vouchers', 'journal', 'journal.view'),
            'purchase-vouchers' => self::voucherModule('purchase-vouchers', 'Purchase Vouchers', 'purchase', 'purchase-vouchers.view', items: true),
            'sales-vouchers' => self::voucherModule('sales-vouchers', 'Sales Vouchers', 'sales', 'sales-vouchers.view', items: true),
            'credit-notes' => self::voucherModule('credit-notes', 'Credit Notes', 'credit-note', 'credit-notes.view', items: true),
            'debit-notes' => self::voucherModule('debit-notes', 'Debit Notes', 'debit-note', 'debit-notes.view', items: true),
            'bank-reconciliation' => self::bankReconciliation(),
            'finance-reports' => self::financeReports(),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Shared voucher shapes                                                */
    /* ------------------------------------------------------------------ */

    /**
     * The standard report set for a voucher type.
     *
     * Every voucher type - contra, payment, receipt, journal, purchase, sales,
     * credit note, debit note - is the same document with a different key, so
     * they all answer the same questions: what was posted, against whom, in
     * which Dr/Cr lines, and how it lands over time. Writing that set once
     * keeps the eight modules consistent.
     *
     * @param  string  $key        the registry module key
     * @param  string  $label      the module label
     * @param  string  $type       the `voucher_type` value
     * @param  string  $permission the read permission the module needs
     * @param  bool    $items      whether the voucher carries product lines
     */
    protected static function voucherModule(string $key, string $label, string $type, string $permission, bool $items = false): array
    {
        $party = Spec::join('ledger_accounts', 'vouchers.party_ledger_id', 'ledger_accounts.id', 'left', 'party');
        $year = Spec::join('financial_years', 'vouchers.financial_year_id', 'financial_years.id');

        $reports = [

            'register' => [
                'label' => $label . ' Register',
                'description' => 'Every ' . strtolower($label) . ', with the party it was posted against.',
                'base' => 'vouchers',
                'joins' => [$party, $year],
                'constraints' => [
                    ['column' => 'vouchers.voucher_type', 'operator' => '=', 'value' => $type],
                ],
                'columns' => [
                    Spec::col('voucher_no', 'Voucher', 'vouchers.voucher_no', ['primary' => true]),
                    Spec::date('voucher_date', 'Date', 'vouchers.voucher_date'),
                    Spec::col('party', 'Party', 'party.name'),
                    Spec::col('reference_no', 'Reference', 'vouchers.reference_no'),
                    Spec::money('amount', 'Amount', 'vouchers.amount'),
                    Spec::money('taxable_total', 'Taxable', 'vouchers.taxable_total'),
                    Spec::money('tax_total', 'Tax', 'vouchers.tax_total'),
                    Spec::col('financial_year', 'FY', 'financial_years.name'),
                    Spec::badge('status', 'Status', 'vouchers.status'),
                ],
                'filters' => [
                    Spec::dateFilter('from', 'From', 'vouchers.voucher_date', '>='),
                    Spec::dateFilter('to', 'To', 'vouchers.voucher_date', '<='),
                    Spec::multiFilter('status', 'Status', 'vouchers.status'),
                    Spec::textFilter('q', 'Search', ['vouchers.voucher_no', 'vouchers.narration', 'party.name']),
                ],
                'summary' => [
                    Spec::summary('vouchers', 'Vouchers', 'count(*)'),
                    Spec::summary('amount', 'Total amount', 'sum(vouchers.amount)', 'money'),
                    Spec::summary('tax', 'Total tax', 'sum(vouchers.tax_total)', 'money'),
                ],
                'order' => [Spec::order('voucher_date', 'desc')],
            ],

            'by-party' => [
                'label' => $label . ' by Party',
                'description' => 'How much was posted against each party.',
                'base' => 'vouchers',
                'joins' => [$party],
                'constraints' => [
                    ['column' => 'vouchers.voucher_type', 'operator' => '=', 'value' => $type],
                ],
                'group_by' => ['party.id', 'party.name'],
                'columns' => [
                    Spec::col('party', 'Party', 'party.name', ['primary' => true]),
                    Spec::num('vouchers', 'Vouchers', 'count(*)'),
                    Spec::money('amount', 'Amount', 'sum(vouchers.amount)'),
                    Spec::money('tax', 'Tax', 'sum(vouchers.tax_total)'),
                    Spec::money('average', 'Average', 'round(avg(vouchers.amount), 2)'),
                ],
                'filters' => [
                    Spec::dateFilter('from', 'From', 'vouchers.voucher_date', '>='),
                    Spec::dateFilter('to', 'To', 'vouchers.voucher_date', '<='),
                ],
                'summary' => [
                    Spec::summary('parties', 'Parties', 'count(distinct party.id)'),
                    Spec::summary('amount', 'Total amount', 'sum(vouchers.amount)', 'money'),
                ],
                'order' => [Spec::order('amount', 'desc')],
                'chart' => Spec::chart('bar', 'party', 'amount'),
            ],

            'monthly-trend' => [
                'label' => $label . ' Monthly Trend',
                'description' => 'How the value moved month by month.',
                'base' => 'vouchers',
                'constraints' => [
                    ['column' => 'vouchers.voucher_type', 'operator' => '=', 'value' => $type],
                ],
                'group_by' => ['year(vouchers.voucher_date)', 'month(vouchers.voucher_date)'],
                'columns' => [
                    Spec::col('period', 'Period', "concat(month(vouchers.voucher_date), '/', year(vouchers.voucher_date))", ['primary' => true]),
                    Spec::num('vouchers', 'Vouchers', 'count(*)'),
                    Spec::money('amount', 'Amount', 'sum(vouchers.amount)'),
                    Spec::money('tax', 'Tax', 'sum(vouchers.tax_total)'),
                ],
                'filters' => [
                    Spec::dateFilter('from', 'From', 'vouchers.voucher_date', '>='),
                    Spec::dateFilter('to', 'To', 'vouchers.voucher_date', '<='),
                ],
                'summary' => [
                    Spec::summary('months', 'Months', 'count(*)'),
                    Spec::summary('amount', 'Total amount', 'sum(vouchers.amount)', 'money'),
                ],
                'order' => [Spec::order('period', 'desc')],
                'chart' => Spec::chart('line', 'period', 'amount'),
            ],

            'entries' => [
                'label' => $label . ' Entries',
                'description' => 'The Dr and Cr lines behind each voucher.',
                'base' => 'voucher_entries',
                'joins' => [
                    Spec::join('vouchers', 'voucher_entries.voucher_id', 'vouchers.id'),
                    Spec::join('ledger_accounts', 'voucher_entries.ledger_id', 'ledger_accounts.id', 'left', 'entry_ledger'),
                ],
                'constraints' => [
                    ['column' => 'vouchers.voucher_type', 'operator' => '=', 'value' => $type],
                ],
                'columns' => [
                    Spec::col('voucher_no', 'Voucher', 'vouchers.voucher_no'),
                    Spec::date('voucher_date', 'Date', 'vouchers.voucher_date'),
                    Spec::num('line_no', 'Line', 'voucher_entries.line_no'),
                    Spec::col('ledger', 'Ledger', 'entry_ledger.name', ['primary' => true]),
                    Spec::badge('entry_type', 'Dr / Cr', 'voucher_entries.entry_type'),
                    Spec::money('amount', 'Amount', 'voucher_entries.amount'),
                    Spec::col('narration', 'Narration', 'voucher_entries.narration'),
                ],
                'filters' => [
                    Spec::dateFilter('from', 'From', 'vouchers.voucher_date', '>='),
                    Spec::dateFilter('to', 'To', 'vouchers.voucher_date', '<='),
                    Spec::multiFilter('entry_type', 'Dr / Cr', 'voucher_entries.entry_type'),
                ],
                'summary' => [
                    Spec::summary('entries', 'Entries', 'count(*)'),
                    Spec::summary('debit', 'Total debit', "sum(case when voucher_entries.entry_type = 'Dr' then voucher_entries.amount else 0 end)", 'money'),
                    Spec::summary('credit', 'Total credit', "sum(case when voucher_entries.entry_type = 'Cr' then voucher_entries.amount else 0 end)", 'money'),
                ],
                'order' => [Spec::order('voucher_date', 'desc')],
            ],

            'bill-allocations' => [
                'label' => $label . ' Bill Allocations',
                'description' => 'Which bills each voucher was settled against.',
                'base' => 'voucher_bill_allocations',
                'joins' => [
                    Spec::join('vouchers', 'voucher_bill_allocations.voucher_id', 'vouchers.id'),
                    Spec::join('ledger_accounts', 'voucher_bill_allocations.ledger_id', 'ledger_accounts.id', 'left', 'bill_ledger'),
                ],
                'constraints' => [
                    ['column' => 'vouchers.voucher_type', 'operator' => '=', 'value' => $type],
                ],
                'columns' => [
                    Spec::col('voucher_no', 'Voucher', 'vouchers.voucher_no'),
                    Spec::date('voucher_date', 'Date', 'vouchers.voucher_date'),
                    Spec::col('bill_name', 'Bill', 'voucher_bill_allocations.bill_name', ['primary' => true]),
                    Spec::col('bill_type', 'Bill type', 'voucher_bill_allocations.bill_type'),
                    Spec::col('ledger', 'Ledger', 'bill_ledger.name'),
                    Spec::money('amount', 'Allocated', 'voucher_bill_allocations.amount'),
                ],
                'filters' => [
                    Spec::dateFilter('from', 'From', 'vouchers.voucher_date', '>='),
                    Spec::dateFilter('to', 'To', 'vouchers.voucher_date', '<='),
                ],
                'summary' => [
                    Spec::summary('allocations', 'Allocations', 'count(*)'),
                    Spec::summary('amount', 'Allocated', 'sum(voucher_bill_allocations.amount)', 'money'),
                ],
                'order' => [Spec::order('voucher_date', 'desc')],
            ],
        ];

        if ($items) {
            $reports['items'] = [
                'label' => $label . ' Items',
                'description' => 'The product lines on each voucher.',
                'base' => 'voucher_items',
                'joins' => [
                    Spec::join('vouchers', 'voucher_items.voucher_id', 'vouchers.id'),
                    Spec::join('products', 'voucher_items.product_id', 'products.id', 'left', 'item_product'),
                ],
                'constraints' => [
                    ['column' => 'vouchers.voucher_type', 'operator' => '=', 'value' => $type],
                ],
                'columns' => [
                    Spec::col('voucher_no', 'Voucher', 'vouchers.voucher_no'),
                    Spec::date('voucher_date', 'Date', 'vouchers.voucher_date'),
                    Spec::num('line_no', 'Line', 'voucher_items.line_no'),
                    Spec::col('product', 'Product', 'item_product.name', ['primary' => true]),
                    Spec::col('description', 'Description', 'voucher_items.item_description'),
                    Spec::col('hsn_code', 'HSN', 'voucher_items.hsn_code'),
                    Spec::num('quantity', 'Qty', 'voucher_items.quantity'),
                    Spec::money('rate', 'Rate', 'voucher_items.rate'),
                    Spec::money('taxable_amount', 'Taxable', 'voucher_items.taxable_amount'),
                    Spec::money('tax_amount', 'Tax', 'voucher_items.tax_amount'),
                    Spec::money('total_amount', 'Total', 'voucher_items.total_amount'),
                ],
                'filters' => [
                    Spec::dateFilter('from', 'From', 'vouchers.voucher_date', '>='),
                    Spec::dateFilter('to', 'To', 'vouchers.voucher_date', '<='),
                    Spec::textFilter('q', 'Search', ['voucher_items.item_description', 'voucher_items.hsn_code', 'item_product.name']),
                ],
                'summary' => [
                    Spec::summary('lines', 'Lines', 'count(*)'),
                    Spec::summary('taxable', 'Taxable value', 'sum(voucher_items.taxable_amount)', 'money'),
                    Spec::summary('tax', 'Tax', 'sum(voucher_items.tax_amount)', 'money'),
                    Spec::summary('total', 'Line total', 'sum(voucher_items.total_amount)', 'money'),
                ],
                'order' => [Spec::order('voucher_date', 'desc')],
            ];

            $reports['tax-breakdown'] = [
                'label' => $label . ' Tax Breakdown',
                'description' => 'How the tax was split across the rate slabs.',
                'base' => 'voucher_items',
                'joins' => [Spec::join('vouchers', 'voucher_items.voucher_id', 'vouchers.id')],
                'constraints' => [
                    ['column' => 'vouchers.voucher_type', 'operator' => '=', 'value' => $type],
                ],
                'group_by' => ['voucher_items.tax_rate'],
                'columns' => [
                    Spec::num('tax_rate', 'Rate %', 'voucher_items.tax_rate', ['primary' => true]),
                    Spec::num('lines', 'Lines', 'count(*)'),
                    Spec::money('taxable', 'Taxable value', 'sum(voucher_items.taxable_amount)'),
                    Spec::money('cgst', 'CGST', 'sum(voucher_items.cgst_amount)'),
                    Spec::money('sgst', 'SGST', 'sum(voucher_items.sgst_amount)'),
                    Spec::money('igst', 'IGST', 'sum(voucher_items.igst_amount)'),
                    Spec::money('tax', 'Total tax', 'sum(voucher_items.tax_amount)'),
                ],
                'filters' => [
                    Spec::dateFilter('from', 'From', 'vouchers.voucher_date', '>='),
                    Spec::dateFilter('to', 'To', 'vouchers.voucher_date', '<='),
                ],
                'summary' => [
                    Spec::summary('taxable', 'Taxable value', 'sum(voucher_items.taxable_amount)', 'money'),
                    Spec::summary('tax', 'Total tax', 'sum(voucher_items.tax_amount)', 'money'),
                ],
                'order' => [Spec::order('tax_rate')],
                'chart' => Spec::chart('bar', 'tax_rate', 'tax'),
            ];
        }

        return [
            'label' => $label,
            'category' => 'Finance',
            'permission' => $permission,
            'reports' => $reports,
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Chart of accounts                                                    */
    /* ------------------------------------------------------------------ */

    private static function accountGroups(): array
    {
        return [
            'label' => 'Account Groups',
            'category' => 'Finance',
            'permission' => 'account-groups.view',
            'reports' => [

                'master' => [
                    'label' => 'Account Group Master',
                    'description' => 'The chart of accounts - the groups every ledger hangs from.',
                    'base' => 'account_groups',
                    'columns' => [
                        Spec::col('name', 'Group', 'account_groups.name', ['primary' => true]),
                        Spec::badge('nature', 'Nature', 'account_groups.nature'),
                        Spec::col('parent', 'Parent', 'parent_groups.name'),
                        Spec::num('sort_order', 'Order', 'account_groups.sort_order'),
                        Spec::col('is_primary', 'Primary', 'account_groups.is_primary'),
                        Spec::col('affects_gross_profit', 'Affects GP', 'account_groups.affects_gross_profit'),
                        Spec::col('is_party_group', 'Party group', 'account_groups.is_party_group'),
                        Spec::col('is_system', 'System', 'account_groups.is_system'),
                    ],
                    'joins' => [
                        Spec::join('account_groups', 'account_groups.parent_id', 'parent_groups.id', 'left', 'parent_groups'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('nature', 'Nature', 'account_groups', 'nature'),
                        Spec::textFilter('q', 'Search', ['account_groups.name']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Groups', 'count(*)'),
                        Spec::summary('primary', 'Primary groups', 'sum(case when account_groups.is_primary = 1 then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('sort_order'), Spec::order('name')],
                ],

                'by-nature' => [
                    'label' => 'Groups by Nature',
                    'description' => 'How the chart splits between assets, liabilities, income and expenses.',
                    'base' => 'account_groups',
                    'group_by' => ['account_groups.nature'],
                    'columns' => [
                        Spec::badge('nature', 'Nature', 'account_groups.nature', ['primary' => true]),
                        Spec::num('groups', 'Groups', 'count(*)'),
                        Spec::num('primary_groups', 'Primary groups', 'sum(case when account_groups.is_primary = 1 then 1 else 0 end)'),
                    ],
                    'summary' => [Spec::summary('total', 'Groups', 'count(*)')],
                    'order' => [Spec::order('groups', 'desc')],
                    'chart' => Spec::chart('pie', 'nature', 'groups'),
                ],

                'group-hierarchy' => [
                    'label' => 'Group Hierarchy',
                    'description' => 'The parent-child structure of the chart of accounts.',
                    'base' => 'account_groups',
                    'joins' => [
                        Spec::join('account_groups', 'account_groups.parent_id', 'parent_groups.id', 'left', 'parent_groups'),
                    ],
                    'columns' => [
                        Spec::col('group', 'Group', 'account_groups.name', ['primary' => true]),
                        Spec::col('parent', 'Parent', 'parent_groups.name'),
                        Spec::badge('nature', 'Nature', 'account_groups.nature'),
                        Spec::num('sort_order', 'Order', 'account_groups.sort_order'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('nature', 'Nature', 'account_groups', 'nature'),
                    ],
                    'summary' => [Spec::summary('total', 'Groups', 'count(*)')],
                    'order' => [Spec::order('sort_order'), Spec::order('group')],
                ],

                'ledger-count-by-group' => [
                    'label' => 'Ledger Count by Group',
                    'description' => 'How many ledgers sit under each group.',
                    'base' => 'ledger_accounts',
                    'joins' => [Spec::join('account_groups', 'ledger_accounts.group_id', 'account_groups.id')],
                    'group_by' => ['account_groups.name', 'account_groups.nature'],
                    'columns' => [
                        Spec::col('group', 'Group', 'account_groups.name', ['primary' => true]),
                        Spec::badge('nature', 'Nature', 'account_groups.nature'),
                        Spec::num('ledgers', 'Ledgers', 'count(distinct ledger_accounts.id)'),
                        Spec::num('active', 'Active', "sum(case when ledger_accounts.status = 'active' then 1 else 0 end)"),
                    ],
                    'summary' => [
                        Spec::summary('groups', 'Groups', 'count(distinct account_groups.id)'),
                        Spec::summary('ledgers', 'Ledgers', 'count(distinct ledger_accounts.id)'),
                    ],
                    'order' => [Spec::order('ledgers', 'desc')],
                    'chart' => Spec::chart('bar', 'group', 'ledgers'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function ledgers(): array
    {
        $group = Spec::join('account_groups', 'ledger_accounts.group_id', 'account_groups.id');

        return [
            'label' => 'Ledgers',
            'category' => 'Finance',
            'permission' => 'ledgers.view',
            'reports' => [

                'master' => [
                    'label' => 'Ledger Master',
                    'description' => 'Every ledger account, with the group it belongs to.',
                    'base' => 'ledger_accounts',
                    'joins' => [$group],
                    'columns' => [
                        Spec::col('name', 'Ledger', 'ledger_accounts.name', ['primary' => true]),
                        Spec::col('group', 'Group', 'account_groups.name'),
                        Spec::badge('nature', 'Nature', 'account_groups.nature'),
                        Spec::money('opening_balance', 'Opening balance', 'ledger_accounts.opening_balance'),
                        Spec::col('opening_type', 'Dr / Cr', 'ledger_accounts.opening_type'),
                        Spec::col('is_bank', 'Bank', 'ledger_accounts.is_bank'),
                        Spec::col('is_cash', 'Cash', 'ledger_accounts.is_cash'),
                        Spec::badge('status', 'Status', 'ledger_accounts.status'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('status', 'Status', 'ledger_accounts', 'status'),
                        Spec::sourceFilter('nature', 'Nature', 'account_groups', 'nature'),
                        Spec::textFilter('q', 'Search', ['ledger_accounts.name', 'ledger_accounts.bank_account_number']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Ledgers', 'count(*)'),
                        Spec::summary('opening', 'Opening balance', 'sum(ledger_accounts.opening_balance)', 'money'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'bank-ledgers' => [
                    'label' => 'Bank Ledgers',
                    'description' => 'The bank accounts money actually sits in.',
                    'base' => 'ledger_accounts',
                    'joins' => [$group],
                    'constraints' => [
                        ['column' => 'ledger_accounts.is_bank', 'operator' => '=', 'value' => 1],
                    ],
                    'columns' => [
                        Spec::col('name', 'Ledger', 'ledger_accounts.name', ['primary' => true]),
                        Spec::col('group', 'Group', 'account_groups.name'),
                        Spec::col('bank_name', 'Bank', 'ledger_accounts.bank_name'),
                        Spec::col('account_number', 'Account no', 'ledger_accounts.bank_account_number'),
                        Spec::col('ifsc', 'IFSC', 'ledger_accounts.bank_ifsc'),
                        Spec::col('branch', 'Branch', 'ledger_accounts.bank_branch'),
                        Spec::money('opening_balance', 'Opening balance', 'ledger_accounts.opening_balance'),
                        Spec::badge('status', 'Status', 'ledger_accounts.status'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['ledger_accounts.name', 'ledger_accounts.bank_name']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Bank accounts', 'count(*)'),
                        Spec::summary('opening', 'Opening balance', 'sum(ledger_accounts.opening_balance)', 'money'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'cash-ledgers' => [
                    'label' => 'Cash Ledgers',
                    'description' => 'The petty-cash and till accounts.',
                    'base' => 'ledger_accounts',
                    'joins' => [$group],
                    'constraints' => [
                        ['column' => 'ledger_accounts.is_cash', 'operator' => '=', 'value' => 1],
                    ],
                    'columns' => [
                        Spec::col('name', 'Ledger', 'ledger_accounts.name', ['primary' => true]),
                        Spec::col('group', 'Group', 'account_groups.name'),
                        Spec::money('opening_balance', 'Opening balance', 'ledger_accounts.opening_balance'),
                        Spec::col('opening_type', 'Dr / Cr', 'ledger_accounts.opening_type'),
                        Spec::col('notes', 'Notes', 'ledger_accounts.notes'),
                        Spec::badge('status', 'Status', 'ledger_accounts.status'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['ledger_accounts.name']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Cash accounts', 'count(*)'),
                        Spec::summary('opening', 'Opening balance', 'sum(ledger_accounts.opening_balance)', 'money'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'party-ledgers' => [
                    'label' => 'Party Ledgers',
                    'description' => 'The customer and supplier accounts the business trades with.',
                    'base' => 'ledger_accounts',
                    'joins' => [$group],
                    'constraints' => [
                        ['column' => 'ledger_accounts.party_type', 'operator' => '!=', 'value' => null],
                    ],
                    'columns' => [
                        Spec::col('name', 'Party', 'ledger_accounts.name', ['primary' => true]),
                        Spec::col('party_type', 'Type', 'ledger_accounts.party_type'),
                        Spec::col('group', 'Group', 'account_groups.name'),
                        Spec::col('gst_number', 'GST no', 'ledger_accounts.gst_number'),
                        Spec::col('pan_number', 'PAN', 'ledger_accounts.pan_number'),
                        Spec::money('opening_balance', 'Opening balance', 'ledger_accounts.opening_balance'),
                        Spec::col('opening_type', 'Dr / Cr', 'ledger_accounts.opening_type'),
                        Spec::badge('status', 'Status', 'ledger_accounts.status'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('party_type', 'Type', 'ledger_accounts', 'party_type'),
                        Spec::textFilter('q', 'Search', ['ledger_accounts.name', 'ledger_accounts.gst_number']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Parties', 'count(*)'),
                        Spec::summary('opening', 'Opening balance', 'sum(ledger_accounts.opening_balance)', 'money'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'opening-balances' => [
                    'label' => 'Opening Balances',
                    'description' => 'What every ledger carried forward at the start of the books.',
                    'base' => 'ledger_accounts',
                    'joins' => [$group],
                    'group_by' => ['account_groups.name', 'account_groups.nature'],
                    'columns' => [
                        Spec::col('group', 'Group', 'account_groups.name', ['primary' => true]),
                        Spec::badge('nature', 'Nature', 'account_groups.nature'),
                        Spec::num('ledgers', 'Ledgers', 'count(*)'),
                        Spec::money('debit', 'Debit total', "sum(case when ledger_accounts.opening_type = 'Dr' then ledger_accounts.opening_balance else 0 end)"),
                        Spec::money('credit', 'Credit total', "sum(case when ledger_accounts.opening_type = 'Cr' then ledger_accounts.opening_balance else 0 end)"),
                    ],
                    'filters' => [
                        Spec::sourceFilter('nature', 'Nature', 'account_groups', 'nature'),
                    ],
                    'summary' => [
                        Spec::summary('ledgers', 'Ledgers', 'count(*)'),
                        Spec::summary('opening', 'Total opening', 'sum(ledger_accounts.opening_balance)', 'money'),
                    ],
                    'order' => [Spec::order('group')],
                    'chart' => Spec::chart('bar', 'group', 'ledgers'),
                ],

                'ledger-activity' => [
                    'label' => 'Ledger Activity',
                    'description' => 'How much has moved through each ledger.',
                    'base' => 'voucher_entries',
                    'joins' => [
                        Spec::join('ledger_accounts', 'voucher_entries.ledger_id', 'ledger_accounts.id', 'left', 'entry_ledger'),
                        Spec::join('account_groups', 'entry_ledger.group_id', 'account_groups.id', 'left', 'entry_group'),
                    ],
                    'group_by' => ['entry_ledger.id', 'entry_ledger.name'],
                    'columns' => [
                        Spec::col('ledger', 'Ledger', 'entry_ledger.name', ['primary' => true]),
                        Spec::col('group', 'Group', 'entry_group.name'),
                        Spec::num('entries', 'Entries', 'count(*)'),
                        Spec::money('debit', 'Debit', "sum(case when voucher_entries.entry_type = 'Dr' then voucher_entries.amount else 0 end)"),
                        Spec::money('credit', 'Credit', "sum(case when voucher_entries.entry_type = 'Cr' then voucher_entries.amount else 0 end)"),
                        Spec::money('net', 'Net', 'sum(case when voucher_entries.entry_type = \'Dr\' then voucher_entries.amount else -voucher_entries.amount end)'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('nature', 'Nature', 'account_groups', 'nature'),
                    ],
                    'summary' => [
                        Spec::summary('ledgers', 'Ledgers moved', 'count(distinct voucher_entries.ledger_id)'),
                        Spec::summary('entries', 'Entries', 'count(*)'),
                    ],
                    'order' => [Spec::order('entries', 'desc')],
                    'chart' => Spec::chart('bar', 'ledger', 'entries'),
                ],

                'inactive-ledgers' => [
                    'label' => 'Inactive Ledgers',
                    'description' => 'The accounts that have been put on hold.',
                    'base' => 'ledger_accounts',
                    'joins' => [$group],
                    'constraints' => [
                        ['column' => 'ledger_accounts.status', 'operator' => '=', 'value' => 'inactive'],
                    ],
                    'columns' => [
                        Spec::col('name', 'Ledger', 'ledger_accounts.name', ['primary' => true]),
                        Spec::col('group', 'Group', 'account_groups.name'),
                        Spec::money('opening_balance', 'Opening balance', 'ledger_accounts.opening_balance'),
                        Spec::col('notes', 'Notes', 'ledger_accounts.notes'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['ledger_accounts.name']),
                    ],
                    'summary' => [Spec::summary('total', 'Inactive ledgers', 'count(*)')],
                    'order' => [Spec::order('name')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function financialYears(): array
    {
        return [
            'label' => 'Financial Years',
            'category' => 'Finance',
            'permission' => 'financial-years.view',
            'reports' => [

                'master' => [
                    'label' => 'Financial Year Master',
                    'description' => 'The accounting periods the books are divided into.',
                    'base' => 'financial_years',
                    'columns' => [
                        Spec::col('name', 'Year', 'financial_years.name', ['primary' => true]),
                        Spec::date('start_date', 'Starts', 'financial_years.start_date'),
                        Spec::date('end_date', 'Ends', 'financial_years.end_date'),
                        Spec::num('days', 'Days', 'datediff(financial_years.end_date, financial_years.start_date)'),
                        Spec::col('is_active', 'Active', 'financial_years.is_active'),
                        Spec::col('is_closed', 'Closed', 'financial_years.is_closed'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['financial_years.name']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Years', 'count(*)'),
                        Spec::summary('active', 'Active', 'sum(case when financial_years.is_active = 1 then 1 else 0 end)'),
                        Spec::summary('closed', 'Closed', 'sum(case when financial_years.is_closed = 1 then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('start_date', 'desc')],
                ],

                'voucher-volume' => [
                    'label' => 'Voucher Volume by Financial Year',
                    'description' => 'How much was posted in each year.',
                    'base' => 'vouchers',
                    'joins' => [Spec::join('financial_years', 'vouchers.financial_year_id', 'financial_years.id')],
                    'group_by' => ['financial_years.name'],
                    'columns' => [
                        Spec::col('financial_year', 'Year', 'financial_years.name', ['primary' => true]),
                        Spec::num('vouchers', 'Vouchers', 'count(*)'),
                        Spec::money('amount', 'Amount', 'sum(vouchers.amount)'),
                        Spec::money('tax', 'Tax', 'sum(vouchers.tax_total)'),
                    ],
                    'summary' => [
                        Spec::summary('years', 'Years', 'count(distinct financial_years.id)'),
                        Spec::summary('amount', 'Amount', 'sum(vouchers.amount)', 'money'),
                    ],
                    'order' => [Spec::order('financial_year')],
                    'chart' => Spec::chart('bar', 'financial_year', 'amount'),
                ],

                'year-close-status' => [
                    'label' => 'Year Close Status',
                    'description' => 'Which years are open for posting and which are locked.',
                    'base' => 'financial_years',
                    'columns' => [
                        Spec::col('name', 'Year', 'financial_years.name', ['primary' => true]),
                        Spec::date('start_date', 'Starts', 'financial_years.start_date'),
                        Spec::date('end_date', 'Ends', 'financial_years.end_date'),
                        Spec::col('state', 'State', "case when financial_years.is_closed = 1 then 'Closed' when financial_years.is_active = 1 then 'Active' else 'Open' end"),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['financial_years.name']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Years', 'count(*)'),
                        Spec::summary('active', 'Active', 'sum(case when financial_years.is_active = 1 then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('start_date', 'desc')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function cashVouchers(): array
    {
        $employee = Spec::join('employees', 'cash_vouchers.employee_id', 'employees.id');
        $supplier = Spec::join('suppliers', 'cash_vouchers.supplier_id', 'suppliers.id');
        $site = Spec::join('sites', 'cash_vouchers.site_id', 'sites.id');

        return [
            'label' => 'Cash Vouchers',
            'category' => 'Finance',
            'permission' => 'cash-vouchers.view',
            'reports' => [

                'register' => [
                    'label' => 'Cash Voucher Register',
                    'description' => 'Every petty-cash payment the company made.',
                    'base' => 'cash_vouchers',
                    'joins' => [$employee, $supplier, $site],
                    'columns' => [
                        Spec::col('voucher_no', 'Voucher', 'cash_vouchers.voucher_no', ['primary' => true]),
                        Spec::date('voucher_date', 'Date', 'cash_vouchers.voucher_date'),
                        Spec::col('paid_to', 'Paid to', 'cash_vouchers.paid_to'),
                        Spec::col('purpose', 'Purpose', 'cash_vouchers.purpose'),
                        Spec::col('category', 'Category', 'cash_vouchers.category'),
                        Spec::money('amount', 'Amount', 'cash_vouchers.amount'),
                        Spec::col('payment_mode', 'Mode', 'cash_vouchers.payment_mode'),
                        Spec::badge('status', 'Status', 'cash_vouchers.status'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'cash_vouchers.voucher_date', '>='),
                        Spec::dateFilter('to', 'To', 'cash_vouchers.voucher_date', '<='),
                        Spec::multiFilter('status', 'Status', 'cash_vouchers.status'),
                        Spec::textFilter('q', 'Search', ['cash_vouchers.voucher_no', 'cash_vouchers.paid_to', 'cash_vouchers.purpose']),
                    ],
                    'summary' => [
                        Spec::summary('vouchers', 'Vouchers', 'count(*)'),
                        Spec::summary('amount', 'Total paid', 'sum(cash_vouchers.amount)', 'money'),
                    ],
                    'order' => [Spec::order('voucher_date', 'desc')],
                ],

                'by-category' => [
                    'label' => 'Cash Spend by Category',
                    'description' => 'Where the petty cash actually goes.',
                    'base' => 'cash_vouchers',
                    'group_by' => ['cash_vouchers.category'],
                    'columns' => [
                        Spec::col('category', 'Category', 'cash_vouchers.category', ['primary' => true]),
                        Spec::num('vouchers', 'Vouchers', 'count(*)'),
                        Spec::money('amount', 'Amount', 'sum(cash_vouchers.amount)'),
                        Spec::money('average', 'Average', 'round(avg(cash_vouchers.amount), 2)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'cash_vouchers.voucher_date', '>='),
                        Spec::dateFilter('to', 'To', 'cash_vouchers.voucher_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('categories', 'Categories', 'count(distinct cash_vouchers.category)'),
                        Spec::summary('amount', 'Total paid', 'sum(cash_vouchers.amount)', 'money'),
                    ],
                    'order' => [Spec::order('amount', 'desc')],
                    'chart' => Spec::chart('pie', 'category', 'amount'),
                ],

                'by-payment-mode' => [
                    'label' => 'Cash Spend by Payment Mode',
                    'description' => 'How the money was handed over.',
                    'base' => 'cash_vouchers',
                    'group_by' => ['cash_vouchers.payment_mode'],
                    'columns' => [
                        Spec::col('payment_mode', 'Mode', 'cash_vouchers.payment_mode', ['primary' => true]),
                        Spec::num('vouchers', 'Vouchers', 'count(*)'),
                        Spec::money('amount', 'Amount', 'sum(cash_vouchers.amount)'),
                    ],
                    'summary' => [
                        Spec::summary('amount', 'Total paid', 'sum(cash_vouchers.amount)', 'money'),
                    ],
                    'order' => [Spec::order('amount', 'desc')],
                    'chart' => Spec::chart('bar', 'payment_mode', 'amount'),
                ],

                'by-site' => [
                    'label' => 'Cash Spend by Site',
                    'description' => 'Which site burnt the most petty cash.',
                    'base' => 'cash_vouchers',
                    'joins' => [$site],
                    'group_by' => ['sites.name'],
                    'columns' => [
                        Spec::col('site', 'Site', 'sites.name', ['primary' => true]),
                        Spec::num('vouchers', 'Vouchers', 'count(*)'),
                        Spec::money('amount', 'Amount', 'sum(cash_vouchers.amount)'),
                        Spec::money('average', 'Average', 'round(avg(cash_vouchers.amount), 2)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'cash_vouchers.voucher_date', '>='),
                        Spec::dateFilter('to', 'To', 'cash_vouchers.voucher_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('sites', 'Sites', 'count(distinct sites.id)'),
                        Spec::summary('amount', 'Total paid', 'sum(cash_vouchers.amount)', 'money'),
                    ],
                    'order' => [Spec::order('amount', 'desc')],
                    'chart' => Spec::chart('bar', 'site', 'amount'),
                ],

                'by-beneficiary' => [
                    'label' => 'Cash Spend by Beneficiary',
                    'description' => 'Who received the money, and how much.',
                    'base' => 'cash_vouchers',
                    'group_by' => ['cash_vouchers.paid_to'],
                    'columns' => [
                        Spec::col('paid_to', 'Paid to', 'cash_vouchers.paid_to', ['primary' => true]),
                        Spec::num('vouchers', 'Vouchers', 'count(*)'),
                        Spec::money('amount', 'Amount', 'sum(cash_vouchers.amount)'),
                        Spec::date('last_paid', 'Last paid', 'max(cash_vouchers.voucher_date)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'cash_vouchers.voucher_date', '>='),
                        Spec::dateFilter('to', 'To', 'cash_vouchers.voucher_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('beneficiaries', 'Beneficiaries', 'count(distinct cash_vouchers.paid_to)'),
                        Spec::summary('amount', 'Total paid', 'sum(cash_vouchers.amount)', 'money'),
                    ],
                    'order' => [Spec::order('amount', 'desc')],
                ],

                'employee-claims' => [
                    'label' => 'Employee Reimbursements',
                    'description' => 'The petty-cash claims raised by employees.',
                    'base' => 'cash_vouchers',
                    'joins' => [$employee],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::date('voucher_date', 'Date', 'cash_vouchers.voucher_date'),
                        Spec::col('purpose', 'Purpose', 'cash_vouchers.purpose'),
                        Spec::money('amount', 'Amount', 'cash_vouchers.amount'),
                        Spec::badge('status', 'Status', 'cash_vouchers.status'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'cash_vouchers.voucher_date', '>='),
                        Spec::dateFilter('to', 'To', 'cash_vouchers.voucher_date', '<='),
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id']),
                    ],
                    'summary' => [
                        Spec::summary('claims', 'Claims', 'count(*)'),
                        Spec::summary('amount', 'Total claimed', 'sum(cash_vouchers.amount)', 'money'),
                    ],
                    'order' => [Spec::order('voucher_date', 'desc')],
                ],

                'supplier-payments' => [
                    'label' => 'Cash Payments to Suppliers',
                    'description' => 'Petty-cash payments made out to suppliers.',
                    'base' => 'cash_vouchers',
                    'joins' => [$supplier],
                    'columns' => [
                        Spec::col('voucher_no', 'Voucher', 'cash_vouchers.voucher_no'),
                        Spec::date('voucher_date', 'Date', 'cash_vouchers.voucher_date'),
                        Spec::col('supplier', 'Supplier', 'suppliers.name', ['primary' => true]),
                        Spec::col('purpose', 'Purpose', 'cash_vouchers.purpose'),
                        Spec::money('amount', 'Amount', 'cash_vouchers.amount'),
                        Spec::col('reference_no', 'Reference', 'cash_vouchers.reference_no'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'cash_vouchers.voucher_date', '>='),
                        Spec::dateFilter('to', 'To', 'cash_vouchers.voucher_date', '<='),
                        Spec::textFilter('q', 'Search', ['suppliers.name', 'cash_vouchers.paid_to']),
                    ],
                    'summary' => [
                        Spec::summary('payments', 'Payments', 'count(*)'),
                        Spec::summary('amount', 'Total paid', 'sum(cash_vouchers.amount)', 'money'),
                    ],
                    'order' => [Spec::order('voucher_date', 'desc')],
                ],

                'pending-approval' => [
                    'label' => 'Cash Vouchers Awaiting Approval',
                    'description' => 'The claims still sitting in the approval queue.',
                    'base' => 'cash_vouchers',
                    'joins' => [$employee],
                    'filters' => [
                        Spec::multiFilter('status', 'Status', 'cash_vouchers.status', ['Pending', 'Submitted']),
                        Spec::dateFilter('from', 'From', 'cash_vouchers.voucher_date', '>='),
                        Spec::dateFilter('to', 'To', 'cash_vouchers.voucher_date', '<='),
                    ],
                    'columns' => [
                        Spec::col('voucher_no', 'Voucher', 'cash_vouchers.voucher_no', ['primary' => true]),
                        Spec::date('voucher_date', 'Date', 'cash_vouchers.voucher_date'),
                        Spec::col('paid_to', 'Paid to', 'cash_vouchers.paid_to'),
                        Spec::col('name', 'Employee', 'employees.full_name'),
                        Spec::money('amount', 'Amount', 'cash_vouchers.amount'),
                        Spec::badge('status', 'Status', 'cash_vouchers.status'),
                    ],
                    'summary' => [
                        Spec::summary('pending', 'Pending', 'count(*)'),
                        Spec::summary('amount', 'Amount', 'sum(cash_vouchers.amount)', 'money'),
                    ],
                    'order' => [Spec::order('voucher_date', 'desc')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function bankReconciliation(): array
    {
        $entryLedger = Spec::join('ledger_accounts', 'voucher_entries.ledger_id', 'ledger_accounts.id', 'left', 'entry_ledger');

        return [
            'label' => 'Bank Reconciliation',
            'category' => 'Finance',
            'permission' => 'bank-reconciliation.view',
            'reports' => [

                'reconciled-entries' => [
                    'label' => 'Reconciled Entries',
                    'description' => 'The bank entries that have been matched off.',
                    'base' => 'voucher_entries',
                    'joins' => [
                        Spec::join('vouchers', 'voucher_entries.voucher_id', 'vouchers.id'),
                        $entryLedger,
                    ],
                    'constraints' => [
                        ['column' => 'voucher_entries.reconciled_at', 'operator' => '!=', 'value' => null],
                    ],
                    'columns' => [
                        Spec::col('voucher_no', 'Voucher', 'vouchers.voucher_no'),
                        Spec::date('voucher_date', 'Date', 'vouchers.voucher_date'),
                        Spec::col('ledger', 'Ledger', 'entry_ledger.name', ['primary' => true]),
                        Spec::badge('entry_type', 'Dr / Cr', 'voucher_entries.entry_type'),
                        Spec::money('amount', 'Amount', 'voucher_entries.amount'),
                        Spec::date('reconciled_at', 'Reconciled', 'voucher_entries.reconciled_at'),
                        Spec::col('reconciliation_note', 'Note', 'voucher_entries.reconciliation_note'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'voucher_entries.reconciled_at', '>='),
                        Spec::dateFilter('to', 'To', 'voucher_entries.reconciled_at', '<='),
                        Spec::textFilter('q', 'Search', ['vouchers.voucher_no', 'entry_ledger.name']),
                    ],
                    'summary' => [
                        Spec::summary('entries', 'Reconciled', 'count(*)'),
                        Spec::summary('amount', 'Amount', 'sum(voucher_entries.amount)', 'money'),
                    ],
                    'order' => [Spec::order('reconciled_at', 'desc')],
                ],

                'unreconciled-entries' => [
                    'label' => 'Unreconciled Entries',
                    'description' => 'The bank entries still waiting to be matched.',
                    'base' => 'voucher_entries',
                    'joins' => [
                        Spec::join('vouchers', 'voucher_entries.voucher_id', 'vouchers.id'),
                        $entryLedger,
                    ],
                    'constraints' => [
                        ['column' => 'voucher_entries.reconciled_at', 'operator' => '=', 'value' => null],
                        ['column' => 'entry_ledger.is_bank', 'operator' => '=', 'value' => 1],
                    ],
                    'columns' => [
                        Spec::col('voucher_no', 'Voucher', 'vouchers.voucher_no', ['primary' => true]),
                        Spec::date('voucher_date', 'Date', 'vouchers.voucher_date'),
                        Spec::col('ledger', 'Ledger', 'entry_ledger.name'),
                        Spec::badge('entry_type', 'Dr / Cr', 'voucher_entries.entry_type'),
                        Spec::money('amount', 'Amount', 'voucher_entries.amount'),
                        Spec::col('narration', 'Narration', 'voucher_entries.narration'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'vouchers.voucher_date', '>='),
                        Spec::dateFilter('to', 'To', 'vouchers.voucher_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('entries', 'Unreconciled', 'count(*)'),
                        Spec::summary('amount', 'Amount', 'sum(voucher_entries.amount)', 'money'),
                    ],
                    'order' => [Spec::order('voucher_date', 'desc')],
                ],

                'reconciliation-by-ledger' => [
                    'label' => 'Reconciliation by Bank Ledger',
                    'description' => 'How far each bank account has been reconciled.',
                    'base' => 'voucher_entries',
                    'joins' => [$entryLedger],
                    'constraints' => [
                        ['column' => 'entry_ledger.is_bank', 'operator' => '=', 'value' => 1],
                    ],
                    'group_by' => ['entry_ledger.id', 'entry_ledger.name'],
                    'columns' => [
                        Spec::col('ledger', 'Bank ledger', 'entry_ledger.name', ['primary' => true]),
                        Spec::num('entries', 'Entries', 'count(*)'),
                        Spec::num('reconciled', 'Reconciled', 'sum(case when voucher_entries.reconciled_at is not null then 1 else 0 end)'),
                        Spec::num('pending', 'Pending', 'sum(case when voucher_entries.reconciled_at is null then 1 else 0 end)'),
                        Spec::money('amount', 'Amount', 'sum(voucher_entries.amount)'),
                    ],
                    'summary' => [
                        Spec::summary('ledgers', 'Bank ledgers', 'count(distinct entry_ledger.id)'),
                        Spec::summary('entries', 'Entries', 'count(*)'),
                    ],
                    'order' => [Spec::order('entries', 'desc')],
                    'chart' => Spec::chart('bar', 'ledger', 'entries'),
                ],

                'reconciliation-progress' => [
                    'label' => 'Reconciliation Progress',
                    'description' => 'How much of the bank work has been done.',
                    'base' => 'voucher_entries',
                    'joins' => [$entryLedger],
                    'constraints' => [
                        ['column' => 'entry_ledger.is_bank', 'operator' => '=', 'value' => 1],
                    ],
                    'group_by' => ['year(voucher_entries.reconciled_at)', 'month(voucher_entries.reconciled_at)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(voucher_entries.reconciled_at), '/', year(voucher_entries.reconciled_at))", ['primary' => true]),
                        Spec::num('reconciled', 'Reconciled', 'count(*)'),
                        Spec::money('amount', 'Amount', 'sum(voucher_entries.amount)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'voucher_entries.reconciled_at', '>='),
                        Spec::dateFilter('to', 'To', 'voucher_entries.reconciled_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('amount', 'Amount', 'sum(voucher_entries.amount)', 'money'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('bar', 'period', 'amount'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function financeReports(): array
    {
        return [
            'label' => 'Finance Reports',
            'category' => 'Finance',
            'permission' => 'finance-reports.view',
            'reports' => [

                'trial-balance' => [
                    'label' => 'Trial Balance',
                    'description' => 'Every ledger with its debit and credit total.',
                    'base' => 'voucher_entries',
                    'joins' => [
                        Spec::join('ledger_accounts', 'voucher_entries.ledger_id', 'ledger_accounts.id', 'left', 'entry_ledger'),
                        Spec::join('account_groups', 'entry_ledger.group_id', 'account_groups.id', 'left', 'entry_group'),
                    ],
                    'group_by' => ['entry_ledger.id', 'entry_ledger.name', 'entry_group.name'],
                    'columns' => [
                        Spec::col('group', 'Group', 'entry_group.name'),
                        Spec::col('ledger', 'Ledger', 'entry_ledger.name', ['primary' => true]),
                        Spec::money('debit', 'Debit', "sum(case when voucher_entries.entry_type = 'Dr' then voucher_entries.amount else 0 end)"),
                        Spec::money('credit', 'Credit', "sum(case when voucher_entries.entry_type = 'Cr' then voucher_entries.amount else 0 end)"),
                        Spec::money('net', 'Net', "sum(case when voucher_entries.entry_type = 'Dr' then voucher_entries.amount else -voucher_entries.amount end)"),
                        Spec::num('entries', 'Entries', 'count(*)'),
                    ],
                    'summary' => [
                        Spec::summary('ledgers', 'Ledgers', 'count(distinct voucher_entries.ledger_id)'),
                        Spec::summary('debit', 'Total debit', "sum(case when voucher_entries.entry_type = 'Dr' then voucher_entries.amount else 0 end)", 'money'),
                        Spec::summary('credit', 'Total credit', "sum(case when voucher_entries.entry_type = 'Cr' then voucher_entries.amount else 0 end)", 'money'),
                    ],
                    'order' => [Spec::order('ledger')],
                ],

                'profit-and-loss' => [
                    'label' => 'Profit & Loss',
                    'description' => 'Income against expenses, by account group.',
                    'base' => 'voucher_entries',
                    'joins' => [
                        Spec::join('ledger_accounts', 'voucher_entries.ledger_id', 'ledger_accounts.id', 'left', 'entry_ledger'),
                        Spec::join('account_groups', 'entry_ledger.group_id', 'account_groups.id', 'left', 'entry_group'),
                    ],
                    'group_by' => ['entry_group.name', 'entry_group.nature'],
                    'columns' => [
                        Spec::col('group', 'Group', 'entry_group.name', ['primary' => true]),
                        Spec::badge('nature', 'Nature', 'entry_group.nature'),
                        Spec::money('income', 'Income', "sum(case when entry_group.nature = 'Income' then voucher_entries.amount else 0 end)"),
                        Spec::money('expenses', 'Expenses', "sum(case when entry_group.nature = 'Expenses' then voucher_entries.amount else 0 end)"),
                        Spec::money('net', 'Net', "sum(case when entry_group.nature = 'Income' then voucher_entries.amount else -voucher_entries.amount end)"),
                    ],
                    'summary' => [
                        Spec::summary('income', 'Total income', "sum(case when entry_group.nature = 'Income' then voucher_entries.amount else 0 end)", 'money'),
                        Spec::summary('expenses', 'Total expenses', "sum(case when entry_group.nature = 'Expenses' then voucher_entries.amount else 0 end)", 'money'),
                    ],
                    'order' => [Spec::order('group')],
                    'chart' => Spec::chart('bar', 'group', 'net'),
                ],

                'balance-sheet' => [
                    'label' => 'Balance Sheet',
                    'description' => 'What the company owns against what it owes.',
                    'base' => 'voucher_entries',
                    'joins' => [
                        Spec::join('ledger_accounts', 'voucher_entries.ledger_id', 'ledger_accounts.id', 'left', 'entry_ledger'),
                        Spec::join('account_groups', 'entry_ledger.group_id', 'account_groups.id', 'left', 'entry_group'),
                    ],
                    'group_by' => ['entry_group.name', 'entry_group.nature'],
                    'columns' => [
                        Spec::col('group', 'Group', 'entry_group.name', ['primary' => true]),
                        Spec::badge('nature', 'Nature', 'entry_group.nature'),
                        Spec::money('amount', 'Balance', 'sum(voucher_entries.amount)'),
                        Spec::num('ledgers', 'Ledgers', 'count(distinct entry_ledger.id)'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('nature', 'Nature', 'account_groups', 'nature'),
                    ],
                    'summary' => [
                        Spec::summary('groups', 'Groups', 'count(distinct entry_group.id)'),
                        Spec::summary('amount', 'Total', 'sum(voucher_entries.amount)', 'money'),
                    ],
                    'order' => [Spec::order('group')],
                    'chart' => Spec::chart('bar', 'group', 'amount'),
                ],

                'receivables' => [
                    'label' => 'Receivables by Party',
                    'description' => 'What customers still owe.',
                    'base' => 'ledger_accounts',
                    'joins' => [$group = Spec::join('account_groups', 'ledger_accounts.group_id', 'account_groups.id')],
                    'constraints' => [
                        ['column' => 'ledger_accounts.party_type', 'operator' => '=', 'value' => 'customer'],
                    ],
                    'columns' => [
                        Spec::col('name', 'Customer', 'ledger_accounts.name', ['primary' => true]),
                        Spec::col('group', 'Group', 'account_groups.name'),
                        Spec::money('opening_balance', 'Opening balance', 'ledger_accounts.opening_balance'),
                        Spec::col('opening_type', 'Dr / Cr', 'ledger_accounts.opening_type'),
                        Spec::col('gst_number', 'GST no', 'ledger_accounts.gst_number'),
                        Spec::badge('status', 'Status', 'ledger_accounts.status'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['ledger_accounts.name', 'ledger_accounts.gst_number']),
                    ],
                    'summary' => [
                        Spec::summary('customers', 'Customers', 'count(*)'),
                        Spec::summary('opening', 'Total outstanding', 'sum(ledger_accounts.opening_balance)', 'money'),
                    ],
                    'order' => [Spec::order('opening_balance', 'desc')],
                ],

                'payables' => [
                    'label' => 'Payables by Party',
                    'description' => 'What the company owes its suppliers.',
                    'base' => 'ledger_accounts',
                    'joins' => [$group = Spec::join('account_groups', 'ledger_accounts.group_id', 'account_groups.id')],
                    'constraints' => [
                        ['column' => 'ledger_accounts.party_type', 'operator' => '=', 'value' => 'supplier'],
                    ],
                    'columns' => [
                        Spec::col('name', 'Supplier', 'ledger_accounts.name', ['primary' => true]),
                        Spec::col('group', 'Group', 'account_groups.name'),
                        Spec::money('opening_balance', 'Opening balance', 'ledger_accounts.opening_balance'),
                        Spec::col('opening_type', 'Dr / Cr', 'ledger_accounts.opening_type'),
                        Spec::col('gst_number', 'GST no', 'ledger_accounts.gst_number'),
                        Spec::badge('status', 'Status', 'ledger_accounts.status'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['ledger_accounts.name', 'ledger_accounts.gst_number']),
                    ],
                    'summary' => [
                        Spec::summary('suppliers', 'Suppliers', 'count(*)'),
                        Spec::summary('opening', 'Total outstanding', 'sum(ledger_accounts.opening_balance)', 'money'),
                    ],
                    'order' => [Spec::order('opening_balance', 'desc')],
                ],

                'gst-summary' => [
                    'label' => 'GST Summary',
                    'description' => 'Tax collected and paid, by rate.',
                    'base' => 'voucher_items',
                    'joins' => [Spec::join('vouchers', 'voucher_items.voucher_id', 'vouchers.id')],
                    'group_by' => ['voucher_items.tax_rate'],
                    'columns' => [
                        Spec::num('tax_rate', 'Rate %', 'voucher_items.tax_rate', ['primary' => true]),
                        Spec::num('lines', 'Lines', 'count(*)'),
                        Spec::money('taxable', 'Taxable value', 'sum(voucher_items.taxable_amount)'),
                        Spec::money('cgst', 'CGST', 'sum(voucher_items.cgst_amount)'),
                        Spec::money('sgst', 'SGST', 'sum(voucher_items.sgst_amount)'),
                        Spec::money('igst', 'IGST', 'sum(voucher_items.igst_amount)'),
                        Spec::money('tax', 'Total tax', 'sum(voucher_items.tax_amount)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'vouchers.voucher_date', '>='),
                        Spec::dateFilter('to', 'To', 'vouchers.voucher_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('taxable', 'Taxable value', 'sum(voucher_items.taxable_amount)', 'money'),
                        Spec::summary('tax', 'Total tax', 'sum(voucher_items.tax_amount)', 'money'),
                    ],
                    'order' => [Spec::order('tax_rate')],
                    'chart' => Spec::chart('bar', 'tax_rate', 'tax'),
                ],

                'cash-flow-summary' => [
                    'label' => 'Cash Flow Summary',
                    'description' => 'Money in and money out, month by month.',
                    'base' => 'voucher_entries',
                    'joins' => [Spec::join('vouchers', 'voucher_entries.voucher_id', 'vouchers.id')],
                    'group_by' => ['year(vouchers.voucher_date)', 'month(vouchers.voucher_date)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(vouchers.voucher_date), '/', year(vouchers.voucher_date))", ['primary' => true]),
                        Spec::money('inflow', 'Money in', "sum(case when voucher_entries.entry_type = 'Dr' then voucher_entries.amount else 0 end)"),
                        Spec::money('outflow', 'Money out', "sum(case when voucher_entries.entry_type = 'Cr' then voucher_entries.amount else 0 end)"),
                        Spec::money('net', 'Net movement', 'sum(case when voucher_entries.entry_type = \'Dr\' then voucher_entries.amount else -voucher_entries.amount end)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'vouchers.voucher_date', '>='),
                        Spec::dateFilter('to', 'To', 'vouchers.voucher_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('net', 'Net movement', "sum(case when voucher_entries.entry_type = 'Dr' then voucher_entries.amount else -voucher_entries.amount end)", 'money'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'net'),
                ],
            ],
        ];
    }
}
