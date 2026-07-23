<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'file_name', 'original_file_name', 'mime_type', 'file_path', 'file_type',
        'document_type', 'expiry_date', 'remarks', 'documentable_id',
        'documentable_type', 'uploaded_by', 'file_size',
    ];

    public function documentable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
