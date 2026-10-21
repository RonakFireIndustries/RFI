<?php

/*
|--------------------------------------------------------------------------
| Accounting — Tally-style voucher definitions
|--------------------------------------------------------------------------
|
| The voucher engine is data-driven: every voucher stores a `voucher_type`
| key, and this file describes the types the application understands.
|
|   label    Human-readable name shown in the UI.
|   prefix   Number prefix, e.g. PMT-2026-0001.
|   bills    Whether the type carries bill-wise allocation details.
|   module   Canonical access-control module that gates the type
|            (config/access.php). Types without a module are not yet
|            exposed to the API (they arrive in a later slice).
|
| Slice 2 ships contra / payment / receipt / journal. Purchase, sales and the
| notes are defined now so later slices only need to wire their UI, but they
| stay unreachable until a `module` is assigned.
|
*/

return [

    'voucher_types' => [

        'contra' => [
            'label' => 'Contra',
            'prefix' => 'CONTRA',
            'bills' => false,
            'module' => 'contra',
        ],

        'payment' => [
            'label' => 'Payment',
            'prefix' => 'PMT',
            'bills' => true,
            'module' => 'payment-vouchers',
        ],

        'receipt' => [
            'label' => 'Receipt',
            'prefix' => 'RCPT',
            'bills' => true,
            'module' => 'receipt-vouchers',
        ],

        'journal' => [
            'label' => 'Journal',
            'prefix' => 'JRNL',
            'bills' => true,
            'module' => 'journal',
        ],

        // Section 2 — purchase / sales vouchers (later slice).
        //
        // `stock` names the direction the document moves warehouse stock in:
        // an `in` document creates the goods receipt the warehouse fulfils, an
        // `out` document is fulfilled by deliveries. `None` means the type
        // never touches stock (a credit note is a financial adjustment that
        // presumes the goods went back through a separate entry).
        'purchase' => [
            'label' => 'Purchase',
            'prefix' => 'PUR',
            'bills' => true,
            'module' => 'purchase-vouchers',
            'items' => true,
            'party_type' => 'supplier',
            'stock' => 'in',
            'posting' => [
                'party' => 'Cr',
                'base' => 'Dr',
                'base_ledger' => 'purchase_ledger',
            ],
        ],
        'sales' => [
            'label' => 'Sales',
            'prefix' => 'SAL',
            'bills' => true,
            'module' => 'sales-vouchers',
            'items' => true,
            'party_type' => 'customer',
            'stock' => 'out',
            'posting' => [
                'party' => 'Dr',
                'base' => 'Cr',
                'base_ledger' => 'sales_ledger',
            ],
        ],
        'credit-note' => [
            'label' => 'Credit Note',
            'prefix' => 'CRN',
            'bills' => true,
            'module' => 'credit-notes',
            'items' => true,
            'party_type' => 'customer',
            // A credit note to a customer returns goods to the warehouse, so
            // the note is the receipt: stock flows in.
            'stock' => 'in',
            'posting' => [
                'party' => 'Cr',
                'base' => 'Dr',
                'base_ledger' => 'sales_ledger',
            ],
        ],
        'debit-note' => [
            'label' => 'Debit Note',
            'prefix' => 'DBN',
            'bills' => true,
            'module' => 'debit-notes',
            'items' => true,
            'party_type' => 'supplier',
            // A debit note to a supplier sends goods back, so stock flows out.
            'stock' => 'out',
            'posting' => [
                'party' => 'Dr',
                'base' => 'Cr',
                'base_ledger' => 'purchase_ledger',
            ],
        ],
    ],

    /*
    | Default ledger names used to derive Dr/Cr lines for item-based vouchers.
    */
    'defaults' => [
        'purchase_ledger' => 'Purchase',
        'sales_ledger' => 'Sales',
        'cgst_ledger' => 'CGST',
        'sgst_ledger' => 'SGST',
        'igst_ledger' => 'IGST',
        'round_off_ledger' => 'Round Off',
    ],

    'gst_rates' => [0, 5, 12, 18, 28],

    /*
    | Bill reference kinds a voucher can allocate against. `opening` is the
    | party ledger's brought-forward balance; `invoice` / `purchase-order` are
    | the legacy documents still in the books; the rest are entered by hand.
    */
    'bill_types' => [
        'opening',
        'invoice',
        'purchase-order',
        'sales-voucher',
        'purchase-voucher',
        'credit-note',
        'debit-note',
        'new-ref',
        'advance',
        'on-account',
    ],

    /*
    | Bill kinds that settle an actual outstanding document (as opposed to an
    | advance). Used by the outstanding report to work out what is still due.
    */
    'settling_bill_types' => [
        'opening',
        'invoice',
        'purchase-order',
        'sales-voucher',
        'purchase-voucher',
        'credit-note',
        'debit-note',
    ],

    /* Voucher statuses. Only `posted` vouchers affect the books. */
    'statuses' => ['draft', 'posted', 'cancelled'],

    /*
    |--------------------------------------------------------------------------
    | Warehouse stock
    |--------------------------------------------------------------------------
    |
    | Whether a document's goods actually reached (or left) the warehouse is
    | tracked separately from the books, because a purchase bill arrives with
    | the first part-delivery and the rest lands weeks later. The status is
    | derived from the receipt lines rather than stored, so it can never drift:
    | nothing received is `pending`, some is `partial`, all is `complete`, and
    | a document whose lines carry no product at all is `not_tracked`.
    |
    */
    'stock_tracking' => [
        'statuses' => ['not_tracked', 'pending', 'partial', 'complete'],
        'quantity_tolerance' => 0.001,
    ],

];