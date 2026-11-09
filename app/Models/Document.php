<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'type',
        'path',
        'original_name',
        'mime',
        'size',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'size' => 'integer',
        ];
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime ?? '', 'image/')
            || in_array(strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    }

    public function isPdf(): bool
    {
        return ($this->mime === 'application/pdf')
            || strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION)) === 'pdf';
    }
}

