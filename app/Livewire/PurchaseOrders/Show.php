<?php

namespace App\Livewire\PurchaseOrders;

use App\Enums\DocumentType;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Show extends Component
{
    use WithFileUploads;

    public PurchaseOrder $purchaseOrder;

    public string $activeTab = 'items'; // items | deliveries | invoices | documents
    public $new_document = null;
    public string $document_type = 'po';

    public function mount(PurchaseOrder $purchaseOrder): void
    {
        Gate::authorize('view', $purchaseOrder);
        $this->purchaseOrder = $purchaseOrder->load([
            'vendor',
            'poItems.deliveryItems.delivery',
            'deliveries.deliveryItems',
            'invoices.invoiceLines',
            'documents.uploader',
        ]);
    }

    public function uploadDocument(): void
    {
        Gate::authorize('update', $this->purchaseOrder);

        $this->validate([
            'new_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'document_type' => 'required|in:po,other',
        ]);

        $path = $this->new_document->store('documents/po', 'local');

        Document::create([
            'documentable_type' => PurchaseOrder::class,
            'documentable_id' => $this->purchaseOrder->id,
            'type' => DocumentType::from($this->document_type),
            'path' => $path,
            'original_name' => $this->new_document->getClientOriginalName(),
            'mime' => $this->new_document->getMimeType(),
            'size' => $this->new_document->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        AuditLog::log('document_uploaded', $this->purchaseOrder, [
            'file_name' => $this->new_document->getClientOriginalName(),
        ]);

        $this->reset(['new_document']);
        $this->purchaseOrder->load('documents.uploader');
        session()->flash('success', 'Dokumen berhasil diunggah dan disimpan di private storage.');
    }

    public function render()
    {
        Gate::authorize('view', $this->purchaseOrder);

        return view('livewire.purchase-orders.show', [
            'isVendor' => Auth::user()->isVendor(),
            'canEdit' => $this->purchaseOrder->canBeEdited() && (Auth::user()->isSuperAdmin() || Auth::user()->isSpv()),
        ]);
    }
}

