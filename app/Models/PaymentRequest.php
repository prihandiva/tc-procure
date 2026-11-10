<?php

namespace App\Models;

use App\Support\DocumentNumberSuggester;
use App\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentRequest extends Model
{
    use HasAuditTrail, HasFactory, SoftDeletes;

    protected $fillable = [
        'number',
        'current_stage_id',
        'submitted_at',
        'notes',
        'posting_id',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(PaymentStage::class, 'current_stage_id');
    }

    public function posting(): BelongsTo
    {
        return $this->belongsTo(Posting::class);
    }

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'payment_request_invoices')
            ->withTimestamps();
    }

    public function stageLogs(): HasMany
    {
        return $this->hasMany(PaymentRequestStageLog::class)->orderBy('created_at', 'desc');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function getTotalAmountAttribute(): int
    {
        return (int) $this->invoices->sum('total_amount');
    }

    public function isPosted(): bool
    {
        return $this->posting_id !== null;
    }

    public function getVendorsAttribute()
    {
        return $this->invoices->map->vendor->unique('id');
    }

    public function scopePosted($query)
    {
        return $query->whereNotNull('posting_id');
    }

    public function scopeUnposted($query)
    {
        return $query->whereNull('posting_id');
    }

    /**
     * Suggest default Payment Request number pre-filled from the Purchase Order number.
     */
    public static function suggestNumberForPo(PurchaseOrder $po): string
    {
        return DocumentNumberSuggester::fromPo(
            $po,
            fn (string $candidate) => static::withTrashed()
                ->where('number', $candidate)
                ->exists()
        );
    }
}

