<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCategory extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function reports()
    {
        return $this->hasMany(Report::class, 'category_id');
    }

    public function activeReports()
    {
        return $this->hasMany(Report::class, 'category_id')->where('status', 'active');
    }
}
