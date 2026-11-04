<?php

namespace App\Enums;

enum PoType: string
{
    case PO = 'po';
    case NON_PO = 'non_po';

    public function label(): string
    {
        return match ($this) {
            self::PO => 'Purchase Order (PO)',
            self::NON_PO => 'Non-PO (Pembelian Langsung)',
        };
    }
}

