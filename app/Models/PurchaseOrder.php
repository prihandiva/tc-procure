<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\PoStatus;
use App\Enums\PoType;
use App\Traits\BelongsToVendor;
use App\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use BelongsToVendor, HasAuditTrail, HasFactory, SoftDeletes;

    protected $fillable = [
        'po_number',
        'type',
        'vendor_id',
        'po_date',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => PoType::class,
            'status' => PoStatus::class,
            'po_date' => 'date',
        ];
    }

    public function poItems(): HasMany
    {
        return $this->hasMany(PoItem::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Total amount calculated from PO items (ordered_qty * unit_price)
     */
    public function getTotalAmountAttribute(): int
    {
        return (int) $this->poItems->sum(fn ($item) => $item->subtotal);
    }

    /**
     * Total quantity ordered across all items
     */
    public function getTotalQtyOrderedAttribute(): float
    {
        return (float) $this->poItems->sum('qty_ordered');
    }

    /**
     * Total confirmed delivered quantity
     */
    public function getTotalQtyDeliveredAttribute(): float
    {
        return (float) $this->poItems->sum('qty_delivered');
    }

    /**
     * Total remaining quantity yet to be delivered
     */
    public function getTotalQtyRemainingAttribute(): float
    {
        return max(0, $this->total_qty_ordered - $this->total_qty_delivered);
    }

    /**
     * Total amount ordered in Rupiah
     */
    public function getTotalAmountOrderedAttribute(): int
    {
        return $this->total_amount;
    }

    /**
     * Total amount confirmed delivered in Rupiah
     */
    public function getTotalAmountDeliveredAttribute(): int
    {
        return (int) $this->poItems->sum(function ($item) {
            return (int) round($item->qty_delivered * $item->unit_price);
        });
    }

    /**
     * Alias for total_amount_delivered
     */
    public function getDeliveredAmountAttribute(): int
    {
        return $this->total_amount_delivered;
    }

    /**
     * Total amount remaining to be delivered in Rupiah
     */
    public function getTotalAmountRemainingAttribute(): int
    {
        return max(0, $this->total_amount_ordered - $this->total_amount_delivered);
    }

    /**
     * Alias for total_amount_remaining
     */
    public function getRemainingAmountAttribute(): int
    {
        return $this->total_amount_remaining;
    }

    /**
     * Total amount of active invoices in Rupiah
     */
    public function getTotalAmountInvoicedAttribute(): int
    {
        return (int) $this->invoices()
            ->whereNotIn('status', [\App\Enums\InvoiceStatus::REJECTED->value, \App\Enums\InvoiceStatus::VOID->value])
            ->sum('total_amount');
    }

    /**
     * Amount delivered but not yet invoiced in Rupiah
     */
    public function getTotalAmountUninvoicedAttribute(): int
    {
        return max(0, $this->total_amount_delivered - $this->total_amount_invoiced);
    }

    /**
     * Total amount paid in Rupiah
     */
    public function getTotalAmountPaidAttribute(): int
    {
        return (int) $this->invoices()
            ->where('status', \App\Enums\InvoiceStatus::PAID->value)
            ->sum('total_amount');
    }

    /**
     * Total amount invoiced but not yet paid in Rupiah
     */
    public function getTotalAmountUnpaidAttribute(): int
    {
        return max(0, $this->total_amount_invoiced - $this->total_amount_paid);
    }

    /**
     * Check if all ordered items have been delivered
     */
    public function getIsDeliveryCompletedAttribute(): bool
    {
        return $this->total_qty_ordered > 0 && $this->total_qty_remaining <= 0;
    }

    /**
     * Check if all invoices are paid
     */
    public function getIsPaidInFullAttribute(): bool
    {
        return $this->invoices()->exists() &&
            $this->invoices()->where('status', '!=', \App\Enums\InvoiceStatus::PAID->value)->doesntExist();
    }

    public function isNonPo(): bool
    {
        return $this->type === \App\Enums\PoType::NON_PO;
    }

    /**
     * PO can be edited only if no deliveries exist yet
     */
    public function canBeEdited(): bool
    {
        return $this->deliveries()->doesntExist();
    }
}

