<?php

namespace App\Livewire\Postings;

use App\Models\Posting;
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
    public string $date_from = '';
    public string $date_to = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        Gate::authorize('viewAny', Posting::class);

        $user = Auth::user();

        $postings = Posting::query()
            ->with(['paymentRequests.invoices.vendor', 'documents'])
            ->when($user->isVendor(), function ($query) use ($user) {
                $query->whereHas('paymentRequests.invoices', fn ($q) => $q->where('vendor_id', $user->vendor_id));
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('document_number', 'like', "%{$this->search}%")
                        ->orWhere('notes', 'like', "%{$this->search}%")
                        ->orWhereHas('paymentRequests', fn ($pr) => $pr->where('number', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->date_from, fn ($q) => $q->whereDate('posting_date', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('posting_date', '<=', $this->date_to))
            ->latest('posting_date')
            ->latest('id')
            ->paginate(12);

        return view('livewire.postings.index', [
            'postings' => $postings,
            'isSpv' => $user->isSuperAdmin() || $user->isSpv(),
            'isVendor' => $user->isVendor(),
        ]);
    }
}

