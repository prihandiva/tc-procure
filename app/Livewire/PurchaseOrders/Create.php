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
     * @var array<int, array{item_id: int|null, item_name: string, unit: string, qty_ordered: float|int, unit_price: int, subtotal: int}>
     */
    public array $items = [];

    public function mount(): void
    {
        Gate::authorize('create', PurchaseOrder::class);

        $this->po_date = now()->format('Y-m-d');
        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
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

    public function updatedItems($value, $key): void
    {
        // Example key: "0.item_id", "0.qty_ordered", "0.unit_price"
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $index = (int) $parts[0];
            $field = $parts[1];

            if ($field === 'item_id' && ! empty($value)) {
                $masterItem = Item::find($value);
                if ($masterItem) {
                    $this->items[$index]['item_name'] = $masterItem->name;
                    $this->items[$index]['unit'] = $masterItem->unit;
                    $this->items[$index]['unit_price'] = (int) ($masterItem->default_price ?? 0);
                }
            }

            // Recalculate subtotal for this item
            $qty = (float) ($this->items[$index]['qty_ordered'] ?? 0);
            $price = (int) ($this->items[$index]['unit_price'] ?? 0);
            $this->items[$index]['subtotal'] = (int) round($qty * $price);
        }
    }

    public function getTotalAmountProperty(): int
    {
        return (int) collect($this->items)->sum(function ($item) {
            $val = $item['subtotal'] ?? 0;

            return is_numeric($val) ? (int) $val : 0;
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
            'items.*.qty_ordered.min' => 'Kuantitas order minimal 0.01.',
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
                    'item_id' => $itemData['item_id'] ?: null,
                    'item_name' => $itemData['item_name'],
                    'unit' => $itemData['unit'],
                    'qty_ordered' => $itemData['qty_ordered'],
                    'unit_price' => $itemData['unit_price'],
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

        return view('livewire.purchase-orders.create', [
            'vendors' => $vendors,
            'masterItems' => $masterItems,
        ]);
    }
}

