<?php

namespace App\Livewire\Postings;

use App\Enums\DocumentType;
use App\Models\Posting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Show extends Component
{
    use WithFileUploads;

    public Posting $posting;

    public $document_file = null;
    public string $document_notes = '';

    public function mount(Posting $posting): void
    {
        Gate::authorize('view', $posting);

        $this->posting = $posting->load([
            'paymentRequests.invoices.vendor',
            'paymentRequests.invoices.purchaseOrder',
            'paymentRequests.currentStage',
            'documents',
        ]);
    }

    public function uploadDocument(): void
    {
        $user = Auth::user();
        if (! ($user->isSuperAdmin() || $user->isSpv())) {
            abort(403, 'Hanya SPV/SuperAdmin yang dapat mengunggah bukti bayar posting.');
        }

        $this->validate([
            'document_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'document_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'document_file.required' => 'Pilih file bukti bayar terlebih dahulu.',
            'document_file.mimes' => 'Format file harus PDF, JPG, JPEG, atau PNG.',
            'document_file.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $path = $this->document_file->store('documents/postings', 'local');

        \App\Models\Document::create([
            'documentable_type' => Posting::class,
            'documentable_id' => $this->posting->id,
            'type' => DocumentType::PAYMENT_PROOF,
            'path' => $path,
            'original_name' => $this->document_file->getClientOriginalName(),
            'mime' => $this->document_file->getMimeType(),
            'size' => $this->document_file->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        $this->document_file = null;
        $this->document_notes = '';
        $this->mount($this->posting->fresh());
        session()->flash('success', 'Bukti bayar / dokumen posting berhasil diunggah.');
    }

    public function render()
    {
        $user = Auth::user();

        // Calculate all invoices across all PRs
        $invoices = $this->posting->paymentRequests
            ->flatMap(fn ($pr) => $pr->invoices)
            ->unique('id');

        $vendors = $invoices->pluck('vendor')->unique('id')->filter();

        return view('livewire.postings.show', [
            'invoices' => $invoices,
            'vendors' => $vendors,
            'isSpv' => $user->isSuperAdmin() || $user->isSpv(),
        ]);
    }
}

