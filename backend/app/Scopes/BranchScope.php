<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::hasUser()) {
            $user = Auth::user();
            $headerBranchId = request()->header('X-Branch-Id');
            if ($headerBranchId && $headerBranchId !== 'null' && $headerBranchId !== 'undefined') {
                $builder->where($model->getTable() . '.branch_id', $headerBranchId);
            } else {
                if ($user->branch_id) {
                    $builder->where($model->getTable() . '.branch_id', $user->branch_id);
                } else {
                    $builder->whereRaw('1 = 0');
                }
            }
        }
    }
}
