<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use \App\Traits\BelongsToBranch;

    protected $guarded = [];
}
