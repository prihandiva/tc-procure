<?php

namespace App\Policies;

use App\Models\Posting;
use App\Models\User;

class PostingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Posting $posting): bool
    {
        if ($user->isSuperAdmin() || $user->isSpv()) {
            return true;
        }

        if ($user->isVendor()) {
            return $posting->paymentRequests()
                ->whereHas('invoices', fn ($q) => $q->where('vendor_id', $user->vendor_id))
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isSpv();
    }

    public function delete(User $user, Posting $posting): bool
    {
        return false;
    }
}

