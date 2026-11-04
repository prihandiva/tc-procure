<?php

namespace App\Enums;

enum DocumentType: string
{
    case PO = 'po';
    case DELIVERY_ORDER = 'delivery_order';
    case INVOICE = 'invoice';
    case PAYMENT_REQUEST = 'payment_request';
    case PAYMENT_PROOF = 'payment_proof';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PO => 'Dokumen PO',
            self::DELIVERY_ORDER => 'Surat Jalan (DO)',
            self::INVOICE => 'Invoice / Faktur',
            self::PAYMENT_REQUEST => 'Lampiran Payment Request',
            self::PAYMENT_PROOF => 'Bukti Bayar / Posting',
            self::OTHER => 'Dokumen Lainnya',
        };
    }
}

