<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->isSuperAdmin() || $user->isSpv()) {
            return true;
        }

        return $user->isVendor() && $user->vendor_id === $invoice->vendor_id;
    }

    public function create(User $user): bool
    {
        if ($user->isVendor()) {
            return ! empty($user->vendor_id);
        }

        return $user->isSuperAdmin() || $user->isSpv();
    }

    public function approve(User $user, Invoice $invoice): bool
    {
        return ($user->isSuperAdmin() || $user->isSpv()) && $invoice->isSubmitted();
    }

    public function reject(User $user, Invoice $invoice): bool
    {
        return ($user->isSuperAdmin() || $user->isSpv()) && $invoice->isSubmitted();
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return false;
    }
}

