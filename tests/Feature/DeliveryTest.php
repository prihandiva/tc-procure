<?php

use App\Enums\DeliveryStatus;
use App\Enums\DocumentType;
use App\Enums\PoStatus;
use App\Livewire\Deliveries\Create as DeliveryCreate;
use App\Livewire\Deliveries\Index as DeliveriesIndex;
use App\Livewire\Deliveries\Show as DeliveryShow;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\PoItem;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('vendor can input delivery with code auto-filled from po number and attached do document', function () {
    Storage::fake('local');

    $vendor = Vendor::factory()->create(['name' => 'CV Yulia Catering']);
    $vendorUser = User::factory()->vendor($vendor->id)->create();

    $po = PurchaseOrder::factory()->create([
        'vendor_id' => $vendor->id,
        'po_number' => '47157028',
        'status' => PoStatus::ACTIVE,
    ]);

    $poItem1 = PoItem::factory()->create([
        'purchase_order_id' => $po->id,
        'item_name' => 'Snack Basah',
        'unit' => 'pc',
        'qty_ordered' => 100.00,
        'unit_price' => 3500,
    ]);

    $poItem2 = PoItem::factory()->create([
        'purchase_order_id' => $po->id,
        'item_name' => 'Ongkos Kirim',
        'unit' => 'au',
        'qty_ordered' => 10.00,
        'unit_price' => 25000,
    ]);

    $file = UploadedFile::fake()->create('do_scan_101.jpg', 300, 'image/jpeg');

    $this->actingAs($vendorUser);

    Livewire::test(DeliveryCreate::class)
        ->set('purchase_order_id', $po->id)
        ->set('delivery_date', '2026-11-03')
        ->set('vendor_do_number', 'DO-YULIA-001')
        ->set('document_file', $file)
        ->set('items', [
            [
                'po_item_id' => $poItem1->id,
                'item_name' => 'Snack Basah',
                'unit' => 'pc',
                'qty_remaining' => 100.00,
                'qty_sent' => 40.00,
                'training_name' => 'Training Mandiri Batch 1',
                'needed_date' => '2026-11-03',
                'variant_note' => '10 lemper, 10 risoles, 10 bolen, 10 puding',
            ],
            [
                'po_item_id' => $poItem2->id,
                'item_name' => 'Ongkos Kirim',
                'unit' => 'au',
                'qty_remaining' => 10.00,
                'qty_sent' => 1.00,
                'training_name' => 'Training Mandiri Batch 1',
                'needed_date' => '2026-11-03',
                'variant_note' => 'Motor',
            ],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $delivery = Delivery::where('vendor_do_number', 'DO-YULIA-001')->first();
    expect($delivery)->not->toBeNull()
        ->and($delivery->code)->toBe('47157028')
        ->and($delivery->status)->toBe(DeliveryStatus::SUBMITTED)
        ->and($delivery->deliveryItems)->toHaveCount(2)
        ->and($delivery->documents)->toHaveCount(1)
        ->and($delivery->documents->first()->type)->toBe(DocumentType::DELIVERY_ORDER);

    Storage::disk('local')->assertExists($delivery->documents->first()->path);

    // Rule: Sisa PO BELUM berkurang saat status masih 'submitted'
    $poItem1->refresh();
    expect($poItem1->qty_delivered)->toBe(0.0)
        ->and($poItem1->qty_remaining)->toBe(100.0);
});

test('vendor cannot over-deliver exceeding po remaining quantity', function () {
    $vendor = Vendor::factory()->create();
    $vendorUser = User::factory()->vendor($vendor->id)->create();

    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);
    $poItem = PoItem::factory()->create([
        'purchase_order_id' => $po->id,
        'item_name' => 'Nasi Box',
        'unit' => 'box',
        'qty_ordered' => 20.00,
        'unit_price' => 30000,
    ]);

    $this->actingAs($vendorUser);

    Livewire::test(DeliveryCreate::class)
        ->set('purchase_order_id', $po->id)
        ->set('delivery_date', '2026-11-03')
        ->set('items', [
            [
                'po_item_id' => $poItem->id,
                'item_name' => 'Nasi Box',
                'unit' => 'box',
                'qty_remaining' => 20.00,
                'qty_sent' => 25.00, // Exceeds 20!
                'training_name' => 'Over delivery test',
                'needed_date' => '2026-11-03',
                'variant_note' => '',
            ],
        ])
        ->call('save')
        ->assertHasErrors(['items']);

    expect(Delivery::count())->toBe(0);
});

test('spv confirms delivery and po remaining quantity decreases atomically', function () {
    $vendor = Vendor::factory()->create();
    $spv = User::factory()->spv()->create();

    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);
    $poItem = PoItem::factory()->create([
        'purchase_order_id' => $po->id,
        'qty_ordered' => 50.00,
        'unit_price' => 3500,
    ]);

    $delivery = Delivery::factory()->create([
        'purchase_order_id' => $po->id,
        'vendor_id' => $vendor->id,
        'status' => DeliveryStatus::SUBMITTED,
    ]);

    $delivItem = DeliveryItem::factory()->create([
        'delivery_id' => $delivery->id,
        'po_item_id' => $poItem->id,
        'qty_sent' => 30.00,
        'qty_received' => null,
    ]);

    $this->actingAs($spv);

    Livewire::test(DeliveryShow::class, ['delivery' => $delivery])
        ->set("receivedQuantities.{$delivItem->id}", 30.00)
        ->call('confirmDelivery')
        ->assertHasNoErrors();

    $delivery->refresh();
    $poItem->refresh();
    $po->refresh();

    expect($delivery->isConfirmed())->toBeTrue()
        ->and($delivery->confirmed_by)->toBe($spv->id)
        ->and((float) $delivItem->fresh()->qty_received)->toBe(30.0)
        ->and($poItem->qty_delivered)->toBe(30.0)
        ->and($poItem->qty_remaining)->toBe(20.0)
        ->and($po->total_qty_delivered)->toBe(30.0)
        ->and($po->total_qty_remaining)->toBe(20.0);
});

