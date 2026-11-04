<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case SUBMITTED = 'submitted';
    case CONFIRMED = 'confirmed';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::SUBMITTED => 'Menunggu Konfirmasi',
            self::CONFIRMED => 'Terkonfirmasi',
            self::REJECTED => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SUBMITTED => 'amber',
            self::CONFIRMED => 'green',
            self::REJECTED => 'rose',
        };
    }

    public function badgeColor(): string
    {
        return $this->color();
    }
}

