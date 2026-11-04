<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\User;

class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        if ($user->isSuperAdmin() || $user->isSpv()) {
            return true;
        }

        if ($user->isVendor()) {
            $parent = $document->documentable;
            if ($parent instanceof PurchaseOrder) {
                return $parent->vendor_id === $user->vendor_id;
            }
            if ($parent instanceof Invoice) {
                return $parent->vendor_id === $user->vendor_id;
            }
            if (isset($parent->vendor_id)) {
                return $parent->vendor_id === $user->vendor_id;
            }
        }

        return false;
    }

    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->isSuperAdmin() || $user->isSpv();
    }
}

