<?php

namespace App\Models;

use App\Enums\ItemType;
use App\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasAuditTrail, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'unit',
        'type',
        'default_price',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => ItemType::class,
            'default_price' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isService(): bool
    {
        return $this->type === ItemType::SERVICE;
    }

    public function isGoods(): bool
    {
        return $this->type === ItemType::GOODS;
    }

    public function poItems(): HasMany
    {
        return $this->hasMany(PoItem::class);
    }
}

