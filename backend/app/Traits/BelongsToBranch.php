<?php

namespace App\Traits;

use App\Scopes\BranchScope;
use Illuminate\Support\Facades\Auth;

trait BelongsToBranch
{
    protected static function bootBelongsToBranch()
    {
        static::addGlobalScope(new BranchScope);

        static::creating(function ($model) {
            if (Auth::hasUser()) {
                $user = Auth::user();
                $headerBranchId = request()->header('X-Branch-Id');
                if ($headerBranchId) {
                    $model->branch_id = $headerBranchId;
                } elseif (empty($model->branch_id)) {
                    $model->branch_id = $user->branch_id;
                }
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }
}
