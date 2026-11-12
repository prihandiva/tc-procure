<?php

namespace App\Livewire\Components;

use App\Services\ProcurementQueueService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class HeaderNotifications extends Component
{
    #[On('refresh-notifications')]
    public function refreshNotifications(): void
    {
        // Triggers component re-render when actions occur on the same page
    }

    public function render(ProcurementQueueService $queue)
    {
        $user = Auth::user();

        if (! $user) {
            return view('livewire.components.header-notifications', [
                'isVendor' => false,
                'totalCount' => 0,
                'incomingPos' => collect(),
                'pendingDos' => collect(),
                'readyInvoices' => collect(),
                'pendingInvoices' => collect(),
                'readyForPrInvoices' => collect(),
            ]);
        }

        $isVendor = $user->isVendor();

        $incomingPos = $queue->readyForDeliveryPos($user);
        $pendingDos = $queue->pendingVerificationDeliveries($user);
        $readyInvoices = $queue->readyForInvoicingPos($user);
        $pendingInvoices = $queue->pendingVerificationInvoices($user);
        $readyForPrInvoices = $isVendor ? collect() : $queue->readyForPaymentRequestInvoices($user);

        $totalCount = $isVendor
            ? ($incomingPos->count() + $pendingDos->count() + $readyInvoices->count() + $pendingInvoices->count())
            : ($incomingPos->count() + $pendingDos->count() + $pendingInvoices->count() + $readyForPrInvoices->count());

        return view('livewire.components.header-notifications', [
            'isVendor' => $isVendor,
            'totalCount' => $totalCount,
            'incomingPos' => $incomingPos,
            'pendingDos' => $pendingDos,
            'readyInvoices' => $readyInvoices,
            'pendingInvoices' => $pendingInvoices,
            'readyForPrInvoices' => $readyForPrInvoices,
        ]);
    }
}

