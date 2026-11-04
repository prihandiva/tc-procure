<?php

namespace App\Policies;

use App\Models\Delivery;
use App\Models\User;

class DeliveryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Delivery $delivery): bool
    {
        if ($user->isSuperAdmin() || $user->isSpv()) {
            return true;
        }

        return $user->isVendor() && $user->vendor_id === $delivery->vendor_id;
    }

    public function create(User $user): bool
    {
        // Vendor or SPV/Superadmin can create a delivery
        if ($user->isVendor()) {
            return ! empty($user->vendor_id);
        }

        return $user->isSuperAdmin() || $user->isSpv();
    }

    public function confirm(User $user, Delivery $delivery): bool
    {
        // Only SPV or Superadmin can confirm received goods
        return ($user->isSuperAdmin() || $user->isSpv()) && $delivery->isSubmitted();
    }

    public function reject(User $user, Delivery $delivery): bool
    {
        // Only SPV or Superadmin can reject
        return ($user->isSuperAdmin() || $user->isSpv()) && $delivery->isSubmitted();
    }

    public function delete(User $user, Delivery $delivery): bool
    {
        return false;
    }
}

