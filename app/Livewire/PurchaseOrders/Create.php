<?php

namespace App\Livewire\PurchaseOrders;

use App\Enums\DocumentType;
use App\Enums\PoStatus;
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
class Create extends Component
{
    use WithFileUploads;

    public string $po_number = '';
    public string $type = 'po';
    public ?int $vendor_id = null;
    public string $po_date = '';
    public string $notes = '';
    public $document_file = null;

    /**
     * @var array<int, array{_key?: string, item_id: int|null, item_name: string, unit: string, qty_ordered: float|int|string, unit_price: int|string, subtotal: int}>
     */
    public array $items = [];

    /**
     * Tracks rows whose fields were edited manually in the current request batch
     * so deferred catalog item_id updates never overwrite custom user edits.
     */
    protected array $manuallyEditedNameRows = [];
    protected array $manuallyEditedUnitRows = [];
    protected array $manuallyEditedPriceRows = [];

    public function mount(): void
    {
        Gate::authorize('create', PurchaseOrder::class);

        $this->po_date = now()->format('Y-m-d');
        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            '_key' => Str::random(8),
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
        // Example key: "0.item_id", "0.qty_ordered", "0.unit_price"
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

            // Recalculate subtotal for this item
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
            'po_number' => 'required|string|max:100|unique:purchase_orders,po_number',
            'type' => 'required|in:po,non_po',
            'vendor_id' => 'required|exists:vendors,id',
            'po_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // max 5MB
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.unit' => 'required|string|max:50',
            'items.*.qty_ordered' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|integer|min:0',
        ];
    }

    protected function messages(): array
    {
        return [
            'po_number.required' => 'Nomor PO wajib diisi.',
            'po_number.unique' => 'Nomor PO ini sudah terdaftar sebelumnya.',
            'vendor_id.required' => 'Silakan pilih vendor catering.',
            'po_date.required' => 'Tanggal PO wajib diisi.',
            'document_file.mimes' => 'Format file dokumen harus PDF, JPG, JPEG, atau PNG.',
            'document_file.max' => 'Ukuran file dokumen maksimal 5 MB.',
            'items.min' => 'PO minimal harus memiliki 1 item barang/jasa.',
            'items.*.item_name.required' => 'Nama item wajib diisi.',
            'items.*.unit.required' => 'Satuan item wajib diisi.',
            'items.*.qty_ordered.required' => 'Kuantitas wajib diisi.',
            'items.*.qty_ordered.min' => 'Kuantitas order minimal 0.01.',
            'items.*.unit_price.required' => 'Harga satuan wajib diisi.',
            'items.*.unit_price.integer' => 'Harga satuan harus berupa angka bulat.',
            'items.*.unit_price.min' => 'Harga satuan tidak boleh negatif.',
        ];
    }

    public function save()
    {
        Gate::authorize('create', PurchaseOrder::class);
        $this->validate();

        return DB::transaction(function () {
            // 1. Create Purchase Order
            $po = PurchaseOrder::create([
                'po_number' => trim($this->po_number),
                'type' => PoType::from($this->type),
                'vendor_id' => $this->vendor_id,
                'po_date' => $this->po_date,
                'status' => PoStatus::ACTIVE,
                'notes' => $this->notes ?: null,
            ]);

            // 2. Create PO Items
            foreach ($this->items as $itemData) {
                PoItem::create([
                    'purchase_order_id' => $po->id,
                    'item_id' => ! empty($itemData['item_id']) ? (int) $itemData['item_id'] : null,
                    'item_name' => trim($itemData['item_name']),
                    'unit' => trim($itemData['unit']),
                    'qty_ordered' => (float) $itemData['qty_ordered'],
                    'unit_price' => (int) $itemData['unit_price'],
                ]);
            }

            // 3. Handle document upload if provided
            if ($this->document_file) {
                $path = $this->document_file->store('documents/po', 'local');

                Document::create([
                    'documentable_type' => PurchaseOrder::class,
                    'documentable_id' => $po->id,
                    'type' => DocumentType::PO,
                    'path' => $path,
                    'original_name' => $this->document_file->getClientOriginalName(),
                    'mime' => $this->document_file->getMimeType(),
                    'size' => $this->document_file->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);
            }

            // 4. Audit Log
            AuditLog::log('created', $po, [
                'po_number' => $po->po_number,
                'vendor_id' => $po->vendor_id,
                'type' => $po->type->value,
                'items_count' => count($this->items),
                'total_amount' => $po->total_amount,
            ]);

            session()->flash('success', "Purchase Order {$po->po_number} berhasil diterbitkan.");

            return redirect()->route('purchase-orders.show', $po);
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

        return view('livewire.purchase-orders.create', [
            'vendors' => $vendors,
            'masterItems' => $masterItems,
            'catalogMap' => $catalogMap,
        ]);
    }
}

