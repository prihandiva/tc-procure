<?php

use App\Enums\DeliveryStatus;
use App\Enums\DocumentType;
use App\Enums\InvoiceStatus;
use App\Enums\ItemType;
use App\Enums\PoStatus;
use App\Enums\PoType;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Item;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestStageLog;
use App\Models\PaymentStage;
use App\Models\PoItem;
use App\Models\Posting;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;

test('vendor model relationships and scopes work', function () {
    $vendor = Vendor::factory()->create();
    $user = User::factory()->vendor($vendor->id)->create();
    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);

    expect($vendor->users)->toHaveCount(1)
        ->and($vendor->users->first()->id)->toBe($user->id)
        ->and($vendor->purchaseOrders)->toHaveCount(1)
        ->and($vendor->purchaseOrders->first()->id)->toBe($po->id);
});

test('item model types and relations work', function () {
    $goods = Item::factory()->create(['type' => ItemType::GOODS, 'unit' => 'pc', 'default_price' => 3500]);
    $service = Item::factory()->service()->create(['default_price' => 25000]);

    expect($goods->isGoods())->toBeTrue()
        ->and($goods->isService())->toBeFalse()
        ->and($service->isService())->toBeTrue();
});

test('purchase order and po item relations and calculations work', function () {
    $vendor = Vendor::factory()->create();
    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);

    $poItem1 = PoItem::factory()->create([
        'purchase_order_id' => $po->id,
        'qty_ordered' => 100.00,
        'unit_price' => 3500,
    ]);

    $poItem2 = PoItem::factory()->create([
        'purchase_order_id' => $po->id,
        'qty_ordered' => 50.00,
        'unit_price' => 1500,
    ]);

    $po->refresh();

    expect($po->poItems)->toHaveCount(2)
        ->and($poItem1->subtotal)->toBe(350000)
        ->and($poItem2->subtotal)->toBe(75000)
        ->and($po->total_amount)->toBe(425000)
        ->and($po->total_qty_ordered)->toBe(150.0)
        ->and($po->vendor->id)->toBe($vendor->id);
});

test('delivery and delivery item relationships work', function () {
    $vendor = Vendor::factory()->create();
    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);
    $poItem = PoItem::factory()->create(['purchase_order_id' => $po->id, 'qty_ordered' => 100, 'unit_price' => 3500]);

    $delivery = Delivery::factory()->create([
        'purchase_order_id' => $po->id,
        'vendor_id' => $vendor->id,
        'status' => DeliveryStatus::SUBMITTED,
    ]);

    $deliveryItem = DeliveryItem::factory()->create([
        'delivery_id' => $delivery->id,
        'po_item_id' => $poItem->id,
        'qty_sent' => 25.00,
        'qty_received' => null,
    ]);

    expect($delivery->deliveryItems)->toHaveCount(1)
        ->and($deliveryItem->poItem->id)->toBe($poItem->id)
        ->and($delivery->isSubmitted())->toBeTrue();
});

test('invoice and invoice lines relationship and amount calculation work', function () {
    $vendor = Vendor::factory()->create();
    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);
    $poItem = PoItem::factory()->create(['purchase_order_id' => $po->id, 'unit_price' => 3500]);
    $delivery = Delivery::factory()->confirmed()->create(['purchase_order_id' => $po->id, 'vendor_id' => $vendor->id]);
    $deliveryItem = DeliveryItem::factory()->create([
        'delivery_id' => $delivery->id,
        'po_item_id' => $poItem->id,
        'qty_sent' => 20.00,
        'qty_received' => 20.00,
    ]);

    $invoice = Invoice::factory()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'total_amount' => 70000,
    ]);

    $line = InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'delivery_item_id' => $deliveryItem->id,
        'qty' => 20.00,
        'unit_price' => 3500,
        'amount' => 70000,
    ]);

    expect($invoice->invoiceLines)->toHaveCount(1)
        ->and($invoice->calculated_total)->toBe(70000)
        ->and($line->deliveryItem->id)->toBe($deliveryItem->id);
});

