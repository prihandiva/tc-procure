<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPERADMIN = 'superadmin';
    case SPV = 'spv';
    case VENDOR = 'vendor';

    /**
     * Get human-readable label in Indonesian
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPERADMIN => 'Super Admin',
            self::SPV => 'Supervisor (SPV)',
            self::VENDOR => 'Vendor Catering',
        };
    }
}

