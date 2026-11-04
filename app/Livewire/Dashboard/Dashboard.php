<?php

namespace App\Livewire\Dashboard;

use App\Enums\DeliveryStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PoStatus;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Invoice;
use App\Models\PaymentRequest;
use App\Models\Posting;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        if ($user->isVendor()) {
            return $this->renderVendorDashboard($user);
        }

        return $this->renderSpvDashboard();
    }

    private function renderSpvDashboard()
    {
        // 1. Alert Metrics
        $pendingDeliveriesCount = Delivery::where('status', DeliveryStatus::SUBMITTED)->count();
        $pendingInvoicesCount = Invoice::where('status', InvoiceStatus::SUBMITTED)->count();
        $unpostedPrsCount = PaymentRequest::unposted()->count();

        // 2. PO Metrics
        $activePos = PurchaseOrder::where('status', PoStatus::ACTIVE)->with(['poItems', 'vendor'])->get();
        $activePoCount = $activePos->count();
        $totalPoValue = (int) $activePos->sum(fn ($po) => (int) $po->total_amount);

        // Delivered and Remaining Calculations
        $totalDeliveredValue = (int) $activePos->sum(fn ($po) => (int) $po->total_amount_delivered);
        $totalRemainingValue = max(0, $totalPoValue - $totalDeliveredValue);

        // 3. Financial Metrics
        $totalPaidValue = (int) Posting::sum('total_paid');
        $totalInvoicedValue = (int) Invoice::whereIn('status', [
            InvoiceStatus::SUBMITTED,
            InvoiceStatus::APPROVED,
            InvoiceStatus::IN_PAYMENT,
            InvoiceStatus::PAID,
        ])->sum('total_amount');

        // 4. Vendors Breakdown
        $vendors = Vendor::active()
            ->with(['purchaseOrders' => function ($query) {
                $query->where('status', PoStatus::ACTIVE)->with('poItems');
            }])
            ->get()
            ->map(function ($vendor) {
                $pos = $vendor->purchaseOrders;
                $poValue = (int) $pos->sum(fn ($p) => (int) $p->total_amount);
                $deliveredValue = (int) $pos->sum(fn ($p) => (int) $p->total_amount_delivered);
                $remainingValue = max(0, $poValue - $deliveredValue);

                $invoices = Invoice::where('vendor_id', $vendor->id)->get();
                $invoicedValue = (int) $invoices->whereIn('status', [
                    InvoiceStatus::SUBMITTED,
                    InvoiceStatus::APPROVED,
                    InvoiceStatus::IN_PAYMENT,
                    InvoiceStatus::PAID,
                ])->sum(fn ($i) => (int) $i->total_amount);

                $paidValue = (int) $invoices->where('status', InvoiceStatus::PAID)->sum(fn ($i) => (int) $i->total_amount);

                return [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'active_po_count' => $pos->count(),
                    'po_value' => $poValue,
                    'delivered_value' => $deliveredValue,
                    'remaining_value' => $remainingValue,
                    'invoiced_value' => $invoicedValue,
                    'paid_value' => $paidValue,
                    'unpaid_value' => max(0, $invoicedValue - $paidValue),
                ];
            });

        // 5. Recent Feeds
        $recentDeliveries = Delivery::with(['vendor', 'purchaseOrder'])
            ->latest('delivery_date')
            ->latest('id')
            ->take(5)
            ->get();

        $recentInvoices = Invoice::with(['vendor', 'purchaseOrder'])
            ->latest('invoice_date')
            ->latest('id')
            ->take(5)
            ->get();

        $recentPostings = Posting::with('paymentRequests')
            ->latest('posting_date')
            ->latest('id')
            ->take(5)
            ->get();

        return view('livewire.dashboard.spv', [
            'pendingDeliveriesCount' => $pendingDeliveriesCount,
            'pendingInvoicesCount' => $pendingInvoicesCount,
            'unpostedPrsCount' => $unpostedPrsCount,
            'activePoCount' => $activePoCount,
            'totalPoValue' => $totalPoValue,
            'totalDeliveredValue' => $totalDeliveredValue,
            'totalRemainingValue' => $totalRemainingValue,
            'totalInvoicedValue' => $totalInvoicedValue,
            'totalPaidValue' => $totalPaidValue,
            'vendorSummaries' => $vendors,
            'recentDeliveries' => $recentDeliveries,
            'recentInvoices' => $recentInvoices,
            'recentPostings' => $recentPostings,
        ]);
    }

    private function renderVendorDashboard($user)
    {
        $vendorId = $user->vendor_id;
        $vendor = Vendor::find($vendorId);

        // 1. Active POs
        $activePos = PurchaseOrder::where('vendor_id', $vendorId)
            ->where('status', PoStatus::ACTIVE)
            ->with('poItems')
            ->get();

        $totalPoValue = (int) $activePos->sum(fn ($po) => (int) $po->total_amount);
        $totalDeliveredValue = (int) $activePos->sum(fn ($po) => (int) $po->total_amount_delivered);
        $totalRemainingValue = max(0, $totalPoValue - $totalDeliveredValue);

        // 2. Uninvoiced Delivery Items (Ready to bill)
        $uninvoicedDeliveryItems = DeliveryItem::query()
            ->whereHas('delivery', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId)
                    ->where('status', DeliveryStatus::CONFIRMED);
            })
            ->where('is_invoiced', false)
            ->with(['delivery', 'poItem'])
            ->get();

        $uninvoicedAmount = (int) $uninvoicedDeliveryItems->sum(function ($item) {
            return (float) ($item->qty_received ?? 0) * (int) ($item->poItem->unit_price ?? 0);
        });

        // 3. Invoice Metrics
        $invoices = Invoice::where('vendor_id', $vendorId)->get();

        $pendingApprovalAmount = (int) $invoices->where('status', InvoiceStatus::SUBMITTED)->sum(fn ($i) => (int) $i->total_amount);
        $inProcessAmount = (int) $invoices->whereIn('status', [
            InvoiceStatus::APPROVED,
            InvoiceStatus::IN_PAYMENT,
        ])->sum(fn ($i) => (int) $i->total_amount);
        $paidAmount = (int) $invoices->where('status', InvoiceStatus::PAID)->sum(fn ($i) => (int) $i->total_amount);

        // 4. Recent Deliveries and Invoices
        $recentDeliveries = Delivery::where('vendor_id', $vendorId)
            ->with('purchaseOrder')
            ->latest('delivery_date')
            ->latest('id')
            ->take(5)
            ->get();

        $recentInvoices = Invoice::where('vendor_id', $vendorId)
            ->with('purchaseOrder')
            ->latest('invoice_date')
            ->latest('id')
            ->take(5)
            ->get();

        return view('livewire.dashboard.vendor', [
            'vendor' => $vendor,
            'activePos' => $activePos,
            'totalPoValue' => $totalPoValue,
            'totalDeliveredValue' => $totalDeliveredValue,
            'totalRemainingValue' => $totalRemainingValue,
            'uninvoicedCount' => $uninvoicedDeliveryItems->count(),
            'uninvoicedAmount' => $uninvoicedAmount,
            'pendingApprovalAmount' => $pendingApprovalAmount,
            'inProcessAmount' => $inProcessAmount,
            'paidAmount' => $paidAmount,
            'recentDeliveries' => $recentDeliveries,
            'recentInvoices' => $recentInvoices,
        ]);
    }
}
