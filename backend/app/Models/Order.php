<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An operational order — the tracking spine from confirmation to settlement.
 *
 * Orders never post to the ledger; money moves when the linked purchase /
 * sales voucher is posted. Statuses walk open → in_progress → fulfilled →
 * invoiced → done (or cancelled), one stage at a time per config/orders.php.
 *
 * `document_type` / `document_id` name the bill the order became, using the
 * same vocabulary as the accounting bill-wise details so the payment panel
 * can find that bill in PartyOutstandingService.
 */
class Order extends Model
{
    protected $fillable = [
        'order_no',
        'side',
        'customer_id',
        'supplier_id',
        'created_by',
        'order_date',
        'expected_delivery_date',
        'status',
        'reference_no',
        'notes',
        'document_type',
        'document_id',
        'subtotal',
        'discount_total',
        'tax_total',
        'total_amount',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<OrderItem> */
    public function items()
    {
        return $this->hasMany(OrderItem::class)->orderBy('position')->orderBy('id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<OrderStatusEvent> */
    public function events()
    {
        return $this->hasMany(OrderStatusEvent::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Customer, Order> */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Supplier, Order> */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Order> */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** The party this order is for — customer on sales, supplier on purchase. */
    public function party(): ?Model
    {
        return $this->side === 'purchase' ? $this->supplier : $this->customer;
    }

    public function partyName(): ?string
    {
        return $this->party()?->name;
    }

    /** A finished order: `done` or `cancelled`, both with no `next` stage. */
    public function isTerminal(): bool
    {
        return config("orders.statuses.{$this->status}.next") === null;
    }

    public function isEditable(): bool
    {
        return in_array($this->status, config('orders.editable_statuses', []), true);
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, config('orders.cancellable_statuses', []), true);
    }
}
