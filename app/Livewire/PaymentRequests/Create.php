<?php

namespace App\Livewire\PaymentRequests;

use App\Enums\InvoiceStatus;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestStageLog;
use App\Models\PaymentStage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class Create extends Component
{
    public string $number = '';
    #[Locked]
    public string $auto_filled_number = '';
    public ?int $current_stage_id = null;
    public string $notes = '';

    /**
     * @var array<int> IDs of approved invoices selected for this PR
     */
    public array $selected_invoice_ids = [];

    public function mount(?int $invoice_id = null): void
    {
        Gate::authorize('create', PaymentRequest::class);

        // Default to first active stage (order 1)
        $firstStage = PaymentStage::active()->ordered()->first();
        if ($firstStage) {
            $this->current_stage_id = $firstStage->id;
        }

        $targetInvoiceId = $invoice_id ?? (request()->filled('invoice_id') ? request()->integer('invoice_id') : null);
        if ($targetInvoiceId) {
            $eligible = Invoice::where('id', $targetInvoiceId)
                ->where('status', InvoiceStatus::APPROVED->value)
                ->whereDoesntHave('paymentRequests')
                ->exists();

            if ($eligible) {
                $this->selected_invoice_ids = [$targetInvoiceId];
                $this->updatedSelectedInvoiceIds();
            }
        }
    }

    public function updatedSelectedInvoiceIds(): void
    {
        if (empty($this->selected_invoice_ids)) {
            if (trim($this->number) === $this->auto_filled_number) {
                $this->number = '';
            }
            $this->auto_filled_number = '';

            return;
        }

        $firstId = reset($this->selected_invoice_ids);
        $firstInvoice = Invoice::with('purchaseOrder')->find($firstId)
            ?? Invoice::with('purchaseOrder')->whereIn('id', $this->selected_invoice_ids)->first();

        if (! $firstInvoice?->purchaseOrder) {
            return;
        }

        $suggested = PaymentRequest::suggestNumberForPo($firstInvoice->purchaseOrder);
        if (trim($this->number) === '' || trim($this->number) === $this->auto_filled_number) {
            $this->number = $suggested;
        }
        $this->auto_filled_number = $suggested;
    }

    public function getTotalAmountProperty(): int
    {
        if (empty($this->selected_invoice_ids)) {
            return 0;
        }

        return (int) Invoice::whereIn('id', $this->selected_invoice_ids)->sum('total_amount');
    }

    protected function rules(): array
    {
        return [
            'number' => 'required|string|max:100|unique:payment_requests,number',
            'current_stage_id' => 'required|exists:payment_stages,id',
            'notes' => 'nullable|string|max:1000',
            'selected_invoice_ids' => 'required|array|min:1',
            'selected_invoice_ids.*' => 'exists:invoices,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'number.required' => 'Nomor Payment Request wajib diisi.',
            'number.unique' => 'Nomor Payment Request ini sudah pernah digunakan.',
            'current_stage_id.required' => 'Pilih tahap status awal pengajuan.',
            'selected_invoice_ids.min' => 'Pilih minimal satu invoice yang sudah disetujui (approved).',
        ];
    }

    public function save()
    {
        Gate::authorize('create', PaymentRequest::class);
        $this->number = trim($this->number);
        $this->validate();

        return DB::transaction(function () {
            // Lock selected invoices to prevent race conditions
            $invoices = Invoice::whereIn('id', $this->selected_invoice_ids)
                ->lockForUpdate()
                ->get();

            foreach ($invoices as $inv) {
                if (! $inv->isApproved()) {
                    throw ValidationException::withMessages([
                        'selected_invoice_ids' => "Invoice {$inv->invoice_number} berstatus {$inv->status->label()}, hanya invoice berstatus Disetujui (Approved) yang dapat diajukan ke Payment Request.",
                    ]);
                }
            }

            $pr = PaymentRequest::create([
                'number' => trim($this->number),
                'current_stage_id' => $this->current_stage_id,
                'submitted_at' => now(),
                'notes' => $this->notes ?: null,
            ]);

            // Attach invoices to PR
            $pr->invoices()->attach($this->selected_invoice_ids);

            // Transition invoice status to in_payment
            Invoice::whereIn('id', $this->selected_invoice_ids)
                ->update(['status' => InvoiceStatus::IN_PAYMENT]);

            // Create initial stage log entry
            PaymentRequestStageLog::create([
                'payment_request_id' => $pr->id,
                'from_stage_id' => null,
                'to_stage_id' => $this->current_stage_id,
                'changed_by' => Auth::id(),
                'notes' => 'Inisiasi pembuatan Payment Request',
                'created_at' => now(),
            ]);

            AuditLog::log('created', $pr, [
                'number' => $pr->number,
                'invoices_count' => $invoices->count(),
                'total_amount' => $pr->total_amount,
            ]);

            session()->flash('success', "Payment Request {$pr->number} berhasil dibuat. Seluruh invoice terkait masuk ke tahap Dalam Proses Pembayaran.");

            return redirect()->route('payment-requests.show', $pr);
        });
    }

    public function render()
    {
        $stages = PaymentStage::active()->ordered()->get();

        // Invoices approved and not yet linked to any payment request
        $approvedInvoices = Invoice::query()
            ->where('status', InvoiceStatus::APPROVED->value)
            ->whereDoesntHave('paymentRequests')
            ->with(['vendor', 'purchaseOrder'])
            ->latest('invoice_date')
            ->get();

        return view('livewire.payment-requests.create', [
            'stages' => $stages,
            'approvedInvoices' => $approvedInvoices,
        ]);
    }
}

