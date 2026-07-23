<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number', 'supplier_id', 'total_amount', 'status', 'requested_by',
        'approved_by', 'notes', 'tax_amount', 'shipping_cost', 'gst_type',
        'gst_rate', 'received_by', 'received_at',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
