<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use \App\Traits\BelongsToBranch;

    protected $guarded = [];
}
