<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpportunityWorkType extends Model
{
    use HasFactory;

    protected $fillable = ['opportunity_id', 'work_type'];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }
}
