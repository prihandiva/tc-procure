<?php

namespace App\Policies;

use App\Models\PaymentStage;
use App\Models\User;

class PaymentStagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isSpv();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, PaymentStage $stage): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, PaymentStage $stage): bool
    {
        return $user->isSuperAdmin();
    }
}

