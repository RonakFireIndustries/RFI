<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransfer extends Model
{
    protected $fillable = [
        'source_branch_id', 'destination_branch_id', 'product_id', 
        'quantity', 'status', 'requested_by', 'approved_by', 'notes'
    ];

    public function sourceBranch() { return $this->belongsTo(Branch::class, 'source_branch_id'); }
    public function destinationBranch() { return $this->belongsTo(Branch::class, 'destination_branch_id'); }
    public function product() { return $this->belongsTo(Product::class); }
    public function requester() { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
