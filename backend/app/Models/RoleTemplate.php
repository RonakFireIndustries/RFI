<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleTemplate extends Model
{
    protected $fillable = ['name', 'description', 'category', 'permissions', 'is_active'];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];
}