test('spv records quantity discrepancy when physical received items differ from sent', function () {
    $vendor = Vendor::factory()->create();
    $spv = User::factory()->spv()->create();

    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);
    $poItem = PoItem::factory()->create([
        'purchase_order_id' => $po->id,
        'qty_ordered' => 50.00,
    ]);

    $delivery = Delivery::factory()->create([
        'purchase_order_id' => $po->id,
        'vendor_id' => $vendor->id,
        'status' => DeliveryStatus::SUBMITTED,
    ]);

    $delivItem = DeliveryItem::factory()->create([
        'delivery_id' => $delivery->id,
        'po_item_id' => $poItem->id,
        'qty_sent' => 25.00, // Vendor sent 25
        'qty_received' => null,
    ]);

    $this->actingAs($spv);

    // SPV received only 22 (3 damaged/missing)
    Livewire::test(DeliveryShow::class, ['delivery' => $delivery])
        ->set("receivedQuantities.{$delivItem->id}", 22.00)
        ->call('confirmDelivery')
        ->assertHasNoErrors();

    $delivery->refresh();
    $delivItem->refresh();
    $poItem->refresh();

    expect($delivery->hasDiscrepancy())->toBeTrue()
        ->and($delivItem->hasDifference())->toBeTrue()
        ->and($delivItem->difference)->toBe(-3.0)
        ->and($poItem->qty_delivered)->toBe(22.0)
        ->and($poItem->qty_remaining)->toBe(28.0);
});

test('spv can reject delivery with reason without reducing po balance', function () {
    $vendor = Vendor::factory()->create();
    $spv = User::factory()->spv()->create();

    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);
    $poItem = PoItem::factory()->create([
        'purchase_order_id' => $po->id,
        'qty_ordered' => 40.00,
    ]);

    $delivery = Delivery::factory()->create([
        'purchase_order_id' => $po->id,
        'vendor_id' => $vendor->id,
        'status' => DeliveryStatus::SUBMITTED,
    ]);

    $delivItem = DeliveryItem::factory()->create([
        'delivery_id' => $delivery->id,
        'po_item_id' => $poItem->id,
        'qty_sent' => 40.00,
    ]);

    $this->actingAs($spv);

    Livewire::test(DeliveryShow::class, ['delivery' => $delivery])
        ->set('reject_reason', 'Makanan rusak dan tidak layak konsumsi saat pengiriman.')
        ->call('rejectDelivery')
        ->assertHasNoErrors();

    $delivery->refresh();
    $poItem->refresh();

    expect($delivery->isRejected())->toBeTrue()
        ->and($delivery->reject_reason)->toContain('Makanan rusak')
        ->and($poItem->qty_delivered)->toBe(0.0)
        ->and($poItem->qty_remaining)->toBe(40.0);
});

test('vendor cannot confirm delivery', function () {
    $vendor = Vendor::factory()->create();
    $vendorUser = User::factory()->vendor($vendor->id)->create();
    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);

    $delivery = Delivery::factory()->create([
        'purchase_order_id' => $po->id,
        'vendor_id' => $vendor->id,
        'status' => DeliveryStatus::SUBMITTED,
    ]);

    $this->actingAs($vendorUser);

    Livewire::test(DeliveryShow::class, ['delivery' => $delivery])
        ->call('confirmDelivery')
        ->assertForbidden();
});

