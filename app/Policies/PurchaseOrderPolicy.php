<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if ($user->isSuperAdmin() || $user->isSpv()) {
            return true;
        }

        return $user->isVendor() && $user->vendor_id === $purchaseOrder->vendor_id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isSpv();
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if (! ($user->isSuperAdmin() || $user->isSpv())) {
            return false;
        }

        // Once deliveries exist, PO items & prices cannot be modified
        return ! $purchaseOrder->deliveries()->exists();
    }

    public function cancel(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if (! ($user->isSuperAdmin() || $user->isSpv())) {
            return false;
        }

        return ! $purchaseOrder->deliveries()->where('status', 'confirmed')->exists();
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // No hard delete allowed for transactions
        return false;
    }
}

