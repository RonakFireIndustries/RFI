<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Payment extends Model
{
    protected $fillable = [
        'type',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
        'supplier_ledger_id',
        'customer_ledger_id',
        'purchase_order_id',
        'sales_order_id',
        'created_by',
    ];

    public function supplierLedger()
    {
        return $this->belongsTo(LedgerAccount::class, 'supplier_ledger_id');
    }

    public function customerLedger()
    {
        return $this->belongsTo(LedgerAccount::class, 'customer_ledger_id');
    }
}
