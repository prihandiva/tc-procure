<?php

namespace App\Livewire\Deliveries;

use App\Enums\DeliveryStatus;
use App\Enums\DocumentType;
use App\Enums\PoStatus;
use App\Models\AuditLog;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Document;
use App\Models\PoItem;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Create extends Component
{
    use WithFileUploads;

    public ?int $purchase_order_id = null;
    public string $code = '';
    #[Locked]
    public string $auto_filled_code = '';
    public string $delivery_date = '';
    public string $vendor_do_number = '';
    public string $notes = '';
    public $document_file = null;

    /**
     * @var array<int, array{po_item_id: int, item_name: string, unit: string, qty_remaining: float, qty_sent: float|int, training_name: string, needed_date: string, variant_note: string}>
     */
    public array $items = [];

    public function mount(?int $po_id = null): void
    {
        Gate::authorize('create', Delivery::class);

        $this->delivery_date = now()->format('Y-m-d');

        if ($po_id) {
            $this->purchase_order_id = $po_id;
            $this->loadPoItems();
        }
    }

    public function updatedPurchaseOrderId(): void
    {
        $this->loadPoItems();
    }

    public function loadPoItems(): void
    {
        $this->items = [];

        if (! $this->purchase_order_id) {
            if (trim($this->code) === $this->auto_filled_code) {
                $this->code = '';
            }
            $this->auto_filled_code = '';

            return;
        }

        $po = PurchaseOrder::with('poItems.deliveryItems.delivery')->find($this->purchase_order_id);

        if (! $po) {
            if (trim($this->code) === $this->auto_filled_code) {
                $this->code = '';
            }
            $this->auto_filled_code = '';

            return;
        }

        Gate::authorize('view', $po);

        $suggested = Delivery::suggestCodeForPo($po);
        if (trim($this->code) === '' || trim($this->code) === $this->auto_filled_code) {
            $this->code = $suggested;
        }
        $this->auto_filled_code = $suggested;

        foreach ($po->poItems as $poItem) {
            $remaining = $poItem->qty_remaining;
            // Pre-fill item line
            $this->items[] = [
                'po_item_id' => $poItem->id,
                'item_name' => $poItem->item_name,
                'unit' => $poItem->unit,
                'qty_remaining' => (float) $remaining,
                'qty_sent' => 0,
                'training_name' => '',
                'needed_date' => $this->delivery_date,
                'variant_note' => '',
            ];
        }
    }

    public function getTotalQtySentProperty(): float
    {
        return (float) collect($this->items)->sum(function ($item) {
            $val = $item['qty_sent'] ?? 0;

            return is_numeric($val) ? (float) $val : 0.0;
        });
    }

    protected function rules(): array
    {
        return [
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'code' => 'required|string|max:100|unique:deliveries,code',
            'delivery_date' => 'required|date',
            'vendor_do_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'items' => 'required|array|min:1',
            'items.*.qty_sent' => 'required|numeric|min:0',
            'items.*.training_name' => 'nullable|string|max:255',
            'items.*.needed_date' => 'nullable|date',
            'items.*.variant_note' => 'nullable|string|max:500',
        ];
    }

    protected function messages(): array
    {
        return [
            'purchase_order_id.required' => 'Silakan pilih Purchase Order terlebih dahulu.',
            'code.required' => 'Nomor surat pengiriman (DO) wajib diisi.',
            'code.unique' => 'Nomor surat pengiriman (DO) ini sudah digunakan. Silakan ubah atau tambahkan akhiran (misal: -2) untuk pengiriman bertahap.',
            'delivery_date.required' => 'Tanggal pengiriman wajib diisi.',
            'document_file.mimes' => 'Format file dokumen DO harus PDF, JPG, JPEG, atau PNG.',
            'document_file.max' => 'Ukuran file dokumen maksimal 5 MB.',
        ];
    }

    public function save()
    {
        Gate::authorize('create', Delivery::class);

        if ($this->purchase_order_id
            && ! PurchaseOrder::where('id', $this->purchase_order_id)->exists()
            && PurchaseOrder::withoutGlobalScopes()->where('id', $this->purchase_order_id)->exists()
        ) {
            abort(404);
        }

        $this->code = trim($this->code);
        $this->validate();

        // Check that at least one item is being sent
        $sentItems = collect($this->items)->filter(function ($i) {
            $val = $i['qty_sent'] ?? 0;

            return is_numeric($val) && (float) $val > 0;
        });
        if ($sentItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Minimal salah satu item harus memiliki kuantitas kirim lebih dari 0.',
            ]);
        }

        return DB::transaction(function () use ($sentItems) {
            // Lock PO and PO items to prevent race conditions (section 8)
            $po = PurchaseOrder::where('id', $this->purchase_order_id)->lockForUpdate()->firstOrFail();
            Gate::authorize('view', $po);

            // Validate against current remaining quantities with lock
            foreach ($sentItems as $itemData) {
                $poItem = PoItem::where('id', $itemData['po_item_id'])
                    ->where('purchase_order_id', $po->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $remaining = $poItem->qty_remaining;
                $qtySent = (float) $itemData['qty_sent'];

                if ($qtySent > $remaining) {
                    throw ValidationException::withMessages([
                        'items' => "Kuantitas kirim untuk {$poItem->item_name} ({$qtySent} {$poItem->unit}) melebihi sisa PO ({$remaining} {$poItem->unit}).",
                    ]);
                }
            }

            $delivery = Delivery::create([
                'code' => trim($this->code),
                'purchase_order_id' => $po->id,
                'vendor_id' => $po->vendor_id,
                'delivery_date' => $this->delivery_date,
                'vendor_do_number' => $this->vendor_do_number ? trim($this->vendor_do_number) : null,
                'status' => DeliveryStatus::SUBMITTED,
                'submitted_by' => Auth::id(),
                'notes' => $this->notes ?: null,
            ]);

            foreach ($sentItems as $itemData) {
                DeliveryItem::create([
                    'delivery_id' => $delivery->id,
                    'po_item_id' => $itemData['po_item_id'],
                    'qty_sent' => $itemData['qty_sent'],
                    'qty_received' => null, // null until SPV confirmation
                    'training_name' => $itemData['training_name'] ?: null,
                    'needed_date' => $itemData['needed_date'] ?: null,
                    'variant_note' => $itemData['variant_note'] ?: null,
                    'is_invoiced' => false,
                ]);
            }

            if ($this->document_file) {
                $path = $this->document_file->store('documents/deliveries', 'local');

                Document::create([
                    'documentable_type' => Delivery::class,
                    'documentable_id' => $delivery->id,
                    'type' => DocumentType::DELIVERY_ORDER,
                    'path' => $path,
                    'original_name' => $this->document_file->getClientOriginalName(),
                    'mime' => $this->document_file->getMimeType(),
                    'size' => $this->document_file->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);
            }

            AuditLog::log('created', $delivery, [
                'code' => $delivery->code,
                'po_id' => $po->id,
                'vendor_do_number' => $delivery->vendor_do_number,
                'total_qty_sent' => $delivery->total_qty_sent,
            ]);

            session()->flash('success', "Pengiriman {$delivery->code} berhasil diajukan dan menunggu konfirmasi SPV.");

            return redirect()->route('deliveries.show', $delivery);
        });
    }

    public function render()
    {
        $user = Auth::user();

        // Vendor only sees their vendor's active POs; SPV sees all active POs
        $availablePos = PurchaseOrder::query()
            ->whereIn('status', [PoStatus::ACTIVE->value, PoStatus::DRAFT->value])
            ->when($user->isVendor(), fn ($q) => $q->where('vendor_id', $user->vendor_id))
            ->with(['vendor', 'poItems'])
            ->latest('po_date')
            ->get()
            ->filter(fn ($p) => $p->total_qty_remaining > 0);

        return view('livewire.deliveries.create', [
            'availablePos' => $availablePos,
        ]);
    }
}

