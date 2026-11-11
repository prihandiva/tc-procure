<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ReportExportController;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Deliveries\Create as DeliveryCreate;
use App\Livewire\Deliveries\Index as DeliveriesIndex;
use App\Livewire\Deliveries\Show as DeliveryShow;
use App\Livewire\Help\Index as HelpIndex;
use App\Livewire\Invoices\Create as InvoiceCreate;
use App\Livewire\Invoices\Index as InvoicesIndex;
use App\Livewire\Invoices\Show as InvoiceShow;
use App\Livewire\Items\Index as ItemsIndex;
use App\Livewire\PaymentRequests\Create as PaymentRequestCreate;
use App\Livewire\PaymentRequests\Index as PaymentRequestsIndex;
use App\Livewire\PaymentRequests\Show as PaymentRequestShow;
use App\Livewire\PaymentStages\Index as PaymentStagesIndex;
use App\Livewire\Postings\Create as PostingCreate;
use App\Livewire\Postings\Index as PostingsIndex;
use App\Livewire\Postings\Show as PostingShow;
use App\Livewire\PurchaseOrders\Create as PurchaseOrderCreate;
use App\Livewire\PurchaseOrders\Edit as PurchaseOrderEdit;
use App\Livewire\PurchaseOrders\Index as PurchaseOrdersIndex;
use App\Livewire\PurchaseOrders\Show as PurchaseOrderShow;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Vendors\Index as VendorsIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('pages::auth.login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    // Purchase Orders
    Route::get('purchase-orders', PurchaseOrdersIndex::class)->name('purchase-orders.index');
    Route::get('purchase-orders/create', PurchaseOrderCreate::class)->name('purchase-orders.create');
    Route::get('purchase-orders/{purchaseOrder}', PurchaseOrderShow::class)->name('purchase-orders.show');
    Route::get('purchase-orders/{purchaseOrder}/edit', PurchaseOrderEdit::class)->name('purchase-orders.edit');

    // Deliveries (DO)
    Route::get('deliveries', DeliveriesIndex::class)->name('deliveries.index');
    Route::get('deliveries/create', DeliveryCreate::class)->name('deliveries.create');
    Route::get('deliveries/{delivery}', DeliveryShow::class)->name('deliveries.show');

    // Invoices (Tagihan)
    Route::get('invoices', InvoicesIndex::class)->name('invoices.index');
    Route::get('invoices/create', InvoiceCreate::class)->name('invoices.create');
    Route::get('invoices/{invoice}', InvoiceShow::class)->name('invoices.show');

    // Payment Requests (Pengajuan Berkas Pembayaran)
    Route::get('payment-requests', PaymentRequestsIndex::class)->name('payment-requests.index');
    Route::get('payment-requests/create', PaymentRequestCreate::class)->name('payment-requests.create');
    Route::get('payment-requests/{paymentRequest}', PaymentRequestShow::class)->name('payment-requests.show');

    // Postings (Dokumen Posting SAP)
    Route::get('postings', PostingsIndex::class)->name('postings.index');
    Route::get('postings/create', PostingCreate::class)->name('postings.create');
    Route::get('postings/{posting}', PostingShow::class)->name('postings.show');

    // Master Data
    Route::get('vendors', VendorsIndex::class)->name('vendors.index');
    Route::get('items', ItemsIndex::class)->name('items.index');
    Route::get('payment-stages', PaymentStagesIndex::class)->name('payment-stages.index');

    // Bantuan & Panduan
    Route::get('help', HelpIndex::class)->name('help');

    // Laporan & Ekspor (Excel & PDF)
    Route::get('reports', ReportsIndex::class)->name('reports.index');
    Route::match(['get', 'post'], 'reports/export', [ReportExportController::class, 'export'])->name('reports.export');

    // Private Documents
    Route::get('documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
});

require __DIR__.'/settings.php';
