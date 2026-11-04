<?php

namespace App\Policies;

use App\Models\PaymentRequest;
use App\Models\User;

class PaymentRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PaymentRequest $paymentRequest): bool
    {
        if ($user->isSuperAdmin() || $user->isSpv()) {
            return true;
        }

        if ($user->isVendor()) {
            return $paymentRequest->invoices()
                ->where('vendor_id', $user->vendor_id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isSpv();
    }

    public function updateStage(User $user, PaymentRequest $paymentRequest): bool
    {
        return ($user->isSuperAdmin() || $user->isSpv()) && ! $paymentRequest->isPosted();
    }

    public function delete(User $user, PaymentRequest $paymentRequest): bool
    {
        return false;
    }
}

