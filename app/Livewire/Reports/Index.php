<?php

namespace App\Livewire\Reports;

use App\Enums\DeliveryStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PoStatus;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Posting;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Services\ReportExportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $reportType = 'po'; // po, deliveries, invoices, postings, vendor_balances

    #[Url]
    public string $date_from = '';

    #[Url]
    public string $date_to = '';

    #[Url]
    public string $vendor_id = 'all';

    #[Url]
    public string $status = 'all';

    public function updatingReportType(): void
    {
        $this->status = 'all';
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

    public function updatingVendorId(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function setDateShortcut(string $shortcut): void
    {
        $now = now();
        if ($shortcut === 'today') {
            $this->date_from = $now->format('Y-m-d');
            $this->date_to = $now->format('Y-m-d');
        } elseif ($shortcut === 'this_month') {
            $this->date_from = $now->copy()->startOfMonth()->format('Y-m-d');
            $this->date_to = $now->copy()->endOfMonth()->format('Y-m-d');
        } elseif ($shortcut === 'this_year') {
            $this->date_from = $now->copy()->startOfYear()->format('Y-m-d');
            $this->date_to = $now->copy()->endOfYear()->format('Y-m-d');
        } elseif ($shortcut === 'all') {
            $this->date_from = '';
            $this->date_to = '';
        }
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->date_from = '';
        $this->date_to = '';
        if (! Auth::user()?->isVendor()) {
            $this->vendor_id = 'all';
        }
        $this->status = 'all';
        $this->resetPage();
    }

    public function render(ReportExportService $exportService)
    {
        $user = Auth::user();
        $isVendor = $user->isVendor();

        if ($isVendor) {
            $this->vendor_id = (string) $user->vendor_id;
        }

        $filters = [
            'date_from' => $this->date_from ?: null,
            'date_to' => $this->date_to ?: null,
            'vendor_id' => $this->vendor_id,
            'status' => $this->status,
        ];

        // Overall summary statistics (Strictly scoped if vendor)
        $stats = [
            'total_po_value' => (int) PurchaseOrder::where('status', PoStatus::ACTIVE)->sum('total_amount'),
            'total_delivered_value' => (int) PurchaseOrder::where('status', PoStatus::ACTIVE)->get()->sum(fn ($p) => (int) $p->total_amount_delivered),
            'total_invoiced_value' => (int) Invoice::whereIn('status', [InvoiceStatus::SUBMITTED, InvoiceStatus::APPROVED, InvoiceStatus::IN_PAYMENT, InvoiceStatus::PAID])->sum('total_amount'),
            'total_paid_value' => $isVendor
                ? (int) Invoice::where('vendor_id', $user->vendor_id)->where('status', InvoiceStatus::PAID)->sum('total_amount')
                : (int) Posting::sum('total_paid'),
        ];

        // Query preview dataset
        $data = $exportService->queryData($this->reportType, $filters, $user);

        $vendors = $isVendor ? collect([$user->vendor]) : Vendor::active()->orderBy('name')->get();

        return view('livewire.reports.index', [
            'data' => $data,
            'stats' => $stats,
            'vendors' => $vendors,
            'isVendor' => $isVendor,
            'reportTitle' => $exportService->getReportTitle($this->reportType),
            'filterDesc' => $exportService->getFilterDescription($filters, $user),
        ]);
    }
}

