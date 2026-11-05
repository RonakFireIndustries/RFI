<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number', 'supplier_ledger_id', 'site_id', 'total_amount', 'status', 'requested_by',
        'approved_by', 'notes', 'tax_amount', 'shipping_cost', 'other_cost',
        'other_cost_note', 'gst_type', 'gst_rate', 'received_by', 'received_at',
        'terms_conditions',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function supplierLedger()
    {
        return $this->belongsTo(LedgerAccount::class, 'supplier_ledger_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'purchase_order_id');
    }
}
