<?php

namespace App\Livewire\PaymentRequests;

use App\Enums\DocumentType;
use App\Models\AuditLog;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestStageLog;
use App\Models\PaymentStage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Show extends Component
{
    use WithFileUploads;

    public PaymentRequest $paymentRequest;

    // Stage update
    public ?int $new_stage_id = null;
    public string $stage_notes = '';

    // Document upload
    public $document_file = null;
    public string $document_notes = '';

    public function mount(PaymentRequest $paymentRequest): void
    {
        Gate::authorize('view', $paymentRequest);

        $this->paymentRequest = $paymentRequest->load([
            'currentStage',
            'posting.documents',
            'documents',
            'stageLogs.fromStage',
            'stageLogs.toStage',
            'stageLogs.changer',
            'invoices.vendor',
            'invoices.purchaseOrder',
            'invoices.lines.deliveryItem.delivery',
        ]);

        $this->new_stage_id = $this->paymentRequest->current_stage_id;
    }

    public function updateStage(): void
    {
        Gate::authorize('updateStage', $this->paymentRequest);

        $this->validate([
            'new_stage_id' => ['required', 'exists:payment_stages,id'],
            'stage_notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'new_stage_id.required' => 'Pilih tahapan baru terlebih dahulu.',
            'new_stage_id.exists' => 'Tahapan yang dipilih tidak valid.',
        ]);

        DB::transaction(function () {
            $lockedPr = PaymentRequest::where('id', $this->paymentRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPr->isPosted()) {
                throw new \RuntimeException('Payment Request sudah diposting ke SAP, tahapan tidak dapat diubah lagi.');
            }

            $fromStageId = $lockedPr->current_stage_id;
            $lockedPr->update([
                'current_stage_id' => $this->new_stage_id,
            ]);

            PaymentRequestStageLog::create([
                'payment_request_id' => $lockedPr->id,
                'from_stage_id' => $fromStageId,
                'to_stage_id' => $this->new_stage_id,
                'changed_by' => Auth::id(),
                'notes' => $this->stage_notes,
                'created_at' => now(),
            ]);

            $targetStage = PaymentStage::find($this->new_stage_id);

            AuditLog::log(
                action: 'payment_request.stage_updated',
                auditable: $lockedPr,
                newValues: ['current_stage_id' => $this->new_stage_id, 'notes' => $this->stage_notes],
                oldValues: ['current_stage_id' => $fromStageId],
            );
        });

        $this->stage_notes = '';
        $this->mount($this->paymentRequest->fresh());
        session()->flash('success', 'Tahapan Payment Request berhasil diperbarui.');
    }

    public function uploadDocument(): void
    {
        $user = Auth::user();
        if (! ($user->isSuperAdmin() || $user->isSpv())) {
            abort(403, 'Hanya SPV/SuperAdmin yang dapat mengunggah dokumen Payment Request.');
        }

        $this->validate([
            'document_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'document_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'document_file.required' => 'Pilih file dokumen terlebih dahulu.',
            'document_file.mimes' => 'Format file harus PDF, JPG, JPEG, atau PNG.',
            'document_file.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $path = $this->document_file->store('documents/payment_requests', 'local');

        \App\Models\Document::create([
            'documentable_type' => PaymentRequest::class,
            'documentable_id' => $this->paymentRequest->id,
            'type' => DocumentType::PAYMENT_REQUEST,
            'path' => $path,
            'original_name' => $this->document_file->getClientOriginalName(),
            'mime' => $this->document_file->getMimeType(),
            'size' => $this->document_file->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        $this->document_file = null;
        $this->document_notes = '';
        $this->mount($this->paymentRequest->fresh());
        session()->flash('success', 'Dokumen lampiran berhasil diunggah.');
    }

    public function render()
    {
        $stages = PaymentStage::active()->ordered()->get();
        $user = Auth::user();

        return view('livewire.payment-requests.show', [
            'stages' => $stages,
            'canManageStage' => ($user->isSuperAdmin() || $user->isSpv()) && ! $this->paymentRequest->isPosted(),
            'isSpv' => $user->isSuperAdmin() || $user->isSpv(),
        ]);
    }
}

