<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Traits\BelongsToVendor;
use App\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use BelongsToVendor, HasAuditTrail, HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'vendor_id',
        'purchase_order_id',
        'invoice_date',
        'total_amount',
        'status',
        'reject_reason',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'status' => InvoiceStatus::class,
            'total_amount' => 'integer',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function invoiceLines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function lines(): HasMany
    {
        return $this->invoiceLines();
    }

    public function paymentRequests(): BelongsToMany
    {
        return $this->belongsToMany(PaymentRequest::class, 'payment_request_invoices')
            ->withTimestamps();
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function getCalculatedTotalAttribute(): int
    {
        return (int) $this->invoiceLines->sum('amount');
    }

    public function isSubmitted(): bool
    {
        return $this->status === InvoiceStatus::SUBMITTED;
    }

    public function isApproved(): bool
    {
        return $this->status === InvoiceStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === InvoiceStatus::REJECTED;
    }

    public function isInPayment(): bool
    {
        return $this->status === InvoiceStatus::IN_PAYMENT;
    }

    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::PAID;
    }

    public function isVoid(): bool
    {
        return $this->status === InvoiceStatus::VOID;
    }

    /**
     * Term of Payment (TOP) standard: 14 hari kalender
     */
    public const PAYMENT_TERMS_DAYS = 14;

    /**
     * Toleransi selisih pembulatan wajar (maksimal Rp 1.000)
     */
    public const ROUNDING_TOLERANCE_RUPIAH = 1000;

    /**
     * Tanggal jatuh tempo invoice (invoice_date + TOP)
     */
    public function getDueDateAttribute(): ?\Carbon\CarbonInterface
    {
        return $this->invoice_date ? $this->invoice_date->copy()->addDays(self::PAYMENT_TERMS_DAYS) : null;
    }

    /**
     * Cek apakah invoice telah melewati jatuh tempo pembayaran (Overdue)
     */
    public function isOverdue(): bool
    {
        if ($this->isPaid() || $this->isVoid() || $this->isRejected() || ! $this->due_date) {
            return false;
        }

        return $this->due_date->endOfDay()->isPast();
    }

    /**
     * Jumlah hari keterlambatan pembayaran
     */
    public function getDaysOverdueAttribute(): int
    {
        if (! $this->isOverdue() || ! $this->due_date) {
            return 0;
        }

        return (int) abs(now()->startOfDay()->diffInDays($this->due_date->startOfDay()));
    }

    /**
     * Check if this vendor has another invoice with the same number (excluding void)
     */
    public function hasDuplicateNumber(): bool
    {
        return static::withoutGlobalScopes()
            ->where('vendor_id', $this->vendor_id)
            ->where('invoice_number', $this->invoice_number)
            ->where('id', '!=', $this->id)
            ->whereNotIn('status', [InvoiceStatus::VOID->value, InvoiceStatus::REJECTED->value])
            ->exists();
    }

    /**
     * 3-Way Match Check 1: Unit prices match PO locked prices (dengan toleransi pembulatan pecahan)
     */
    public function checkPriceMatch(): bool
    {
        foreach ($this->invoiceLines as $line) {
            $poPrice = $line->deliveryItem->poItem->unit_price ?? null;
            if ($poPrice === null || abs($line->unit_price - $poPrice) > 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * 3-Way Match Check 2: Billed quantities match confirmed physically received quantities
     * Menggunakan toleransi floating-point 0.001 untuk menghindari kendala pembulatan desimal
     */
    public function checkQuantityMatch(): bool
    {
        foreach ($this->invoiceLines as $line) {
            $qtyReceived = $line->deliveryItem->qty_received;
            if ($qtyReceived === null || abs((float) $line->qty - (float) $qtyReceived) > 0.001) {
                return false;
            }
        }

        return true;
    }

    /**
     * Full 3-Way Match Evaluation
     */
    public function isThreeWayMatched(): bool
    {
        return ! $this->hasDuplicateNumber() && $this->checkPriceMatch() && $this->checkQuantityMatch();
    }
}

