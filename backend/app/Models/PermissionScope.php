<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionScope extends Model
{
    protected $fillable = ['name', 'label', 'description', 'level'];
}
