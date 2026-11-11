<?php

use App\Enums\DeliveryStatus;
use App\Enums\DocumentType;
use App\Enums\InvoiceStatus;
use App\Livewire\Invoices\Create as InvoiceCreate;
use App\Livewire\Invoices\Index as InvoicesIndex;
use App\Livewire\Invoices\Show as InvoiceShow;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\PoItem;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('vendor can create invoice combining multiple confirmed deliveries from different dates', function () {
    Storage::fake('local');

    $vendor = Vendor::factory()->create(['name' => 'CV Yulia Catering']);
    $vendorUser = User::factory()->vendor($vendor->id)->create();

    $po = PurchaseOrder::factory()->create([
        'vendor_id' => $vendor->id,
        'po_number' => '47157028',
    ]);

    $poItem1 = PoItem::factory()->create([
        'purchase_order_id' => $po->id,
        'item_name' => 'Snack Basah',
        'unit_price' => 3500,
    ]);

    $poItem2 = PoItem::factory()->create([
        'purchase_order_id' => $po->id,
        'item_name' => 'Ongkos Kirim',
        'unit_price' => 25000,
    ]);

    // Delivery 1 (Date: 2026-11-01)
    $delivery1 = Delivery::factory()->confirmed()->create([
        'purchase_order_id' => $po->id,
        'vendor_id' => $vendor->id,
        'delivery_date' => '2026-11-01',
    ]);
    $delivItem1 = DeliveryItem::factory()->create([
        'delivery_id' => $delivery1->id,
        'po_item_id' => $poItem1->id,
        'qty_sent' => 20.00,
        'qty_received' => 20.00,
        'is_invoiced' => false,
    ]);

    // Delivery 2 (Date: 2026-11-02)
    $delivery2 = Delivery::factory()->confirmed()->create([
        'purchase_order_id' => $po->id,
        'vendor_id' => $vendor->id,
        'delivery_date' => '2026-11-02',
    ]);
    $delivItem2 = DeliveryItem::factory()->create([
        'delivery_id' => $delivery2->id,
        'po_item_id' => $poItem1->id,
        'qty_sent' => 30.00,
        'qty_received' => 30.00,
        'is_invoiced' => false,
    ]);
    $delivItem3 = DeliveryItem::factory()->create([
        'delivery_id' => $delivery2->id,
        'po_item_id' => $poItem2->id,
        'qty_sent' => 1.00,
        'qty_received' => 1.00,
        'is_invoiced' => false,
    ]);

    $file = UploadedFile::fake()->create('inv_yulia_nov_01.pdf', 300, 'application/pdf');

    $this->actingAs($vendorUser);

    Livewire::test(InvoiceCreate::class)
        ->set('purchase_order_id', $po->id)
        ->set('invoice_number', 'INV/YULIA/2026/01')
        ->set('invoice_date', '2026-11-03')
        ->set('document_file', $file)
        ->set('selected_item_ids', [$delivItem1->id, $delivItem2->id, $delivItem3->id])
        ->call('save')
        ->assertHasNoErrors();

    // Expected total: (20 * 3500) + (30 * 3500) + (1 * 25000) = 70.000 + 105.000 + 25.000 = 200.000
    $invoice = Invoice::where('invoice_number', 'INV/YULIA/2026/01')->first();
    expect($invoice)->not->toBeNull()
        ->and($invoice->total_amount)->toBe(200000)
        ->and($invoice->status)->toBe(InvoiceStatus::SUBMITTED)
        ->and($invoice->invoiceLines)->toHaveCount(3)
        ->and($invoice->documents)->toHaveCount(1)
        ->and($invoice->documents->first()->type)->toBe(DocumentType::INVOICE);

    // Rule: Delivery items are now marked as invoiced
    expect($delivItem1->fresh()->is_invoiced)->toBeTrue()
        ->and($delivItem2->fresh()->is_invoiced)->toBeTrue()
        ->and($delivItem3->fresh()->is_invoiced)->toBeTrue();
});

