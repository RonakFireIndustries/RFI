<?php

/*
 |--------------------------------------------------------------------------
 | Orders (operational tracking)
 |--------------------------------------------------------------------------
 |
 | Orders follow a party from confirmation to settlement, but they never
 | touch the ledger: money enters the books when a purchase / sales voucher
 | is posted (see config/accounting.php). An order only *links* to the
 | document it became, which is how its payment panel reads due dates,
 | ageing and what is still outstanding.
 |
 */

return [

    // Numbering per financial year, mirroring voucher numbers: SO-2026-0001.
    'prefixes' => [
        'sales' => 'SO',
        'purchase' => 'PO',
    ],

    // The sides an order can be raised on, and the party master each one is
    // for. `documents` lists the bill types an order on that side may be
    // marked invoiced against.
    'sides' => [
        'sales' => [
            'label' => 'Sales',
            'party' => 'customer',
            'documents' => ['invoice', 'sales-voucher'],
        ],
        'purchase' => [
            'label' => 'Purchase',
            'party' => 'supplier',
            'documents' => ['purchase-order', 'purchase-voucher'],
        ],
    ],

    /*
     | The five-stage pipeline, plus cancelled. `next` is the single source of
     | truth for "may this order advance, and to where" — the API enforces it
     | and the UI reads the same table, so button and backend cannot disagree.
     | A null `next` is terminal.
     */
    'statuses' => [
        'open' => ['label' => 'Open', 'next' => 'in_progress'],
        'in_progress' => ['label' => 'In progress', 'next' => 'fulfilled'],
        'fulfilled' => ['label' => 'Fulfilled', 'next' => 'invoiced'],
        'invoiced' => ['label' => 'Invoiced', 'next' => 'done'],
        'done' => ['label' => 'Done', 'next' => null],
        'cancelled' => ['label' => 'Cancelled', 'next' => null],
    ],

    // Statuses whose lines, dates and party may still be edited.
    'editable_statuses' => ['open', 'in_progress'],

    // Statuses a cancel is offered from — everything except the finished and
    // already-cancelled stages.
    'cancellable_statuses' => ['open', 'in_progress', 'fulfilled', 'invoiced'],
];
