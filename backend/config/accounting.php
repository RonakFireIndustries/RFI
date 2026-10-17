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
        'purchase' => [
            'label' => 'Purchase',
            'prefix' => 'PUR',
            'bills' => true,
            'module' => null,
        ],
        'sales' => [
            'label' => 'Sales',
            'prefix' => 'SAL',
            'bills' => true,
            'module' => null,
        ],
        'credit-note' => [
            'label' => 'Credit Note',
            'prefix' => 'CRN',
            'bills' => true,
            'module' => null,
        ],
        'debit-note' => [
            'label' => 'Debit Note',
            'prefix' => 'DBN',
            'bills' => true,
            'module' => null,
        ],
    ],

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
    ],

    /* Voucher statuses. Only `posted` vouchers affect the books. */
    'statuses' => ['draft', 'posted', 'cancelled'],
];
