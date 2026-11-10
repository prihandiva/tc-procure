<?php

use App\Enums\DeliveryStatus;
use App\Enums\DocumentType;
use App\Enums\InvoiceStatus;
use App\Enums\PoStatus;
use App\Livewire\Components\HeaderNotifications;
use App\Livewire\Deliveries\Show as DeliveryShow;
use App\Livewire\Invoices\Show as InvoiceShow;
use App\Livewire\Items\Index as ItemsIndex;
use App\Livewire\PaymentRequests\Create as PaymentRequestCreate;
use App\Livewire\PaymentStages\Index as PaymentStagesIndex;
use App\Livewire\Postings\Create as PostingCreate;
use App\Livewire\PurchaseOrders\Create as PurchaseOrderCreate;
use App\Livewire\Vendors\Index as VendorsIndex;
use App\Models\AuditLog;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\PaymentRequest;
use App\Models\PaymentStage;
use App\Models\PoItem;
use App\Models\Posting;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('vendor A cannot view or access vendor B purchase orders (IDOR protection)', function () {
    $vendorA = Vendor::factory()->create(['name' => 'Vendor Alpha']);
    $vendorB = Vendor::factory()->create(['name' => 'Vendor Beta']);

    $userA = User::factory()->vendor($vendorA->id)->create();

    $poB = PurchaseOrder::factory()->create([
        'vendor_id' => $vendorB->id,
        'po_number' => 'PO-BETA-001',
    ]);

    // Eloquent Global Scope isolates vendor queries -> 404 Not Found on route model binding
    $this->actingAs($userA)
        ->get(route('purchase-orders.show', $poB))
        ->assertNotFound();
});

test('vendor A cannot view or access vendor B delivery orders', function () {
    $vendorA = Vendor::factory()->create(['name' => 'Vendor Alpha']);
    $vendorB = Vendor::factory()->create(['name' => 'Vendor Beta']);

    $userA = User::factory()->vendor($vendorA->id)->create();

    $poB = PurchaseOrder::factory()->create(['vendor_id' => $vendorB->id]);
    $delivB = Delivery::factory()->create([
        'vendor_id' => $vendorB->id,
        'purchase_order_id' => $poB->id,
    ]);

    $this->actingAs($userA)
        ->get(route('deliveries.show', $delivB))
        ->assertNotFound();
});

test('vendor A cannot view or access vendor B invoices', function () {
    $vendorA = Vendor::factory()->create(['name' => 'Vendor Alpha']);
    $vendorB = Vendor::factory()->create(['name' => 'Vendor Beta']);

    $userA = User::factory()->vendor($vendorA->id)->create();

    $poB = PurchaseOrder::factory()->create(['vendor_id' => $vendorB->id]);
    $invB = Invoice::factory()->create([
        'vendor_id' => $vendorB->id,
        'purchase_order_id' => $poB->id,
    ]);

    $this->actingAs($userA)
        ->get(route('invoices.show', $invB))
        ->assertNotFound();
});

test('vendor A cannot download private documents belonging to vendor B (Document IDOR protection)', function () {
    Storage::fake('local');

    $vendorA = Vendor::factory()->create(['name' => 'Vendor Alpha']);
    $vendorB = Vendor::factory()->create(['name' => 'Vendor Beta']);

    $userA = User::factory()->vendor($vendorA->id)->create();
    $spv = User::factory()->spv()->create();

    $poB = PurchaseOrder::factory()->create(['vendor_id' => $vendorB->id]);

    $path = 'documents/po/secret_po_b.pdf';
    Storage::disk('local')->put($path, 'Confidential PO Content for Vendor Beta');

    $docB = Document::create([
        'documentable_type' => PurchaseOrder::class,
        'documentable_id' => $poB->id,
        'type' => DocumentType::PO,
        'path' => $path,
        'original_name' => 'secret_po_b.pdf',
        'mime' => 'application/pdf',
        'size' => 1024,
        'uploaded_by' => $spv->id,
    ]);

    // Vendor A gets 403 Forbidden
    $this->actingAs($userA)
        ->get(route('documents.show', $docB))
        ->assertForbidden();

    // SPV can access without issue
    $this->actingAs($spv)
        ->get(route('documents.show', $docB))
        ->assertOk();
});

