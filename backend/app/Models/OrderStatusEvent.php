<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One step of an order's status history — the timeline shown on the order
 * detail page: who moved it, from where to where, and why.
 */
class OrderStatusEvent extends Model
{
    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'user_id',
        'note',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Order, OrderStatusEvent> */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, OrderStatusEvent> */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
