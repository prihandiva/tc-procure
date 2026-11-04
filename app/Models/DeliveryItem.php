<?php

namespace App\Models;

use App\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryItem extends Model
{
    use HasAuditTrail, HasFactory, SoftDeletes;

    protected $fillable = [
        'delivery_id',
        'po_item_id',
        'qty_sent',
        'qty_received',
        'training_name',
        'needed_date',
        'variant_note',
        'is_invoiced',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'qty_sent' => 'decimal:2',
            'qty_received' => 'decimal:2',
            'needed_date' => 'date',
            'is_invoiced' => 'boolean',
        ];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function poItem(): BelongsTo
    {
        return $this->belongsTo(PoItem::class);
    }

    public function invoiceLine(): HasOne
    {
        return $this->hasOne(InvoiceLine::class);
    }

    public function getSubtotalSentAttribute(): int
    {
        return (int) round(((float) $this->qty_sent) * ($this->poItem->unit_price ?? 0));
    }

    public function getSubtotalReceivedAttribute(): int
    {
        $qty = $this->qty_received !== null ? (float) $this->qty_received : (float) $this->qty_sent;

        return (int) round($qty * ($this->poItem->unit_price ?? 0));
    }

    public function hasDifference(): bool
    {
        return $this->qty_received !== null && (float) $this->qty_received !== (float) $this->qty_sent;
    }

    public function getDifferenceAttribute(): float
    {
        if ($this->qty_received === null) {
            return 0.0;
        }

        return (float) $this->qty_received - (float) $this->qty_sent;
    }
}

