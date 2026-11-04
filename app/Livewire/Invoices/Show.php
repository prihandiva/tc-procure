<?php

namespace App\Livewire\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\AuditLog;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Invoice $invoice;

    public bool $showRejectModal = false;
    public string $reject_reason = '';

    public function mount(Invoice $invoice): void
    {
        Gate::authorize('view', $invoice);

        $this->invoice = $invoice->load([
            'vendor',
            'purchaseOrder.poItems',
            'invoiceLines.deliveryItem.delivery',
            'invoiceLines.deliveryItem.poItem',
            'documents.uploader',
            'paymentRequests',
        ]);
    }

    public function approveInvoice(): void
    {
        Gate::authorize('approve', $this->invoice);

        DB::transaction(function () {
            $lockedInvoice = Invoice::where('id', $this->invoice->id)->lockForUpdate()->firstOrFail();

            if (! $lockedInvoice->isSubmitted()) {
                throw ValidationException::withMessages([
                    'status' => 'Status invoice tidak valid untuk disetujui.',
                ]);
            }

            $lockedInvoice->update([
                'status' => InvoiceStatus::APPROVED,
            ]);

            AuditLog::log('approved', $lockedInvoice, [
                'invoice_number' => $lockedInvoice->invoice_number,
                'total_amount' => $lockedInvoice->total_amount,
                'three_way_matched' => $lockedInvoice->isThreeWayMatched(),
            ]);

            session()->flash('success', "Tagihan {$lockedInvoice->invoice_number} berhasil disetujui (3-Way Match terverifikasi) dan siap masuk ke Payment Request.");
        });

        $this->invoice->refresh();
    }

    public function rejectInvoice(): void
    {
        Gate::authorize('reject', $this->invoice);

        $this->validate([
            'reject_reason' => 'required|string|min:5|max:1000',
        ], [
            'reject_reason.required' => 'Alasan penolakan invoice wajib diisi.',
            'reject_reason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        DB::transaction(function () {
            $lockedInvoice = Invoice::where('id', $this->invoice->id)->lockForUpdate()->firstOrFail();

            if (! $lockedInvoice->isSubmitted()) {
                throw ValidationException::withMessages([
                    'status' => 'Status invoice tidak valid untuk ditolak.',
                ]);
            }

            // Unlock delivery items so they can be re-billed
            foreach ($lockedInvoice->invoiceLines as $line) {
                $line->deliveryItem()->update(['is_invoiced' => false]);
            }

            $lockedInvoice->update([
                'status' => InvoiceStatus::REJECTED,
                'reject_reason' => $this->reject_reason,
            ]);

            AuditLog::log('rejected', $lockedInvoice, [
                'invoice_number' => $lockedInvoice->invoice_number,
                'reason' => $this->reject_reason,
            ]);

            session()->flash('success', "Tagihan {$lockedInvoice->invoice_number} ditolak. Baris pengiriman telah dilepas sehingga vendor dapat merevisi tagihan.");
        });

        $this->showRejectModal = false;
        $this->invoice->refresh();
    }

    public function render()
    {
        Gate::authorize('view', $this->invoice);

        $user = Auth::user();

        return view('livewire.invoices.show', [
            'isSpv' => $user->isSuperAdmin() || $user->isSpv(),
            'isVendor' => $user->isVendor(),
            'canVerify' => ($user->isSuperAdmin() || $user->isSpv()) && $this->invoice->isSubmitted(),
        ]);
    }
}

