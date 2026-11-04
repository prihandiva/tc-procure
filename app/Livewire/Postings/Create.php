<?php

namespace App\Livewire\Postings;

use App\Enums\DocumentType;
use App\Enums\InvoiceStatus;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestStageLog;
use App\Models\PaymentStage;
use App\Models\Posting;
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

    public string $document_number = '';
    public string $posting_date = '';
    public int $total_paid = 0;
    public string $notes = '';
    public $payment_proof = null;

    /** @var array<int> */
    public array $selected_pr_ids = [];

    public function mount(): void
    {
        Gate::authorize('create', Posting::class);

        $this->posting_date = now()->format('Y-m-d');
    }

    public function updatedSelectedPrIds(): void
    {
        $this->recalculateTotal();
    }

    public function recalculateTotal(): void
    {
        if (empty($this->selected_pr_ids)) {
            $this->total_paid = 0;
            return;
        }

        $prs = PaymentRequest::with('invoices')
            ->whereIn('id', $this->selected_pr_ids)
            ->get();

        $this->total_paid = (int) $prs->sum(fn ($pr) => (int) $pr->total_amount);
    }

    public function save()
    {
        Gate::authorize('create', Posting::class);

        $this->validate([
            'document_number' => ['required', 'string', 'max:100', 'unique:postings,document_number'],
            'posting_date' => ['required', 'date'],
            'total_paid' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'selected_pr_ids' => ['required', 'array', 'min:1'],
            'selected_pr_ids.*' => ['exists:payment_requests,id'],
            'payment_proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'document_number.required' => 'Nomor dokumen posting SAP wajib diisi.',
            'document_number.unique' => 'Nomor dokumen posting SAP sudah terdaftar.',
            'posting_date.required' => 'Tanggal posting SAP wajib diisi.',
            'total_paid.required' => 'Total nominal pembayaran wajib diisi.',
            'total_paid.min' => 'Total nominal pembayaran minimal Rp 1.',
            'selected_pr_ids.required' => 'Pilih minimal satu Payment Request untuk diposting.',
            'selected_pr_ids.min' => 'Pilih minimal satu Payment Request untuk diposting.',
            'payment_proof.mimes' => 'Format bukti bayar harus PDF, JPG, JPEG, atau PNG.',
            'payment_proof.max' => 'Ukuran file bukti bayar maksimal 5MB.',
        ]);

        $posting = DB::transaction(function () {
            // Lock selected PRs
            $lockedPrs = PaymentRequest::with('invoices')
                ->whereIn('id', $this->selected_pr_ids)
                ->lockForUpdate()
                ->get();

            foreach ($lockedPrs as $pr) {
                if ($pr->isPosted()) {
                    throw new \RuntimeException("Payment Request {$pr->number} sudah pernah diposting.");
                }
            }

            // Create Posting
            $posting = Posting::create([
                'document_number' => $this->document_number,
                'posting_date' => $this->posting_date,
                'total_paid' => $this->total_paid,
                'notes' => $this->notes ?: null,
            ]);

            // Save file if uploaded
            if ($this->payment_proof) {
                $path = $this->payment_proof->store('documents/postings', 'local');
                \App\Models\Document::create([
                    'documentable_type' => Posting::class,
                    'documentable_id' => $posting->id,
                    'type' => DocumentType::PAYMENT_PROOF,
                    'path' => $path,
                    'original_name' => $this->payment_proof->getClientOriginalName(),
                    'mime' => $this->payment_proof->getMimeType(),
                    'size' => $this->payment_proof->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);
            }

            // Find final stage
            $finalStage = PaymentStage::where('is_final', true)->first()
                ?? PaymentStage::orderBy('sort_order', 'desc')->first();

            $allInvoiceIds = [];

            // Update each Payment Request
            foreach ($lockedPrs as $pr) {
                $oldStageId = $pr->current_stage_id;
                $targetStageId = $finalStage?->id ?? $oldStageId;

                $pr->update([
                    'posting_id' => $posting->id,
                    'current_stage_id' => $targetStageId,
                ]);

                PaymentRequestStageLog::create([
                    'payment_request_id' => $pr->id,
                    'from_stage_id' => $oldStageId,
                    'to_stage_id' => $targetStageId,
                    'changed_by' => Auth::id(),
                    'notes' => "Diposting ke SAP dengan No. Dokumen: {$posting->document_number}",
                    'created_at' => now(),
                ]);

                foreach ($pr->invoices as $inv) {
                    $allInvoiceIds[] = $inv->id;
                }
            }

            // Lock and update all Invoices to PAID
            $allInvoiceIds = array_unique($allInvoiceIds);
            if (! empty($allInvoiceIds)) {
                $invoices = Invoice::whereIn('id', $allInvoiceIds)
                    ->lockForUpdate()
                    ->get();

                foreach ($invoices as $inv) {
                    $oldStatus = $inv->status;
                    $inv->update(['status' => InvoiceStatus::PAID]);

                    AuditLog::log(
                        action: 'invoice.paid',
                        auditable: $inv,
                        newValues: ['status' => InvoiceStatus::PAID->value, 'posting_id' => $posting->id],
                        oldValues: ['status' => $oldStatus->value],
                    );
                }
            }

            AuditLog::log(
                action: 'posting.created',
                auditable: $posting,
                newValues: [
                    'document_number' => $posting->document_number,
                    'posting_date' => $posting->posting_date,
                    'total_paid' => $posting->total_paid,
                    'pr_count' => count($this->selected_pr_ids),
                    'invoice_count' => count($allInvoiceIds),
                ],
            );

            return $posting;
        });

        session()->flash('success', "Posting SAP #{$posting->document_number} berhasil dicatat dan status invoice telah diperbarui ke LUNAS.");

        return redirect()->route('postings.show', $posting);
    }

    public function render()
    {
        // Unposted Payment Requests
        $unpostedPrs = PaymentRequest::query()
            ->unposted()
            ->with(['currentStage', 'invoices.vendor'])
            ->latest('submitted_at')
            ->get();

        return view('livewire.postings.create', [
            'unpostedPrs' => $unpostedPrs,
        ]);
    }
}

