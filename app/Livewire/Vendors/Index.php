<?php

namespace App\Livewire\Vendors;

use App\Models\AuditLog;
use App\Models\Vendor;
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

    // Form Modal state
    public bool $showModal = false;
    public ?int $editingVendorId = null;
    public string $name = '';
    public string $contact = '';
    public string $address = '';
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'contact' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        Gate::authorize('create', Vendor::class);

        $this->reset(['name', 'contact', 'address', 'editingVendorId']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(Vendor $vendor): void
    {
        Gate::authorize('update', $vendor);

        $this->editingVendorId = $vendor->id;
        $this->name = $vendor->name;
        $this->contact = $vendor->contact ?? '';
        $this->address = $vendor->address ?? '';
        $this->is_active = $vendor->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->editingVendorId) {
            $vendor = Vendor::findOrFail($this->editingVendorId);
            Gate::authorize('update', $vendor);
            $this->validate();

            $oldValues = $vendor->only(['name', 'contact', 'address', 'is_active']);
            $vendor->update([
                'name' => $this->name,
                'contact' => $this->contact ?: null,
                'address' => $this->address ?: null,
                'is_active' => $this->is_active,
            ]);

            AuditLog::log('updated', $vendor, $vendor->only(['name', 'contact', 'address', 'is_active']), $oldValues);
            session()->flash('success', 'Data vendor berhasil diperbarui.');
        } else {
            Gate::authorize('create', Vendor::class);
            $this->validate();

            $vendor = Vendor::create([
                'name' => $this->name,
                'contact' => $this->contact ?: null,
                'address' => $this->address ?: null,
                'is_active' => $this->is_active,
            ]);

            AuditLog::log('created', $vendor, $vendor->only(['name', 'contact', 'address', 'is_active']));
            session()->flash('success', 'Vendor baru berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function toggleActive(Vendor $vendor): void
    {
        Gate::authorize('update', $vendor);

        $vendor->update(['is_active' => ! $vendor->is_active]);
        AuditLog::log('status_toggled', $vendor, ['is_active' => $vendor->is_active]);
        session()->flash('success', 'Status vendor diperbarui.');
    }

    public function render()
    {
        Gate::authorize('viewAny', Vendor::class);

        $vendors = Vendor::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('contact', 'like', "%{$this->search}%")
                        ->orWhere('address', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status !== 'all', function ($query) {
                $query->where('is_active', $this->status === 'active');
            })
            ->withCount(['purchaseOrders', 'deliveries', 'invoices'])
            ->latest()
            ->paginate(10);

        return view('livewire.vendors.index', compact('vendors'));
    }
}

