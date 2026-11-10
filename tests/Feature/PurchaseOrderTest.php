<?php

use App\Enums\DeliveryStatus;
use App\Enums\DocumentType;
use App\Enums\PoStatus;
use App\Enums\PoType;
use App\Enums\UserRole;
use App\Livewire\Items\Index as ItemsIndex;
use App\Livewire\PurchaseOrders\Create as PurchaseOrderCreate;
use App\Livewire\PurchaseOrders\Edit as PurchaseOrderEdit;
use App\Livewire\PurchaseOrders\Index as PurchaseOrdersIndex;
use App\Livewire\PurchaseOrders\Show as PurchaseOrderShow;
use App\Livewire\Vendors\Index as VendorsIndex;
use App\Models\Delivery;
use App\Models\Document;
use App\Models\Item;
use App\Models\PoItem;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('spv can view purchase order index and see all pos', function () {
    $spv = User::factory()->spv()->create();
    $vendor1 = Vendor::factory()->create();
    $vendor2 = Vendor::factory()->create();

    $po1 = PurchaseOrder::factory()->create(['vendor_id' => $vendor1->id, 'po_number' => 'PO-SPV-001']);
    $po2 = PurchaseOrder::factory()->create(['vendor_id' => $vendor2->id, 'po_number' => 'PO-SPV-002']);

    $this->actingAs($spv);

    $this->get(route('purchase-orders.index'))
        ->assertOk()
        ->assertSee('PO-SPV-001')
        ->assertSee('PO-SPV-002');
});

test('vendor only sees pos belonging to their vendor', function () {
    $vendorA = Vendor::factory()->create(['name' => 'Catering A']);
    $vendorB = Vendor::factory()->create(['name' => 'Catering B']);

    $vendorUserA = User::factory()->vendor($vendorA->id)->create();

    $poA = PurchaseOrder::factory()->create(['vendor_id' => $vendorA->id, 'po_number' => 'PO-A-1001']);
    $poB = PurchaseOrder::factory()->create(['vendor_id' => $vendorB->id, 'po_number' => 'PO-B-2002']);

    $this->actingAs($vendorUserA);

    $this->get(route('purchase-orders.index'))
        ->assertOk()
        ->assertSee('PO-A-1001')
        ->assertDontSee('PO-B-2002');

    // Vendor A cannot view PO B detail (query level global scope hides it resulting in 404 Not Found)
    $this->get(route('purchase-orders.show', $poB))
        ->assertNotFound();
});

test('vendor cannot access po creation page', function () {
    $vendor = Vendor::factory()->create();
    $vendorUser = User::factory()->vendor($vendor->id)->create();

    $this->actingAs($vendorUser);

    $this->get(route('purchase-orders.create'))
        ->assertForbidden();
});

test('spv can create purchase order with multiple items and upload document', function () {
    Storage::fake('local');

    $spv = User::factory()->spv()->create();
    $vendor = Vendor::factory()->create(['name' => 'CV Mitra Catering']);

    $item1 = Item::factory()->create(['name' => 'Snack Box Standard', 'unit' => 'box', 'default_price' => 15000]);
    $item2 = Item::factory()->create(['name' => 'Jasa Antar Mobil', 'unit' => 'au', 'default_price' => 25000]);

    $file = UploadedFile::fake()->create('po_mitra_4715.pdf', 500, 'application/pdf');

    $this->actingAs($spv);

    Livewire::test(PurchaseOrderCreate::class)
        ->set('po_number', '47157099')
        ->set('type', 'po')
        ->set('vendor_id', $vendor->id)
        ->set('po_date', '2026-11-03')
        ->set('notes', 'Alokasi pelatihan batch November')
        ->set('document_file', $file)
        ->set('items', [
            [
                'item_id' => $item1->id,
                'item_name' => $item1->name,
                'unit' => $item1->unit,
                'qty_ordered' => 50,
                'unit_price' => 15000,
                'subtotal' => 750000,
            ],
            [
                'item_id' => $item2->id,
                'item_name' => $item2->name,
                'unit' => $item2->unit,
                'qty_ordered' => 2,
                'unit_price' => 25000,
                'subtotal' => 50000,
            ],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $po = PurchaseOrder::where('po_number', '47157099')->first();
    expect($po)->not->toBeNull()
        ->and($po->vendor_id)->toBe($vendor->id)
        ->and($po->type)->toBe(PoType::PO)
        ->and($po->poItems)->toHaveCount(2)
        ->and($po->total_amount)->toBe(800000)
        ->and($po->total_qty_ordered)->toBe(52.0)
        ->and($po->documents)->toHaveCount(1)
        ->and($po->documents->first()->type)->toBe(DocumentType::PO);

    Storage::disk('local')->assertExists($po->documents->first()->path);
});

test('spv can edit po when no deliveries exist', function () {
    $spv = User::factory()->spv()->create();
    $vendor = Vendor::factory()->create();
    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id, 'po_number' => 'PO-ORIGINAL']);
    PoItem::factory()->create(['purchase_order_id' => $po->id, 'qty_ordered' => 10, 'unit_price' => 5000]);

    $this->actingAs($spv);

    $this->get(route('purchase-orders.edit', $po))->assertOk();

    Livewire::test(PurchaseOrderEdit::class, ['purchaseOrder' => $po])
        ->set('notes', 'Updated notes by SPV')
        ->call('save')
        ->assertHasNoErrors();

    expect($po->fresh()->notes)->toBe('Updated notes by SPV');
});