test('vendor a cannot submit delivery against vendor b purchase order', function () {
    $vendorA = Vendor::factory()->create();
    $vendorB = Vendor::factory()->create();

    $vendorUserA = User::factory()->vendor($vendorA->id)->create();
    $poB = PurchaseOrder::factory()->create(['vendor_id' => $vendorB->id]);
    $poItemB = PoItem::factory()->create(['purchase_order_id' => $poB->id, 'qty_ordered' => 20]);

    $this->actingAs($vendorUserA);

    Livewire::test(DeliveryCreate::class)
        ->set('purchase_order_id', $poB->id)
        ->set('delivery_date', '2026-11-03')
        ->set('items', [
            [
                'po_item_id' => $poItemB->id,
                'item_name' => $poItemB->item_name,
                'unit' => 'pc',
                'qty_remaining' => 20,
                'qty_sent' => 5,
                'training_name' => '',
                'needed_date' => '',
                'variant_note' => '',
            ],
        ])
        ->call('save')
        ->assertNotFound();
});

test('delivery create handles string inputs for qty_sent without type error in PHP 8.4', function () {
    $vendor = Vendor::factory()->create();
    $vendorUser = User::factory()->vendor($vendor->id)->create();

    $po = PurchaseOrder::factory()->create([
        'vendor_id' => $vendor->id,
        'status' => PoStatus::ACTIVE,
    ]);
    $poItem = PoItem::factory()->create(['purchase_order_id' => $po->id, 'qty_ordered' => 20]);

    Livewire::actingAs($vendorUser)
        ->test(DeliveryCreate::class)
        ->set('purchase_order_id', $po->id)
        ->set('items.0.qty_sent', '10')
        ->assertOk()
        ->set('items.0.qty_sent', '')
        ->assertOk();
});

test('delivery show dynamically displays discrepancy difference when received quantity is adjusted', function () {
    $spv = User::factory()->spv()->create();
    $vendor = Vendor::factory()->create();
    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);
    $poItem = PoItem::factory()->create(['purchase_order_id' => $po->id, 'qty_ordered' => 50]);

    $delivery = Delivery::factory()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'status' => DeliveryStatus::SUBMITTED,
    ]);

    $item = DeliveryItem::factory()->create([
        'delivery_id' => $delivery->id,
        'po_item_id' => $poItem->id,
        'qty_sent' => 20.00,
        'qty_received' => null,
    ]);

    // Initial default: received 20 -> difference 0 -> shows "0,00 (Sesuai)"
    Livewire::actingAs($spv)
        ->test(DeliveryShow::class, ['delivery' => $delivery])
        ->assertSee('0,00 (Sesuai)')
        // When SPV inputs 18 (short delivery of 2) -> shows "Kurang 2,00"
        ->set("receivedQuantities.{$item->id}", 18)
        ->assertSee('Kurang 2,00');
});

test('delivery code is auto-filled from po number, editable, and suggests suffix for subsequent partial deliveries', function () {
    $vendor = Vendor::factory()->create();
    $vendorUser = User::factory()->vendor($vendor->id)->create();

    $po = PurchaseOrder::factory()->create([
        'vendor_id' => $vendor->id,
        'po_number' => '47157099',
        'status' => PoStatus::ACTIVE,
    ]);
    $poItem = PoItem::factory()->create([
        'purchase_order_id' => $po->id,
        'qty_ordered' => 50,
    ]);

    $this->actingAs($vendorUser);

    // 1. Selecting PO auto-fills `code` with PO number, user can keep or edit it
    Livewire::test(DeliveryCreate::class)
        ->set('purchase_order_id', $po->id)
        ->assertSet('code', '47157099')
        ->set('code', 'DO-47157099-CUSTOM')
        ->set('items.0.qty_sent', 20)
        ->call('save')
        ->assertHasNoErrors();

    expect(Delivery::where('code', 'DO-47157099-CUSTOM')->exists())->toBeTrue();

    // 2. If PO number is already taken by an existing delivery, next delivery suggests -2
    Delivery::factory()->create([
        'purchase_order_id' => $po->id,
        'vendor_id' => $vendor->id,
        'code' => '47157099',
    ]);

    Livewire::test(DeliveryCreate::class, ['po_id' => $po->id])
        ->assertSet('code', '47157099-2');
});
