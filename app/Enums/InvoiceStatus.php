<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case IN_PAYMENT = 'in_payment';
    case PAID = 'paid';
    case VOID = 'void';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Diajukan',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::IN_PAYMENT => 'Dalam Proses Bayar',
            self::PAID => 'Lunas',
            self::VOID => 'Batal (Void)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'zinc',
            self::SUBMITTED => 'amber',
            self::APPROVED => 'indigo',
            self::REJECTED => 'rose',
            self::IN_PAYMENT => 'sky',
            self::PAID => 'green',
            self::VOID => 'zinc',
        };
    }

    public function badgeColor(): string
    {
        return $this->color();
    }
}

