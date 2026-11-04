<?php

namespace App\Livewire\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all';
    public string $vendor_id = 'all';
    public string $date_from = '';
    public string $date_to = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingVendorId(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        Gate::authorize('viewAny', Invoice::class);

        $user = Auth::user();

        $invoices = Invoice::query()
            ->with(['purchaseOrder', 'vendor', 'invoiceLines.deliveryItem.delivery', 'invoiceLines.deliveryItem.poItem'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('invoice_number', 'like', "%{$this->search}%")
                        ->orWhereHas('purchaseOrder', fn ($p) => $p->where('po_number', 'like', "%{$this->search}%"))
                        ->orWhereHas('vendor', fn ($v) => $v->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->vendor_id !== 'all' && ! $user->isVendor(), fn ($q) => $q->where('vendor_id', $this->vendor_id))
            ->when($this->date_from, fn ($q) => $q->whereDate('invoice_date', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('invoice_date', '<=', $this->date_to))
            ->latest('invoice_date')
            ->latest('id')
            ->paginate(12);

        $vendors = $user->isVendor() ? collect() : Vendor::active()->orderBy('name')->get();

        return view('livewire.invoices.index', [
            'invoices' => $invoices,
            'vendors' => $vendors,
            'isVendor' => $user->isVendor(),
            'isSpv' => $user->isSpv() || $user->isSuperAdmin(),
        ]);
    }
}

