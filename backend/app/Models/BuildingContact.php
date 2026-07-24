<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildingContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id', 'full_name', 'role', 'role_category',
        'mobile_number', 'whatsapp_number', 'email', 'notes',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }
}
