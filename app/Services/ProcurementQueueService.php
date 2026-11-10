<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PoStatus;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Collection;

class ProcurementQueueService
{
    /**
     * Active POs that still have remaining physical delivery quota.
     *
     * @return Collection<int, PurchaseOrder>
     */
    public function readyForDeliveryPos(User $user, ?int $limit = null): Collection
    {
        $pos = PurchaseOrder::query()
            ->when($user->isVendor(), fn ($q) => $q->where('vendor_id', $user->vendor_id))
            ->where('status', PoStatus::ACTIVE->value)
            ->with(['vendor', 'poItems.deliveryItems.delivery'])
            ->latest('po_date')
            ->latest('id')
            ->get()
            ->filter(fn (PurchaseOrder $po) => $po->total_qty_remaining > 0)
            ->values();

        return $limit ? $pos->take($limit) : $pos;
    }

    /**
     * Deliveries submitted by vendor waiting for Tim TC physical confirmation.
     *
     * @return Collection<int, Delivery>
     */
    public function pendingVerificationDeliveries(User $user, ?int $limit = null): Collection
    {
        $query = Delivery::query()
            ->when($user->isVendor(), fn ($q) => $q->where('vendor_id', $user->vendor_id))
            ->where('status', DeliveryStatus::SUBMITTED->value)
            ->with(['vendor', 'purchaseOrder', 'deliveryItems'])
            ->latest('delivery_date')
            ->latest('id');

        return $limit ? $query->take($limit)->get() : $query->get();
    }

    /**
     * POs that have confirmed deliveries with uninvoiced delivery items.
     * Returns mapped summary arrays ready for cards and notification bell.
     *
     * @return Collection<int, array{po: PurchaseOrder, deliveries_count: int, delivery_codes: array<int, string>, items_count: int, estimated_amount: int}>
     */
    public function readyForInvoicingPos(User $user, ?int $limit = null): Collection
    {
        $pos = PurchaseOrder::query()
            ->when($user->isVendor(), fn ($q) => $q->where('vendor_id', $user->vendor_id))
            ->whereHas('deliveries', function ($d) {
                $d->where('status', DeliveryStatus::CONFIRMED->value)
                    ->whereHas('deliveryItems', fn ($di) => $di->where('is_invoiced', false));
            })
            ->with([
                'vendor',
                'deliveries' => function ($d) {
                    $d->where('status', DeliveryStatus::CONFIRMED->value)
                        ->whereHas('deliveryItems', fn ($di) => $di->where('is_invoiced', false))
                        ->with(['deliveryItems' => fn ($di) => $di->where('is_invoiced', false)->with('poItem')]);
                },
            ])
            ->latest('po_date')
            ->latest('id')
            ->get()
            ->map(function (PurchaseOrder $po) {
                $unbilledItems = $po->deliveries->flatMap->deliveryItems;
                $estimatedAmount = (int) $unbilledItems->sum(function ($item) {
                    $qty = (float) ($item->qty_received ?? $item->qty_sent);
                    $price = (int) ($item->poItem->unit_price ?? 0);

                    return (int) round($qty * $price);
                });

                return [
                    'po' => $po,
                    'deliveries_count' => $po->deliveries->count(),
                    'delivery_codes' => $po->deliveries->pluck('code')->all(),
                    'items_count' => $unbilledItems->count(),
                    'estimated_amount' => $estimatedAmount,
                ];
            })
            ->values();

        return $limit ? $pos->take($limit) : $pos;
    }

    /**
     * Invoices waiting for Tim TC 3-way match verification (or rejected needing vendor revision).
     *
     * @return Collection<int, Invoice>
     */
    public function pendingVerificationInvoices(User $user, ?int $limit = null): Collection
    {
        $statuses = $user->isVendor()
            ? [InvoiceStatus::SUBMITTED->value, InvoiceStatus::REJECTED->value]
            : [InvoiceStatus::SUBMITTED->value];

        $query = Invoice::query()
            ->when($user->isVendor(), fn ($q) => $q->where('vendor_id', $user->vendor_id))
            ->whereIn('status', $statuses)
            ->with(['vendor', 'purchaseOrder'])
            ->latest('invoice_date')
            ->latest('id');

        return $limit ? $query->take($limit)->get() : $query->get();
    }

    /**
     * Approved invoices ready to be bundled into a Payment Request (SPV / Superadmin only).
     *
     * @return Collection<int, Invoice>
     */
    public function readyForPaymentRequestInvoices(User $user, ?int $limit = null): Collection
    {
        if ($user->isVendor()) {
            return collect();
        }

        $query = Invoice::query()
            ->where('status', InvoiceStatus::APPROVED->value)
            ->whereDoesntHave('paymentRequests')
            ->with(['vendor', 'purchaseOrder'])
            ->latest('invoice_date')
            ->latest('id');

        return $limit ? $query->take($limit)->get() : $query->get();
    }
}
