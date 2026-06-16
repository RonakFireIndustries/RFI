<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBranch;

class PurchaseOrder extends Model
{
    use BelongsToBranch;
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
