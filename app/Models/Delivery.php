<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Support\DocumentNumberSuggester;
use App\Traits\BelongsToVendor;
use App\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use BelongsToVendor, HasAuditTrail, HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'purchase_order_id',
        'vendor_id',
        'delivery_date',
        'vendor_do_number',
        'status',
        'reject_reason',
        'submitted_by',
        'confirmed_by',
        'confirmed_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'status' => DeliveryStatus::class,
            'confirmed_at' => 'datetime',
        ];
    }

    public function getDeliveryCodeAttribute(): string
    {
        return $this->code;
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function deliveryItems(): HasMany
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function isConfirmed(): bool
    {
        return $this->status === DeliveryStatus::CONFIRMED;
    }

    public function isSubmitted(): bool
    {
        return $this->status === DeliveryStatus::SUBMITTED;
    }

    public function isRejected(): bool
    {
        return $this->status === DeliveryStatus::REJECTED;
    }

    public function getTotalQtySentAttribute(): float
    {
        return (float) $this->deliveryItems->sum('qty_sent');
    }

    public function getTotalQtyReceivedAttribute(): float
    {
        return (float) $this->deliveryItems->sum('qty_received');
    }

    public function getTotalAmountReceivedAttribute(): int
    {
        return (int) $this->deliveryItems->sum(function ($item) {
            $unitPrice = $item->poItem->unit_price ?? 0;
            $qty = $item->qty_received ?? $item->qty_sent;

            return (int) round($qty * $unitPrice);
        });
    }

    public function hasDiscrepancy(): bool
    {
        return $this->deliveryItems->contains(function ($item) {
            return $item->qty_received !== null && (float) $item->qty_received !== (float) $item->qty_sent;
        });
    }

    public function isInvoiceable(): bool
    {
        return $this->isConfirmed() && $this->deliveryItems->where('is_invoiced', false)->isNotEmpty();
    }

    /**
     * Suggest default delivery/DO code pre-filled from the Purchase Order number.
     * Falls back to `{po_number}-2`, `-3`, ... when partial deliveries already exist.
     */
    public static function suggestCodeForPo(PurchaseOrder $po): string
    {
        return DocumentNumberSuggester::fromPo(
            $po,
            fn (string $candidate) => static::withoutGlobalScopes()
                ->withTrashed()
                ->where('code', $candidate)
                ->exists()
        );
    }

    /**
     * Generate unique sequential delivery code: DLV-YYYY-XXXXX
     */
    public static function generateCode(?int $year = null): string
    {
        $year = $year ?? (int) date('Y');
        $prefix = "DLV-{$year}-";

        $lastDelivery = static::withoutGlobalScopes()
            ->where('code', 'like', "{$prefix}%")
            ->orderBy('code', 'desc')
            ->first();

        if ($lastDelivery) {
            $lastSeq = (int) substr($lastDelivery->code, strlen($prefix));
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return sprintf('%s%05d', $prefix, $nextSeq);
    }
}