test('double-billing prevention blocks invoicing an already invoiced delivery item', function () {
    $vendor = Vendor::factory()->create();
    $vendorUser = User::factory()->vendor($vendor->id)->create();

    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);
    $poItem = PoItem::factory()->create(['purchase_order_id' => $po->id, 'unit_price' => 5000]);

    $delivery = Delivery::factory()->confirmed()->create(['purchase_order_id' => $po->id, 'vendor_id' => $vendor->id]);
    $delivItem = DeliveryItem::factory()->create([
        'delivery_id' => $delivery->id,
        'po_item_id' => $poItem->id,
        'qty_received' => 10.00,
        'is_invoiced' => true, // Already invoiced!
    ]);

    $this->actingAs($vendorUser);

    Livewire::test(InvoiceCreate::class)
        ->set('purchase_order_id', $po->id)
        ->set('invoice_number', 'INV-DOUBLE-TEST')
        ->set('invoice_date', '2026-11-03')
        ->set('selected_item_ids', [$delivItem->id])
        ->call('save')
        ->assertHasErrors(['selected_item_ids']);

    expect(Invoice::where('invoice_number', 'INV-DOUBLE-TEST')->exists())->toBeFalse();
});

test('unconfirmed delivery items cannot be invoiced', function () {
    $vendor = Vendor::factory()->create();
    $vendorUser = User::factory()->vendor($vendor->id)->create();

    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);
    $poItem = PoItem::factory()->create(['purchase_order_id' => $po->id, 'unit_price' => 5000]);

    // Delivery is still SUBMITTED (not confirmed yet)
    $delivery = Delivery::factory()->create([
        'purchase_order_id' => $po->id,
        'vendor_id' => $vendor->id,
        'status' => DeliveryStatus::SUBMITTED,
    ]);
    $delivItem = DeliveryItem::factory()->create([
        'delivery_id' => $delivery->id,
        'po_item_id' => $poItem->id,
        'qty_sent' => 10.00,
        'is_invoiced' => false,
    ]);

    $this->actingAs($vendorUser);

    Livewire::test(InvoiceCreate::class)
        ->set('purchase_order_id', $po->id)
        ->set('invoice_number', 'INV-UNCONFIRMED')
        ->set('invoice_date', '2026-11-03')
        ->set('selected_item_ids', [$delivItem->id])
        ->call('save')
        ->assertHasErrors(['selected_item_ids']);
});

test('3-way match validation verifies price, qty, and invoice uniqueness', function () {
    $vendor = Vendor::factory()->create();
    $spv = User::factory()->spv()->create();

    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);
    $poItem = PoItem::factory()->create(['purchase_order_id' => $po->id, 'unit_price' => 3500]);

    $delivery = Delivery::factory()->confirmed()->create(['purchase_order_id' => $po->id, 'vendor_id' => $vendor->id]);
    $delivItem = DeliveryItem::factory()->create([
        'delivery_id' => $delivery->id,
        'po_item_id' => $poItem->id,
        'qty_received' => 20.00,
    ]);

    $invoice = Invoice::factory()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'invoice_number' => 'INV-MATCH-VALID',
        'status' => InvoiceStatus::SUBMITTED,
        'total_amount' => 70000,
    ]);

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'delivery_item_id' => $delivItem->id,
        'qty' => 20.00, // Matches DO received
        'unit_price' => 3500, // Matches PO unit price
        'amount' => 70000,
    ]);

    expect($invoice->checkPriceMatch())->toBeTrue()
        ->and($invoice->checkQuantityMatch())->toBeTrue()
        ->and($invoice->hasDuplicateNumber())->toBeFalse()
        ->and($invoice->isThreeWayMatched())->toBeTrue();
});

test('duplicate invoice number for same vendor triggers anomaly flag', function () {
    $vendor = Vendor::factory()->create();
    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);

    // Prior invoice already active
    Invoice::factory()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'invoice_number' => 'INV-DUPLICATE-01',
        'status' => InvoiceStatus::APPROVED,
    ]);

    // Second invoice with same number
    $invoice2 = Invoice::factory()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'invoice_number' => 'INV-DUPLICATE-01',
        'status' => InvoiceStatus::SUBMITTED,
    ]);

    expect($invoice2->hasDuplicateNumber())->toBeTrue()
        ->and($invoice2->isThreeWayMatched())->toBeFalse();
});

test('spv can approve invoice and transition status to approved', function () {
    $vendor = Vendor::factory()->create();
    $spv = User::factory()->spv()->create();

    $invoice = Invoice::factory()->create([
        'vendor_id' => $vendor->id,
        'status' => InvoiceStatus::SUBMITTED,
    ]);

    $this->actingAs($spv);

    Livewire::test(InvoiceShow::class, ['invoice' => $invoice])
        ->call('approveInvoice')
        ->assertHasNoErrors();

    expect($invoice->fresh()->isApproved())->toBeTrue();
});

