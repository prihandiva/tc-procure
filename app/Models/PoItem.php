<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoItem extends Model
{
    use HasAuditTrail, HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'item_id',
        'item_name',
        'unit',
        'qty_ordered',
        'unit_price',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'qty_ordered' => 'decimal:2',
            'unit_price' => 'integer',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function deliveryItems(): HasMany
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function getSubtotalAttribute(): int
    {
        return (int) round(((float) $this->qty_ordered) * $this->unit_price);
    }

    /**
     * Confirmed quantity delivered for this PO item
     */
    public function getQtyDeliveredAttribute(): float
    {
        return (float) $this->deliveryItems()
            ->whereHas('delivery', fn ($q) => $q->where('status', DeliveryStatus::CONFIRMED->value))
            ->sum('qty_received');
    }

    /**
     * Remaining quantity to be delivered
     */
    public function getQtyRemainingAttribute(): float
    {
        return max(0, ((float) $this->qty_ordered) - $this->qty_delivered);
    }
}

