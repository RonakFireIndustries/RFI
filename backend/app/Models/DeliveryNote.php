<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    use \App\Traits\BelongsToBranch;

    protected $guarded = [];
}