test('payment request, stage, posting, and log relations work', function () {
    $stage1 = PaymentStage::factory()->create(['name' => 'Proses ACC', 'sort_order' => 1]);
    $stage2 = PaymentStage::factory()->create(['name' => 'Proses Finance', 'sort_order' => 2]);
    $posting = Posting::factory()->create(['document_number' => '5000078901', 'total_paid' => 500000]);

    $pr = PaymentRequest::factory()->create([
        'number' => 'TC/PC/15-X/2026/001',
        'current_stage_id' => $stage1->id,
        'posting_id' => $posting->id,
    ]);

    $invoice = Invoice::factory()->create();
    $pr->invoices()->attach($invoice->id);

    $spv = User::factory()->spv()->create();
    $log = PaymentRequestStageLog::create([
        'payment_request_id' => $pr->id,
        'from_stage_id' => $stage1->id,
        'to_stage_id' => $stage2->id,
        'changed_by' => $spv->id,
        'notes' => 'Disetujui dan diteruskan ke finance',
        'created_at' => now(),
    ]);

    $pr->refresh();

    expect($pr->currentStage->id)->toBe($stage1->id)
        ->and($pr->posting->id)->toBe($posting->id)
        ->and($pr->invoices)->toHaveCount(1)
        ->and($pr->stageLogs)->toHaveCount(1)
        ->and($pr->stageLogs->first()->toStage->id)->toBe($stage2->id);
});

test('polymorphic documents work for purchase orders and invoices', function () {
    $po = PurchaseOrder::factory()->create();
    $spv = User::factory()->spv()->create();

    $doc = Document::create([
        'documentable_type' => PurchaseOrder::class,
        'documentable_id' => $po->id,
        'type' => DocumentType::PO,
        'path' => 'documents/po_sample.pdf',
        'original_name' => 'po_sample.pdf',
        'mime' => 'application/pdf',
        'size' => 204800, // 200 KB
        'uploaded_by' => $spv->id,
    ]);

    expect($po->documents)->toHaveCount(1)
        ->and($po->documents->first()->original_name)->toBe('po_sample.pdf')
        ->and($doc->formatted_size)->toBe('200.00 KB')
        ->and($doc->uploader->id)->toBe($spv->id);
});

test('audit log helper records changes correctly', function () {
    $user = User::factory()->spv()->create();
    $vendor = Vendor::factory()->create(['name' => 'Old Vendor']);

    $log = AuditLog::log(
        action: 'updated',
        auditable: $vendor,
        newValues: ['name' => 'New Vendor'],
        oldValues: ['name' => 'Old Vendor'],
        userId: $user->id
    );

    expect($log->action)->toBe('updated')
        ->and($log->auditable_type)->toBe($vendor->getMorphClass())
        ->and($log->auditable_id)->toBe($vendor->id)
        ->and($log->user->id)->toBe($user->id)
        ->and($log->new_values['name'])->toBe('New Vendor');
});

test('vendor global scope isolates data between different vendors', function () {
    $vendorA = Vendor::factory()->create(['name' => 'Vendor A']);
    $vendorB = Vendor::factory()->create(['name' => 'Vendor B']);

    $poA = PurchaseOrder::factory()->create(['vendor_id' => $vendorA->id]);
    $poB = PurchaseOrder::factory()->create(['vendor_id' => $vendorB->id]);

    $vendorUserA = User::factory()->vendor($vendorA->id)->create();

    // As Vendor A
    $this->actingAs($vendorUserA);

    $visiblePos = PurchaseOrder::all();
    expect($visiblePos)->toHaveCount(1)
        ->and($visiblePos->first()->id)->toBe($poA->id);

    // As SPV (can see all)
    $spv = User::factory()->spv()->create();
    $this->actingAs($spv);

    expect(PurchaseOrder::count())->toBe(2);
});

