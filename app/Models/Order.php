<?php

namespace App\Models;

use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'owner_id',
        'total_amount',
        'status',
        'order_date',
        'contact_name',
        'contact_phone',
        'contact_email',
        'fulfillment',
        'address_line',
        'city',
        'notes',
        'payment_method',
        'payment_status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'status' => OrderStatus::class,
            'order_date' => 'datetime',
            'fulfillment' => FulfillmentMethod::class,
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
        ];
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recalculateTotal(): void
    {
        $total = $this->items->sum(function (OrderItem $item): float {
            return (float) $item->quantity * (float) $item->price_each;
        });

        $this->update(['total_amount' => $total]);
    }
}
