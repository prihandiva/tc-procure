<?php

namespace App\Livewire\PurchaseOrders;

use App\Enums\DocumentType;
use App\Enums\PoType;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Item;
use App\Models\PoItem;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Edit extends Component
{
    use WithFileUploads;

    public PurchaseOrder $purchaseOrder;

    public string $po_number = '';
    public string $type = 'po';
    public ?int $vendor_id = null;
    public string $po_date = '';
    public string $notes = '';
    public $document_file = null;

    /**
     * @var array<int, array{_key?: string, id: int|null, item_id: int|null, item_name: string, unit: string, qty_ordered: float|int|string, unit_price: int|string, subtotal: int}>
     */
    public array $items = [];

    protected array $manuallyEditedNameRows = [];
    protected array $manuallyEditedUnitRows = [];
    protected array $manuallyEditedPriceRows = [];

    public function mount(PurchaseOrder $purchaseOrder): void
    {
        Gate::authorize('update', $purchaseOrder);

        if (! $purchaseOrder->canBeEdited()) {
            abort(403, 'Purchase Order ini sudah memiliki pengiriman barang sehingga tidak dapat diubah.');
        }

        $this->purchaseOrder = $purchaseOrder;
        $this->po_number = $purchaseOrder->po_number;
        $this->type = $purchaseOrder->type->value;
        $this->vendor_id = $purchaseOrder->vendor_id;
        $this->po_date = $purchaseOrder->po_date->format('Y-m-d');
        $this->notes = $purchaseOrder->notes ?? '';

        foreach ($purchaseOrder->poItems as $item) {
            $this->items[] = [
                '_key' => 'db-' . $item->id,
                'id' => $item->id,
                'item_id' => $item->item_id,
                'item_name' => $item->item_name,
                'unit' => $item->unit,
                'qty_ordered' => (float) $item->qty_ordered,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
            ];
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            '_key' => Str::random(8),
            'id' => null,
            'item_id' => null,
            'item_name' => '',
            'unit' => 'pc',
            'qty_ordered' => 1,
            'unit_price' => 0,
            'subtotal' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    public function updatingItems($value, $key): void
    {
        $parts = explode('.', (string) $key);
        if (count($parts) === 2) {
            $index = (int) $parts[0];
            $field = $parts[1];

            if ($field === 'item_name') {
                $this->manuallyEditedNameRows[$index] = true;
            } elseif ($field === 'unit') {
                $this->manuallyEditedUnitRows[$index] = true;
            } elseif ($field === 'unit_price') {
                $this->manuallyEditedPriceRows[$index] = true;
            }
        }
    }

    public function updatedItems($value, $key): void
    {
        $parts = explode('.', (string) $key);
        if (count($parts) === 2) {
            $index = (int) $parts[0];
            $field = $parts[1];

            if (! isset($this->items[$index])) {
                return;
            }

            if ($field === 'item_id' && ! empty($value)) {
                $masterItem = Item::find($value);
                if ($masterItem) {
                    if (empty($this->manuallyEditedNameRows[$index])) {
                        $this->items[$index]['item_name'] = $masterItem->name;
                    }
                    if (empty($this->manuallyEditedUnitRows[$index])) {
                        $this->items[$index]['unit'] = $masterItem->unit;
                    }
                    if (empty($this->manuallyEditedPriceRows[$index])) {
                        $this->items[$index]['unit_price'] = (int) ($masterItem->default_price ?? 0);
                    }
                }
            }

            $qty = is_numeric($this->items[$index]['qty_ordered'] ?? null) ? (float) $this->items[$index]['qty_ordered'] : 0;
            $price = is_numeric($this->items[$index]['unit_price'] ?? null) ? (int) $this->items[$index]['unit_price'] : 0;
            $this->items[$index]['subtotal'] = (int) round($qty * $price);
        }
    }

    public function getTotalAmountProperty(): int
    {
        return (int) collect($this->items)->sum(function ($item) {
            $qty = is_numeric($item['qty_ordered'] ?? null) ? (float) $item['qty_ordered'] : 0;
            $price = is_numeric($item['unit_price'] ?? null) ? (int) $item['unit_price'] : 0;

            return (int) round($qty * $price);
        });
    }

    protected function rules(): array
    {
        return [
            'po_number' => 'required|string|max:100|unique:purchase_orders,po_number,' . $this->purchaseOrder->id,
            'type' => 'required|in:po,non_po',
            'vendor_id' => 'required|exists:vendors,id',
            'po_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.unit' => 'required|string|max:50',
            'items.*.qty_ordered' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|integer|min:0',
        ];
    }

    public function save()
    {
        Gate::authorize('update', $this->purchaseOrder);

        if (! $this->purchaseOrder->canBeEdited()) {
            abort(403, 'PO tidak dapat diubah setelah ada pengiriman.');
        }

        $this->validate();

        return DB::transaction(function () {
            $oldValues = $this->purchaseOrder->only(['po_number', 'type', 'vendor_id', 'po_date', 'notes']);

            $this->purchaseOrder->update([
                'po_number' => trim($this->po_number),
                'type' => PoType::from($this->type),
                'vendor_id' => $this->vendor_id,
                'po_date' => $this->po_date,
                'notes' => $this->notes ?: null,
            ]);

            // Sync PO Items: delete missing, update existing, insert new
            $keptIds = [];
            foreach ($this->items as $itemData) {
                $payload = [
                    'item_id' => ! empty($itemData['item_id']) ? (int) $itemData['item_id'] : null,
                    'item_name' => trim($itemData['item_name']),
                    'unit' => trim($itemData['unit']),
                    'qty_ordered' => (float) $itemData['qty_ordered'],
                    'unit_price' => (int) $itemData['unit_price'],
                ];

                if (! empty($itemData['id'])) {
                    $item = PoItem::where('purchase_order_id', $this->purchaseOrder->id)->find($itemData['id']);
                    if ($item) {
                        $item->update($payload);
                        $keptIds[] = $item->id;
                        continue;
                    }
                }

                $newItem = PoItem::create(array_merge($payload, [
                    'purchase_order_id' => $this->purchaseOrder->id,
                ]));
                $keptIds[] = $newItem->id;
            }

            // Delete items that were removed
            PoItem::where('purchase_order_id', $this->purchaseOrder->id)
                ->whereNotIn('id', $keptIds)
                ->delete();

            if ($this->document_file) {
                $path = $this->document_file->store('documents/po', 'local');

                Document::create([
                    'documentable_type' => PurchaseOrder::class,
                    'documentable_id' => $this->purchaseOrder->id,
                    'type' => DocumentType::PO,
                    'path' => $path,
                    'original_name' => $this->document_file->getClientOriginalName(),
                    'mime' => $this->document_file->getMimeType(),
                    'size' => $this->document_file->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);
            }

            AuditLog::log('updated', $this->purchaseOrder, $this->purchaseOrder->only(['po_number', 'vendor_id', 'type']), $oldValues);

            session()->flash('success', "Purchase Order {$this->purchaseOrder->po_number} berhasil diperbarui.");

            return redirect()->route('purchase-orders.show', $this->purchaseOrder);
        });
    }

    public function render()
    {
        $vendors = Vendor::active()->orderBy('name')->get();
        $masterItems = Item::active()->orderBy('name')->get();
        $catalogMap = $masterItems->mapWithKeys(fn (Item $item) => [
            $item->id => [
                'name' => $item->name,
                'unit' => $item->unit,
                'default_price' => (int) ($item->default_price ?? 0),
            ],
        ])->all();

        return view('livewire.purchase-orders.edit', [
            'vendors' => $vendors,
            'masterItems' => $masterItems,
            'catalogMap' => $catalogMap,
        ]);
    }
}