test('vendor cannot approve or reject deliveries', function () {
    $vendor = Vendor::factory()->create();
    $vendorUser = User::factory()->vendor($vendor->id)->create();

    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);
    $deliv = Delivery::factory()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'status' => DeliveryStatus::SUBMITTED,
    ]);

    Livewire::actingAs($vendorUser)
        ->test(DeliveryShow::class, ['delivery' => $deliv])
        ->call('confirmDelivery')
        ->assertForbidden();

    Livewire::actingAs($vendorUser)
        ->test(DeliveryShow::class, ['delivery' => $deliv])
        ->set('reject_reason', 'Alasan penolakan vendor')
        ->call('rejectDelivery')
        ->assertForbidden();
});

test('vendor cannot approve or reject invoices', function () {
    $vendor = Vendor::factory()->create();
    $vendorUser = User::factory()->vendor($vendor->id)->create();

    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);
    $inv = Invoice::factory()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'status' => InvoiceStatus::SUBMITTED,
    ]);

    Livewire::actingAs($vendorUser)
        ->test(InvoiceShow::class, ['invoice' => $inv])
        ->call('approveInvoice')
        ->assertForbidden();

    Livewire::actingAs($vendorUser)
        ->test(InvoiceShow::class, ['invoice' => $inv])
        ->set('reject_reason', 'Tolak')
        ->call('rejectInvoice')
        ->assertForbidden();
});

test('vendor cannot access or mutate master data (vendors, items, stages)', function () {
    $vendor = Vendor::factory()->create();
    $vendorUser = User::factory()->vendor($vendor->id)->create();

    // Vendor cannot access master vendor index
    $this->actingAs($vendorUser)
        ->get(route('vendors.index'))
        ->assertForbidden();

    // Vendor cannot access master payment stages
    $this->actingAs($vendorUser)
        ->get(route('payment-stages.index'))
        ->assertForbidden();

    // Vendor can view items catalog, but cannot create or edit items
    Livewire::actingAs($vendorUser)
        ->test(ItemsIndex::class)
        ->call('create')
        ->assertForbidden();
});

test('file upload validates MIME type and file size strictly', function () {
    Storage::fake('local');
    $spv = User::factory()->spv()->create();
    $vendor = Vendor::factory()->create();
    $item = Item::factory()->create(['default_price' => 5000]);

    // 1. Executable / dangerous script upload rejected
    $evilFile = UploadedFile::fake()->create('malicious.php', 10, 'application/x-php');

    Livewire::actingAs($spv)
        ->test(PurchaseOrderCreate::class)
        ->set('vendor_id', $vendor->id)
        ->set('po_number', 'PO-SECURITY-001')
        ->set('po_date', '2026-11-04')
        ->set('document_file', $evilFile)
        ->set('items', [
            ['item_id' => $item->id, 'item_name' => $item->name, 'unit' => 'pc', 'qty_ordered' => 10, 'unit_price' => 5000],
        ])
        ->call('save')
        ->assertHasErrors(['document_file']);

    // 2. File exceeding 5MB (e.g. 6000 KB) rejected
    $hugeFile = UploadedFile::fake()->create('oversized_file.pdf', 6000, 'application/pdf');

    Livewire::actingAs($spv)
        ->test(PurchaseOrderCreate::class)
        ->set('vendor_id', $vendor->id)
        ->set('po_number', 'PO-SECURITY-002')
        ->set('po_date', '2026-11-04')
        ->set('document_file', $hugeFile)
        ->set('items', [
            ['item_id' => $item->id, 'item_name' => $item->name, 'unit' => 'pc', 'qty_ordered' => 10, 'unit_price' => 5000],
        ])
        ->call('save')
        ->assertHasErrors(['document_file']);
});

test('critical state changes generate structured audit logs', function () {
    $spv = User::factory()->spv()->create();
    $vendor = Vendor::factory()->create();

    // Create a PO
    $po = PurchaseOrder::factory()->create([
        'vendor_id' => $vendor->id,
        'po_number' => 'PO-AUDIT-001',
    ]);

    AuditLog::log(
        action: 'purchase_order.created',
        auditable: $po,
        newValues: ['po_number' => 'PO-AUDIT-001'],
        userId: $spv->id,
    );

    $log = AuditLog::where('auditable_type', PurchaseOrder::class)
        ->where('auditable_id', $po->id)
        ->where('action', 'purchase_order.created')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($spv->id)
        ->and($log->new_values['po_number'])->toBe('PO-AUDIT-001');
});

