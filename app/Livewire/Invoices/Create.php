<?php

namespace App\Livewire\Invoices;

use App\Enums\DeliveryStatus;
use App\Enums\DocumentType;
use App\Enums\InvoiceStatus;
use App\Models\AuditLog;
use App\Models\DeliveryItem;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Create extends Component
{
    use WithFileUploads;

    public ?int $purchase_order_id = null;
    public string $invoice_number = '';
    public string $invoice_date = '';
    public $document_file = null;

    /**
     * @var array<int> IDs of delivery_items selected for this invoice
     */
    public array $selected_item_ids = [];

    public function mount(?int $po_id = null): void
    {
        Gate::authorize('create', Invoice::class);

        $this->invoice_date = now()->format('Y-m-d');

        if ($po_id) {
            $this->purchase_order_id = $po_id;
            $this->loadEligibleDeliveryItems();
        }
    }

    public function updatedPurchaseOrderId(): void
    {
        $this->loadEligibleDeliveryItems();
    }

    public function loadEligibleDeliveryItems(): void
    {
        $this->selected_item_ids = [];

        if (! $this->purchase_order_id) {
            return;
        }

        $po = PurchaseOrder::find($this->purchase_order_id);
        if (! $po) {
            return;
        }

        Gate::authorize('view', $po);

        $items = DeliveryItem::query()
            ->where('is_invoiced', false)
            ->whereHas('delivery', function ($q) {
                $q->where('purchase_order_id', $this->purchase_order_id)
                    ->where('status', DeliveryStatus::CONFIRMED->value);
            })
            ->pluck('id')
            ->toArray();

        $this->selected_item_ids = $items;
    }

    public function getTotalAmountProperty(): int
    {
        if (empty($this->selected_item_ids)) {
            return 0;
        }

        $items = DeliveryItem::with('poItem')
            ->whereIn('id', $this->selected_item_ids)
            ->get();

        return (int) $items->sum(function ($item) {
            $qty = $item->qty_received ?? $item->qty_sent;
            $price = $item->poItem->unit_price ?? 0;

            return (int) round($qty * $price);
        });
    }

    protected function rules(): array
    {
        $user = Auth::user();
        $vendorId = $user->isVendor() ? $user->vendor_id : PurchaseOrder::find($this->purchase_order_id)?->vendor_id;

        return [
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'invoice_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('invoices', 'invoice_number')
                    ->where('vendor_id', $vendorId)
                    ->whereNull('deleted_at'),
            ],
            'invoice_date' => 'required|date',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'selected_item_ids' => 'required|array|min:1',
            'selected_item_ids.*' => 'exists:delivery_items,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'purchase_order_id.required' => 'Silakan pilih Purchase Order acuan tagihan.',
            'invoice_number.required' => 'Nomor invoice/faktur wajib diisi.',
            'invoice_number.unique' => 'Nomor invoice ini sudah pernah digunakan oleh vendor Anda.',
            'invoice_date.required' => 'Tanggal invoice wajib diisi.',
            'document_file.mimes' => 'Format berkas invoice harus PDF, JPG, JPEG, atau PNG.',
            'document_file.max' => 'Ukuran berkas invoice maksimal 5 MB.',
            'selected_item_ids.min' => 'Pilih minimal satu pengiriman terkonfirmasi untuk ditagihkan.',
        ];
    }

    public function save()
    {
        Gate::authorize('create', Invoice::class);
        $this->validate();

        return DB::transaction(function () {
            $po = PurchaseOrder::where('id', $this->purchase_order_id)->lockForUpdate()->firstOrFail();
            Gate::authorize('view', $po);

            // Lock selected delivery items and verify none is already invoiced (Anti Double-Billing)
            $deliveryItems = DeliveryItem::with(['delivery', 'poItem'])
                ->whereIn('id', $this->selected_item_ids)
                ->lockForUpdate()
                ->get();

            foreach ($deliveryItems as $dItem) {
                if ($dItem->is_invoiced) {
                    throw ValidationException::withMessages([
                        'selected_item_ids' => "Baris pengiriman {$dItem->poItem->item_name} pada DO {$dItem->delivery->code} sudah masuk ke dalam invoice aktif lain.",
                    ]);
                }
                if ($dItem->delivery->status !== DeliveryStatus::CONFIRMED) {
                    throw ValidationException::withMessages([
                        'selected_item_ids' => "Pengiriman {$dItem->delivery->code} belum dikonfirmasi oleh SPV sehingga belum dapat ditagihkan.",
                    ]);
                }
            }

            // Create Invoice
            $invoice = Invoice::create([
                'invoice_number' => trim($this->invoice_number),
                'vendor_id' => $po->vendor_id,
                'purchase_order_id' => $po->id,
                'invoice_date' => $this->invoice_date,
                'total_amount' => 0, // updated after lines created
                'status' => InvoiceStatus::SUBMITTED,
            ]);

            $totalAmount = 0;

            foreach ($deliveryItems as $dItem) {
                $qty = $dItem->qty_received ?? $dItem->qty_sent;
                $price = $dItem->poItem->unit_price;
                $lineAmount = (int) round($qty * $price);
                $totalAmount += $lineAmount;

                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'delivery_item_id' => $dItem->id,
                    'qty' => $qty,
                    'unit_price' => $price,
                    'amount' => $lineAmount,
                ]);

                // Mark delivery item as invoiced
                $dItem->update(['is_invoiced' => true]);
            }

            $invoice->update(['total_amount' => $totalAmount]);

            if ($this->document_file) {
                $path = $this->document_file->store('documents/invoices', 'local');

                Document::create([
                    'documentable_type' => Invoice::class,
                    'documentable_id' => $invoice->id,
                    'type' => DocumentType::INVOICE,
                    'path' => $path,
                    'original_name' => $this->document_file->getClientOriginalName(),
                    'mime' => $this->document_file->getMimeType(),
                    'size' => $this->document_file->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);
            }

            AuditLog::log('created', $invoice, [
                'invoice_number' => $invoice->invoice_number,
                'total_amount' => $invoice->total_amount,
                'lines_count' => $deliveryItems->count(),
            ]);

            session()->flash('success', "Invoice {$invoice->invoice_number} berhasil dibuat dan dikirimkan ke SPV untuk verifikasi.");

            return redirect()->route('invoices.show', $invoice);
        });
    }

    public function render()
    {
        $user = Auth::user();

        // Active POs with confirmed uninvoiced delivery items
        $availablePos = PurchaseOrder::query()
            ->when($user->isVendor(), fn ($q) => $q->where('vendor_id', $user->vendor_id))
            ->whereHas('deliveries', function ($d) {
                $d->where('status', DeliveryStatus::CONFIRMED->value)
                    ->whereHas('deliveryItems', fn ($di) => $di->where('is_invoiced', false));
            })
            ->with('vendor')
            ->latest('po_date')
            ->get();

        $eligibleDeliveryItems = collect();
        if ($this->purchase_order_id) {
            $eligibleDeliveryItems = DeliveryItem::with(['delivery', 'poItem'])
                ->where('is_invoiced', false)
                ->whereHas('delivery', function ($q) {
                    $q->where('purchase_order_id', $this->purchase_order_id)
                        ->where('status', DeliveryStatus::CONFIRMED->value);
                })
                ->get()
                ->groupBy('delivery_id');
        }

        return view('livewire.invoices.create', [
            'availablePos' => $availablePos,
            'eligibleDeliveryItems' => $eligibleDeliveryItems,
        ]);
    }
}

