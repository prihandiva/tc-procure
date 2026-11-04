<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait HasAuditTrail
{
    protected static function bootHasAuditTrail(): void
    {
        static::creating(function ($model) {
            if (Auth::check() && ! $model->created_by) {
                $model->created_by = Auth::id();
            }
            if (Auth::check() && ! $model->updated_by) {
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

