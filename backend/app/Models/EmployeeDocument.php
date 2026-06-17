<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class EmployeeDocument extends Document
{
    protected $table = 'documents';

    protected static function booted()
    {
        static::addGlobalScope('employee_document', function (Builder $builder) {
            $builder->where('documentable_type', Employee::class);
        });

        static::creating(function ($document) {
            $document->documentable_type = Employee::class;
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'documentable_id');
    }
}
