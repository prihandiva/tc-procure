<?php

namespace App\Traits;

use App\Models\Scopes\VendorScope;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToVendor
{
    /**
     * Boot the BelongsToVendor trait.
     */
    protected static function bootBelongsToVendor(): void
    {
        static::addGlobalScope(new VendorScope);
    }

    /**
     * Relationship to Vendor
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}

