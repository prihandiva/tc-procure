<?php

namespace App\Livewire\Items;

use App\Enums\ItemType;
use App\Models\AuditLog;
use App\Models\Item;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $type = 'all';
    public string $status = 'all';

    // Form Modal state
    public bool $showModal = false;
    public ?int $editingItemId = null;
    public string $name = '';
    public string $unit = 'pc';
    public string $itemType = 'goods';
    public ?int $default_price = null;
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:255',
            'unit' => 'required|string|max:50',
            'itemType' => 'required|in:goods,service',
            'default_price' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        Gate::authorize('create', Item::class);

        $this->reset(['name', 'editingItemId', 'default_price']);
        $this->unit = 'pc';
        $this->itemType = 'goods';
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(Item $item): void
    {
        Gate::authorize('update', $item);

        $this->editingItemId = $item->id;
        $this->name = $item->name;
        $this->unit = $item->unit;
        $this->itemType = $item->type->value;
        $this->default_price = $item->default_price;
        $this->is_active = $item->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->editingItemId) {
            $item = Item::findOrFail($this->editingItemId);
            Gate::authorize('update', $item);
            $this->validate();

            $oldValues = $item->only(['name', 'unit', 'type', 'default_price', 'is_active']);
            $item->update([
                'name' => $this->name,
                'unit' => $this->unit,
                'type' => ItemType::from($this->itemType),
                'default_price' => $this->default_price ?: null,
                'is_active' => $this->is_active,
            ]);

            AuditLog::log('updated', $item, $item->only(['name', 'unit', 'type', 'default_price', 'is_active']), $oldValues);
            session()->flash('success', 'Master item berhasil diperbarui.');
        } else {
            Gate::authorize('create', Item::class);
            $this->validate();

            $item = Item::create([
                'name' => $this->name,
                'unit' => $this->unit,
                'type' => ItemType::from($this->itemType),
                'default_price' => $this->default_price ?: null,
                'is_active' => $this->is_active,
            ]);

            AuditLog::log('created', $item, $item->only(['name', 'unit', 'type', 'default_price', 'is_active']));
            session()->flash('success', 'Master item baru berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function toggleActive(Item $item): void
    {
        Gate::authorize('update', $item);

        $item->update(['is_active' => ! $item->is_active]);
        AuditLog::log('status_toggled', $item, ['is_active' => $item->is_active]);
        session()->flash('success', 'Status master item diperbarui.');
    }

    public function render()
    {
        Gate::authorize('viewAny', Item::class);

        $items = Item::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('unit', 'like', "%{$this->search}%");
                });
            })
            ->when($this->type !== 'all', function ($query) {
                $query->where('type', $this->type);
            })
            ->when($this->status !== 'all', function ($query) {
                $query->where('is_active', $this->status === 'active');
            })
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.items.index', compact('items'));
    }
}

