<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerQuotation extends Model
{
    use \App\Traits\BelongsToBranch;

    protected $guarded = [];
}
