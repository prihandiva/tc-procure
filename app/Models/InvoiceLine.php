<?php

namespace App\Models;

use App\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceLine extends Model
{
    use HasAuditTrail, HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'delivery_item_id',
        'qty',
        'unit_price',
        'amount',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'unit_price' => 'integer',
            'amount' => 'integer',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function deliveryItem(): BelongsTo
    {
        return $this->belongsTo(DeliveryItem::class);
    }
}

