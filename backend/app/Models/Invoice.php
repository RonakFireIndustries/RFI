<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'customer_ledger_id', 'sales_order_id',
        'subtotal', 'cgst_total', 'sgst_total', 'igst_total', 'grand_total',
        'status', 'due_date', 'payment_date', 'paid_amount', 'notes', 'terms', 'created_by',
        'gst_type', 'gst_rate',
        'issue_date', 'total_amount',
    ];

    public function customerLedger()
    {
        return $this->belongsTo(LedgerAccount::class, 'customer_ledger_id');
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