test('po cannot be edited after delivery is created', function () {
    $spv = User::factory()->spv()->create();
    $vendor = Vendor::factory()->create();
    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);
    $poItem = PoItem::factory()->create(['purchase_order_id' => $po->id]);

    Delivery::factory()->create([
        'purchase_order_id' => $po->id,
        'vendor_id' => $vendor->id,
        'status' => DeliveryStatus::SUBMITTED,
    ]);

    $this->actingAs($spv);

    $this->get(route('purchase-orders.edit', $po))
        ->assertForbidden();
});

test('superadmin can create and update vendors and items', function () {
    $admin = User::factory()->superadmin()->create();

    $this->actingAs($admin);

    // Test Vendor CRUD
    Livewire::test(VendorsIndex::class)
        ->set('name', 'Vendor Barokah')
        ->set('contact', '081299990000')
        ->set('address', 'Surabaya')
        ->call('save')
        ->assertHasNoErrors();

    expect(Vendor::where('name', 'Vendor Barokah')->exists())->toBeTrue();

    // Test Item CRUD
    Livewire::test(ItemsIndex::class)
        ->set('name', 'Kue Lumpur')
        ->set('unit', 'pc')
        ->set('itemType', 'goods')
        ->set('default_price', 3000)
        ->call('save')
        ->assertHasNoErrors();

    expect(Item::where('name', 'Kue Lumpur')->exists())->toBeTrue();
});

test('spv cannot create vendor or master item', function () {
    $spv = User::factory()->spv()->create();

    $this->actingAs($spv);

    Livewire::test(VendorsIndex::class)
        ->set('name', 'Vendor Terlarang')
        ->call('save')
        ->assertForbidden();

    Livewire::test(ItemsIndex::class)
        ->set('name', 'Item Terlarang')
        ->call('save')
        ->assertForbidden();
});

test('private documents cannot be downloaded by unauthorized vendors', function () {
    Storage::fake('local');

    $vendorA = Vendor::factory()->create();
    $vendorB = Vendor::factory()->create();

    $vendorUserA = User::factory()->vendor($vendorA->id)->create();
    $vendorUserB = User::factory()->vendor($vendorB->id)->create();

    $poA = PurchaseOrder::factory()->create(['vendor_id' => $vendorA->id]);

    $filePath = 'documents/po/secret_po_a.pdf';
    Storage::disk('local')->put($filePath, 'sample content');

    $doc = Document::create([
        'documentable_type' => PurchaseOrder::class,
        'documentable_id' => $poA->id,
        'type' => DocumentType::PO,
        'path' => $filePath,
        'original_name' => 'secret_po_a.pdf',
        'mime' => 'application/pdf',
        'size' => 1024,
        'uploaded_by' => $vendorUserA->id,
    ]);

    // Vendor A can download
    $this->actingAs($vendorUserA);
    $this->get(route('documents.show', $doc))->assertOk();

    // Vendor B cannot download (403 Forbidden)
    $this->actingAs($vendorUserB);
    $this->get(route('documents.show', $doc))->assertForbidden();
});

test('catalog selection auto-fills unit price from database and allows custom price override', function () {
    $spv = User::factory()->spv()->create();
    $vendor = Vendor::factory()->create();
    $catalogItem = Item::factory()->create([
        'name' => 'Snack Box',
        'unit' => 'box',
        'default_price' => 15000,
    ]);

    $this->actingAs($spv);

    // 1. Selecting catalog item auto-fills DB price, and overriding unit_price keeps custom value
    Livewire::test(PurchaseOrderCreate::class)
        ->set('po_number', 'PO-CUSTOM-PRICE-01')
        ->set('vendor_id', $vendor->id)
        ->set('po_date', '2026-11-10')
        ->set('items.0.item_id', $catalogItem->id)
        ->assertSet('items.0.item_name', 'Snack Box')
        ->assertSet('items.0.unit', 'box')
        ->assertSet('items.0.unit_price', 15000)
        ->set('items.0.qty_ordered', 1)
        ->set('items.0.unit_price', 16000)
        ->assertSet('items.0.subtotal', 16000)
        ->call('save')
        ->assertHasNoErrors();

    $po = PurchaseOrder::where('po_number', 'PO-CUSTOM-PRICE-01')->first();
    expect($po)->not->toBeNull()
        ->and($po->poItems->first()->unit_price)->toBe(16000)
        ->and($po->total_amount)->toBe(16000);

    // 2. Simultaneous batch update of item_id + custom unit_price (like deferred wire:model on save) must NOT revert price to DB default
    Livewire::test(PurchaseOrderCreate::class)
        ->set('po_number', 'PO-CUSTOM-PRICE-02')
        ->set('vendor_id', $vendor->id)
        ->set('po_date', '2026-11-10')
        ->set([
            'items.0.item_id' => $catalogItem->id,
            'items.0.item_name' => 'Snack Box Custom',
            'items.0.unit' => 'box',
            'items.0.qty_ordered' => 2,
            'items.0.unit_price' => 16000,
        ])
        ->call('save')
        ->assertHasNoErrors();

    $po2 = PurchaseOrder::where('po_number', 'PO-CUSTOM-PRICE-02')->first();
    expect($po2)->not->toBeNull()
        ->and($po2->poItems->first()->item_name)->toBe('Snack Box Custom')
        ->and($po2->poItems->first()->unit_price)->toBe(16000)
        ->and($po2->total_amount)->toBe(32000);
});
