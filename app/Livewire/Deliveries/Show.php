<?php

namespace App\Livewire\Deliveries;

use App\Enums\DeliveryStatus;
use App\Models\AuditLog;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\PoItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Delivery $delivery;

    // SPV Confirmation inputs
    public array $receivedQuantities = [];
    public bool $showRejectModal = false;
    public string $reject_reason = '';

    public function mount(Delivery $delivery): void
    {
        Gate::authorize('view', $delivery);

        $this->delivery = $delivery->load([
            'purchaseOrder.poItems',
            'vendor',
            'deliveryItems.poItem',
            'submitter',
            'confirmer',
            'documents.uploader',
        ]);

        foreach ($this->delivery->deliveryItems as $item) {
            $this->receivedQuantities[$item->id] = $item->qty_received !== null
                ? (float) $item->qty_received
                : (float) $item->qty_sent;
        }
    }

    public function confirmDelivery(): void
    {
        Gate::authorize('confirm', $this->delivery);

        $this->validate([
            'receivedQuantities.*' => 'required|numeric|min:0',
        ], [
            'receivedQuantities.*.required' => 'Kuantitas diterima harus diisi.',
            'receivedQuantities.*.min' => 'Kuantitas diterima minimal 0.',
        ]);

        DB::transaction(function () {
            $lockedDelivery = Delivery::where('id', $this->delivery->id)->lockForUpdate()->firstOrFail();

            if (! $lockedDelivery->isSubmitted()) {
                throw ValidationException::withMessages([
                    'status' => 'Pengiriman ini sudah diproses sebelumnya.',
                ]);
            }

            foreach ($this->delivery->deliveryItems as $item) {
                $qtyReceived = (float) ($this->receivedQuantities[$item->id] ?? $item->qty_sent);

                // Lock PO Item to check over-delivery if received exceeds ordered
                $poItem = PoItem::where('id', $item->po_item_id)->lockForUpdate()->firstOrFail();

                $item->update([
                    'qty_received' => $qtyReceived,
                ]);
            }

            $lockedDelivery->update([
                'status' => DeliveryStatus::CONFIRMED,
                'confirmed_by' => Auth::id(),
                'confirmed_at' => now(),
            ]);

            AuditLog::log('confirmed', $lockedDelivery, [
                'code' => $lockedDelivery->code,
                'total_qty_received' => $lockedDelivery->total_qty_received,
                'has_discrepancy' => $lockedDelivery->hasDiscrepancy(),
            ]);

            session()->flash('success', "Penerimaan pengiriman {$lockedDelivery->code} berhasil dikonfirmasi. Kuota sisa PO telah diperbarui.");
        });

        $this->delivery->refresh();
    }

    public function rejectDelivery(): void
    {
        Gate::authorize('reject', $this->delivery);

        $this->validate([
            'reject_reason' => 'required|string|min:5|max:1000',
        ], [
            'reject_reason.required' => 'Alasan penolakan pengiriman wajib diisi.',
            'reject_reason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        DB::transaction(function () {
            $lockedDelivery = Delivery::where('id', $this->delivery->id)->lockForUpdate()->firstOrFail();

            if (! $lockedDelivery->isSubmitted()) {
                throw ValidationException::withMessages([
                    'status' => 'Pengiriman ini sudah diproses sebelumnya.',
                ]);
            }

            // In reject, qty_received is set to 0 (no PO deduction)
            foreach ($this->delivery->deliveryItems as $item) {
                $item->update([
                    'qty_received' => 0,
                ]);
            }

            $lockedDelivery->update([
                'status' => DeliveryStatus::REJECTED,
                'reject_reason' => $this->reject_reason,
                'confirmed_by' => Auth::id(),
                'confirmed_at' => now(),
            ]);

            AuditLog::log('rejected', $lockedDelivery, [
                'code' => $lockedDelivery->code,
                'reason' => $this->reject_reason,
            ]);

            session()->flash('success', "Pengiriman {$lockedDelivery->code} ditolak dengan alasan yang tercatat.");
        });

        $this->showRejectModal = false;
        $this->delivery->refresh();
    }

    public function render()
    {
        Gate::authorize('view', $this->delivery);

        $user = Auth::user();

        return view('livewire.deliveries.show', [
            'canConfirm' => ($user->isSuperAdmin() || $user->isSpv()) && $this->delivery->isSubmitted(),
            'isSpv' => $user->isSuperAdmin() || $user->isSpv(),
            'isVendor' => $user->isVendor(),
        ]);
    }
}

