<?php

namespace App\Livewire\Deliveries;

use App\Enums\DeliveryStatus;
use App\Models\Delivery;
use App\Models\Vendor;
use App\Services\ProcurementQueueService;
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

    public function render(ProcurementQueueService $queue)
    {
        Gate::authorize('viewAny', Delivery::class);

        $user = Auth::user();
        $isSpv = $user->isSpv() || $user->isSuperAdmin();

        $deliveries = Delivery::query()
            ->with(['purchaseOrder', 'vendor', 'deliveryItems.poItem', 'submitter'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('code', 'like', "%{$this->search}%")
                        ->orWhere('vendor_do_number', 'like', "%{$this->search}%")
                        ->orWhere('notes', 'like', "%{$this->search}%")
                        ->orWhereHas('purchaseOrder', fn ($p) => $p->where('po_number', 'like', "%{$this->search}%"))
                        ->orWhereHas('deliveryItems', fn ($di) => $di->where('training_name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->vendor_id !== 'all' && ! $user->isVendor(), fn ($q) => $q->where('vendor_id', $this->vendor_id))
            ->when($this->date_from, fn ($q) => $q->whereDate('delivery_date', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('delivery_date', '<=', $this->date_to))
            ->latest('delivery_date')
            ->latest('id')
            ->paginate(12);

        $vendors = $user->isVendor() ? collect() : Vendor::active()->orderBy('name')->get();

        return view('livewire.deliveries.index', [
            'deliveries' => $deliveries,
            'vendors' => $vendors,
            'isVendor' => $user->isVendor(),
            'isSpv' => $isSpv,
            'readyForDeliveryPos' => $user->can('create', Delivery::class) ? $queue->readyForDeliveryPos($user) : collect(),
            'pendingVerificationDeliveries' => $isSpv ? $queue->pendingVerificationDeliveries($user) : collect(),
        ]);
    }
}

