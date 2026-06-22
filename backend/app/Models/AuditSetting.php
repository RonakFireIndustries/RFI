<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditSetting extends Model
{
    protected $fillable = [
        'configurable_type', 'configurable_id',
        'logging_verbosity', 'retention_days',
        'reason_required', 'change_tracking_enabled', 'snapshot_creation_enabled',
    ];

    protected $casts = [
        'reason_required' => 'boolean',
        'change_tracking_enabled' => 'boolean',
        'snapshot_creation_enabled' => 'boolean',
    ];
}
