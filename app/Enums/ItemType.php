<?php

namespace App\Enums;

enum ItemType: string
{
    case GOODS = 'goods';
    case SERVICE = 'service';

    public function label(): string
    {
        return match ($this) {
            self::GOODS => 'Barang',
            self::SERVICE => 'Jasa',
        };
    }
}

