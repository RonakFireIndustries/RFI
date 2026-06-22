<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemException extends Model
{
    protected $fillable = ['name', 'key', 'description', 'category', 'is_enabled', 'requires_approval', 'allowed_roles'];

    protected $casts = [
        'is_enabled' => 'boolean',
        'requires_approval' => 'boolean',
        'allowed_roles' => 'array',
    ];
}
