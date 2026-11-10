<?php

namespace App\Livewire\PaymentRequests;

use App\Models\PaymentRequest;
use App\Models\PaymentStage;
use App\Services\ProcurementQueueService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $stage_id = 'all';
    public string $posting_status = 'all'; // all | posted | unposted
    public string $date_from = '';
    public string $date_to = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStageId(): void
    {
        $this->resetPage();
    }

    public function updatingPostingStatus(): void
    {
        $this->resetPage();
    }

    public function render(ProcurementQueueService $queue)
    {
        Gate::authorize('viewAny', PaymentRequest::class);

        $user = Auth::user();
        $isSpv = $user->isSuperAdmin() || $user->isSpv();

        $paymentRequests = PaymentRequest::query()
            ->with(['currentStage', 'posting', 'invoices.vendor', 'invoices.purchaseOrder'])
            ->when($user->isVendor(), function ($query) use ($user) {
                $query->whereHas('invoices', fn ($q) => $q->where('vendor_id', $user->vendor_id));
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('number', 'like', "%{$this->search}%")
                        ->orWhere('notes', 'like', "%{$this->search}%")
                        ->orWhereHas('invoices', fn ($inv) => $inv->where('invoice_number', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->stage_id !== 'all', fn ($q) => $q->where('current_stage_id', $this->stage_id))
            ->when($this->posting_status === 'posted', fn ($q) => $q->whereNotNull('posting_id'))
            ->when($this->posting_status === 'unposted', fn ($q) => $q->whereNull('posting_id'))
            ->when($this->date_from, fn ($q) => $q->whereDate('submitted_at', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('submitted_at', '<=', $this->date_to))
            ->latest('submitted_at')
            ->latest('id')
            ->paginate(12);

        $stages = PaymentStage::active()->ordered()->get();

        return view('livewire.payment-requests.index', [
            'paymentRequests' => $paymentRequests,
            'stages' => $stages,
            'isSpv' => $isSpv,
            'isVendor' => $user->isVendor(),
            'readyForPrInvoices' => $user->can('create', PaymentRequest::class) ? $queue->readyForPaymentRequestInvoices($user) : collect(),
        ]);
    }
}

