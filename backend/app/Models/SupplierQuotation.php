<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierQuotation extends Model
{
    use \App\Traits\BelongsToBranch;

    protected $guarded = [];
}