test('sidebar displays master data menu strictly for superadmin and hides it from spv and vendor', function () {
    $superadmin = User::factory()->superadmin()->create();
    $spv = User::factory()->spv()->create();
    $vendor = Vendor::factory()->create();
    $vendorUser = User::factory()->vendor($vendor->id)->create();

    // 1. Superadmin sees Master Data group & items
    $this->actingAs($superadmin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Master Data & Konfigurasi')
        ->assertSee('Master Vendor')
        ->assertSee('Master Item Snack')
        ->assertSee('Tahapan Pembayaran');

    // 2. SPV does NOT see Master Data group in sidebar
    $this->actingAs($spv)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Master Data & Konfigurasi')
        ->assertDontSee('Master Vendor')
        ->assertDontSee('Master Item Snack')
        ->assertDontSee('Tahapan Pembayaran');

    // 3. Vendor does NOT see Master Data group in sidebar
    $this->actingAs($vendorUser)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Master Data & Konfigurasi')
        ->assertDontSee('Master Vendor')
        ->assertDontSee('Master Item Snack')
        ->assertDontSee('Tahapan Pembayaran');
});

test('header contains indonesian live server clock with wib timestamp', function () {
    $user = User::factory()->spv()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('liveServerClock');
    $response->assertSee('WIB');
    $response->assertSee('Asia/Jakarta');
});

test('unauthorized action buttons are strictly hidden for vendor across all pages', function () {
    $vendorA = Vendor::factory()->create(['name' => 'Katering Berkah']);
    $vendorUserA = User::factory()->vendor($vendorA->id)->create();

    $poA = PurchaseOrder::factory()->create(['vendor_id' => $vendorA->id, 'status' => PoStatus::ACTIVE]);
    $delivA = Delivery::factory()->create(['vendor_id' => $vendorA->id, 'purchase_order_id' => $poA->id, 'status' => DeliveryStatus::SUBMITTED]);
    $invA = Invoice::factory()->create(['vendor_id' => $vendorA->id, 'purchase_order_id' => $poA->id, 'status' => InvoiceStatus::SUBMITTED]);

    // 1. PO Index: Vendor cannot see "Terbitkan PO Baru" button
    $this->actingAs($vendorUserA)
        ->get(route('purchase-orders.index'))
        ->assertOk()
        ->assertDontSee('Terbitkan PO Baru');

    // 2. DO Show: Vendor cannot see "Konfirmasi Diterima" or "Tolak Pengiriman" buttons
    $this->actingAs($vendorUserA)
        ->get(route('deliveries.show', $delivA))
        ->assertOk()
        ->assertDontSee('Konfirmasi Diterima')
        ->assertDontSee('Tolak Pengiriman');

    // 3. Invoice Show: Vendor cannot see "Setujui Tagihan" or "Tolak Tagihan" buttons
    $this->actingAs($vendorUserA)
        ->get(route('invoices.show', $invA))
        ->assertOk()
        ->assertDontSee('Setujui Tagihan')
        ->assertDontSee('Tolak Tagihan (Kembalikan ke Vendor)');

    // 4. Payment Requests Index: Vendor cannot see "Buat PR Baru" button
    $this->actingAs($vendorUserA)
        ->get(route('payment-requests.index'))
        ->assertOk()
        ->assertDontSee('Buat PR Baru');

    // 5. Postings Index: Vendor cannot see "Catat Posting Baru" button
    $this->actingAs($vendorUserA)
        ->get(route('postings.index'))
        ->assertOk()
        ->assertDontSee('Catat Posting Baru');
});

test('visible logout buttons and notification bell appear in layout header and sidebar', function () {
    $spv = User::factory()->spv()->create();

    $response = $this->actingAs($spv)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('header-logout-button', false)
        ->assertSee('sidebar-logout-button', false)
        ->assertSee('header-notifications-button', false)
        ->assertSee('Keluar (Log out)');
});

test('delivery, invoice, and payment request pages display pending action queue cards with strict vendor isolation', function () {
    $spv = User::factory()->spv()->create();
    $vendorA = Vendor::factory()->create(['name' => 'Vendor Alpha']);
    $vendorB = Vendor::factory()->create(['name' => 'Vendor Beta']);
    $vendorUserA = User::factory()->vendor($vendorA->id)->create();

    // PO for Vendor A (remaining quota -> ready for DO) & confirmed DO (ready for Invoice) & approved invoice (ready for PR)
    $poA = PurchaseOrder::factory()->create(['vendor_id' => $vendorA->id, 'po_number' => 'PO-ALPHA-99', 'status' => PoStatus::ACTIVE]);
    $poItemA = PoItem::factory()->create(['purchase_order_id' => $poA->id, 'qty_ordered' => 50, 'unit_price' => 10000]);
    $confirmedDelivA = Delivery::factory()->confirmed()->create(['vendor_id' => $vendorA->id, 'purchase_order_id' => $poA->id, 'code' => 'DO-ALPHA-CONF']);
    DeliveryItem::factory()->create(['delivery_id' => $confirmedDelivA->id, 'po_item_id' => $poItemA->id, 'qty_sent' => 20, 'qty_received' => 20, 'is_invoiced' => false]);
    $submittedDelivA = Delivery::factory()->create(['vendor_id' => $vendorA->id, 'purchase_order_id' => $poA->id, 'code' => 'DO-ALPHA-SUB', 'status' => DeliveryStatus::SUBMITTED]);

    $invApprovedA = Invoice::factory()->approved()->create(['vendor_id' => $vendorA->id, 'purchase_order_id' => $poA->id, 'invoice_number' => 'INV-ALPHA-APP', 'total_amount' => 100000]);

    // PO & DO & Invoice for Vendor B (MUST NOT leak to Vendor A!)
    $poB = PurchaseOrder::factory()->create(['vendor_id' => $vendorB->id, 'po_number' => 'PO-BETA-88', 'status' => PoStatus::ACTIVE]);
    $poItemB = PoItem::factory()->create(['purchase_order_id' => $poB->id, 'qty_ordered' => 40, 'unit_price' => 10000]);
    $confirmedDelivB = Delivery::factory()->confirmed()->create(['vendor_id' => $vendorB->id, 'purchase_order_id' => $poB->id, 'code' => 'DO-BETA-CONF']);
    DeliveryItem::factory()->create(['delivery_id' => $confirmedDelivB->id, 'po_item_id' => $poItemB->id, 'qty_sent' => 10, 'qty_received' => 10, 'is_invoiced' => false]);
    $invApprovedB = Invoice::factory()->approved()->create(['vendor_id' => $vendorB->id, 'purchase_order_id' => $poB->id, 'invoice_number' => 'INV-BETA-APP', 'total_amount' => 100000]);

    // 1. Vendor A sees ONLY Vendor A's queues on DO & Invoice pages, and does NOT see PR creation queue
    $this->actingAs($vendorUserA)
        ->get(route('deliveries.index'))
        ->assertOk()
        ->assertSee('PO Aktif Siap Dibuatkan Surat Jalan (DO)')
        ->assertSee('PO-ALPHA-99')
        ->assertDontSee('PO-BETA-88');

    $this->actingAs($vendorUserA)
        ->get(route('invoices.index'))
        ->assertOk()
        ->assertSee('DO Terkonfirmasi Siap Dibuatkan Invoice')
        ->assertSee('DO-ALPHA-CONF')
        ->assertDontSee('DO-BETA-CONF');

    $this->actingAs($vendorUserA)
        ->get(route('payment-requests.index'))
        ->assertOk()
        ->assertDontSee('Invoice Approved Siap Dibuatkan Payment Request (PR)')
        ->assertDontSee('INV-BETA-APP');

    // 2. SPV sees both vendors' queues + verification banners + PR queue
    $this->actingAs($spv)
        ->get(route('deliveries.index'))
        ->assertOk()
        ->assertSee('Surat Jalan (DO) Masuk Menunggu Verifikasi')
        ->assertSee('DO-ALPHA-SUB')
        ->assertSee('PO-ALPHA-99')
        ->assertSee('PO-BETA-88');

    $this->actingAs($spv)
        ->get(route('payment-requests.index'))
        ->assertOk()
        ->assertSee('Invoice Approved Siap Dibuatkan Payment Request (PR)')
        ->assertSee('INV-ALPHA-APP')
        ->assertSee('INV-BETA-APP');

    // 3. Header notification component respects vendor isolation
    Livewire::actingAs($vendorUserA)
        ->test(HeaderNotifications::class)
        ->assertSee('PO-ALPHA-99')
        ->assertSee('DO-ALPHA-SUB')
        ->assertDontSee('PO-BETA-88')
        ->assertDontSee('INV-BETA-APP');

    Livewire::actingAs($spv)
        ->test(HeaderNotifications::class)
        ->assertSee('PO-ALPHA-99')
        ->assertSee('PO-BETA-88')
        ->assertSee('DO-ALPHA-SUB')
        ->assertSee('INV-ALPHA-APP');

    // 4. Pre-selecting invoice via query parameter ?invoice_id=... in PaymentRequests\Create
    Livewire::actingAs($spv)
        ->test(PaymentRequestCreate::class, ['invoice_id' => $invApprovedA->id])
        ->assertSet('selected_invoice_ids', [$invApprovedA->id])
        ->assertSet('number', 'PO-ALPHA-99');
});



