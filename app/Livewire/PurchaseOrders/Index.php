<?php

namespace App\Livewire\PurchaseOrders;

use App\Enums\PoStatus;
use App\Enums\PoType;
use App\Models\PurchaseOrder;
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
    public string $type = 'all';
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

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function updatingVendorId(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        Gate::authorize('viewAny', PurchaseOrder::class);

        $user = Auth::user();

        $query = PurchaseOrder::query()
            ->with(['vendor', 'poItems.deliveryItems.delivery'])
            ->withCount(['deliveries', 'invoices'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('po_number', 'like', "%{$this->search}%")
                        ->orWhere('notes', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->type !== 'all', fn ($q) => $q->where('type', $this->type))
            ->when($this->vendor_id !== 'all' && ! $user->isVendor(), fn ($q) => $q->where('vendor_id', $this->vendor_id))
            ->when($this->date_from, fn ($q) => $q->whereDate('po_date', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('po_date', '<=', $this->date_to))
            ->latest('po_date');

        $purchaseOrders = $query->paginate(10);

        $vendors = $user->isVendor() ? collect() : Vendor::active()->orderBy('name')->get();

        return view('livewire.purchase-orders.index', [
            'purchaseOrders' => $purchaseOrders,
            'vendors' => $vendors,
            'isVendor' => $user->isVendor(),
        ]);
    }
}

