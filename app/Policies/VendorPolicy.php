<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor;

class VendorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isSpv();
    }

    public function view(User $user, Vendor $vendor): bool
    {
        if ($user->isSuperAdmin() || $user->isSpv()) {
            return true;
        }

        return $user->isVendor() && $user->vendor_id === $vendor->id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Vendor $vendor): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return $user->isSuperAdmin();
    }
}

