<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueReportScreenshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_report_id', 'file_name', 'file_path', 'mime_type', 'file_size', 'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    protected $appends = ['url'];

    public function issueReport(): BelongsTo
    {
        return $this->belongsTo(IssueReport::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return '/storage/' . $this->file_path;
    }
}