test('spv can reject invoice with reason which unlocks delivery items for revision', function () {
    $vendor = Vendor::factory()->create();
    $spv = User::factory()->spv()->create();

    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);
    $poItem = PoItem::factory()->create(['purchase_order_id' => $po->id]);

    $delivery = Delivery::factory()->confirmed()->create(['purchase_order_id' => $po->id, 'vendor_id' => $vendor->id]);
    $delivItem = DeliveryItem::factory()->create([
        'delivery_id' => $delivery->id,
        'po_item_id' => $poItem->id,
        'is_invoiced' => true,
    ]);

    $invoice = Invoice::factory()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'status' => InvoiceStatus::SUBMITTED,
    ]);

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'delivery_item_id' => $delivItem->id,
    ]);

    $this->actingAs($spv);

    Livewire::test(InvoiceShow::class, ['invoice' => $invoice])
        ->set('reject_reason', 'Total nominal faktur salah perhitungan pajak.')
        ->call('rejectInvoice')
        ->assertHasNoErrors();

    $invoice->refresh();
    expect($invoice->isRejected())->toBeTrue()
        ->and($invoice->reject_reason)->toContain('Total nominal faktur salah')
        ->and($delivItem->fresh()->is_invoiced)->toBeFalse(); // Unlocked for vendor revision!
});

test('vendor a cannot access vendor b invoice', function () {
    $vendorA = Vendor::factory()->create();
    $vendorB = Vendor::factory()->create();

    $vendorUserA = User::factory()->vendor($vendorA->id)->create();
    $invoiceB = Invoice::factory()->create(['vendor_id' => $vendorB->id]);

    $this->actingAs($vendorUserA);

    $this->get(route('invoices.show', $invoiceB))
        ->assertNotFound();
});

test('invoice number is auto-filled from po number, editable, and suggests suffix if already used by vendor', function () {
    $vendor = Vendor::factory()->create();
    $vendorUser = User::factory()->vendor($vendor->id)->create();

    $po = PurchaseOrder::factory()->create([
        'vendor_id' => $vendor->id,
        'po_number' => '47158801',
    ]);
    $poItem = PoItem::factory()->create(['purchase_order_id' => $po->id, 'qty_ordered' => 20, 'unit_price' => 5000]);

    $deliv1 = Delivery::factory()->confirmed()->create(['purchase_order_id' => $po->id, 'vendor_id' => $vendor->id]);
    $delivItem1 = DeliveryItem::factory()->create([
        'delivery_id' => $deliv1->id,
        'po_item_id' => $poItem->id,
        'qty_sent' => 10,
        'qty_received' => 10,
        'is_invoiced' => false,
    ]);

    $this->actingAs($vendorUser);

    // 1. Selecting PO auto-fills invoice_number with PO number and is editable
    Livewire::test(InvoiceCreate::class)
        ->set('purchase_order_id', $po->id)
        ->assertSet('invoice_number', '47158801')
        ->set('invoice_number', 'INV-COMP-8801')
        ->set('selected_item_ids', [$delivItem1->id])
        ->call('save')
        ->assertHasNoErrors();

    expect(Invoice::where('invoice_number', 'INV-COMP-8801')->exists())->toBeTrue();

    // 2. Existing invoice using PO number triggers -2 suggestion for subsequent invoice
    Invoice::factory()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'invoice_number' => '47158801',
    ]);

    Livewire::test(InvoiceCreate::class, ['po_id' => $po->id])
        ->assertSet('invoice_number', '47158801-2');
});

test('invoice create renders searchable po dropdown with po number and search input', function () {
    $vendor = Vendor::factory()->create(['name' => 'CV Search Invoice']);
    $vendorUser = User::factory()->vendor($vendor->id)->create();

    $po = PurchaseOrder::factory()->create([
        'vendor_id' => $vendor->id,
        'po_number' => '47158899',
    ]);
    $poItem = PoItem::factory()->create(['purchase_order_id' => $po->id, 'qty_ordered' => 10, 'unit_price' => 5000]);
    $deliv = Delivery::factory()->confirmed()->create(['purchase_order_id' => $po->id, 'vendor_id' => $vendor->id]);
    DeliveryItem::factory()->create([
        'delivery_id' => $deliv->id,
        'po_item_id' => $poItem->id,
        'qty_sent' => 10,
        'qty_received' => 10,
        'is_invoiced' => false,
    ]);

    Livewire::actingAs($vendorUser)
        ->test(InvoiceCreate::class)
        ->assertSee('Ketik nomor PO atau nama vendor...')
        ->assertSee('47158899')
        ->assertSee('CV Search Invoice');
});

