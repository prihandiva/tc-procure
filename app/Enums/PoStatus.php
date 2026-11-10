<?php

namespace App\Enums;

enum PoStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::ACTIVE => 'Aktif',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'zinc',
            self::ACTIVE => 'emerald',
            self::COMPLETED => 'indigo',
            self::CANCELLED => 'rose',
        };
    }

    public function badgeColor(): string
    {
        return $this->color();
    }
}

