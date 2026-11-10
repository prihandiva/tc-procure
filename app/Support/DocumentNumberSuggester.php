<?php

namespace App\Support;

use App\Models\PurchaseOrder;

class DocumentNumberSuggester
{
    /**
     * Suggest a unique document number derived from a Purchase Order number.
     * Uses the raw PO number when available, otherwise appends `-2`, `-3`, etc.
     *
     * @param  callable(string): bool  $existsChecker
     */
    public static function fromPo(PurchaseOrder $po, callable $existsChecker): string
    {
        $base = trim($po->po_number);

        if (! $existsChecker($base)) {
            return $base;
        }

        $counter = 2;
        while ($existsChecker("{$base}-{$counter}")) {
            $counter++;
        }

        return "{$base}-{$counter}";
    }
}
