<?php

use App\Enums\DeliveryStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PoStatus;
use App\Livewire\Dashboard\Dashboard;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Invoice;
use App\Models\PoItem;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('spv sees global procurement dashboard with pending actions and vendor summary', function () {
    $spv = User::factory()->spv()->create();
    $vendor = Vendor::factory()->create(['name' => 'CV Yulia Catering']);

    // Create an active PO
    $po = PurchaseOrder::factory()->create([
        'vendor_id' => $vendor->id,
        'po_number' => 'PO-SPV-001',
        'status' => PoStatus::ACTIVE,
    ]);

    // Create a pending delivery
    Delivery::factory()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'code' => 'DLV-2026-00001',
        'status' => DeliveryStatus::SUBMITTED,
    ]);

    // Create a pending invoice
    Invoice::factory()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'invoice_number' => 'INV-SPV-001',
        'status' => InvoiceStatus::SUBMITTED,
    ]);

    Livewire::actingAs($spv)
        ->test(Dashboard::class)
        ->assertSee('Dashboard Pengadaan Training Center')
        ->assertSee('DO Menunggu Konfirmasi')
        ->assertSee('Invoice Menunggu 3-Way Match')
        ->assertSee('CV Yulia Catering')
        ->assertSee('PO-SPV-001');
});

test('vendor sees dedicated vendor dashboard with active POs and uninvoiced deliveries alert', function () {
    $vendor = Vendor::factory()->create(['name' => 'CV Berkah Snack']);
    $vendorUser = User::factory()->vendor($vendor->id)->create();

    $po = PurchaseOrder::factory()->create([
        'vendor_id' => $vendor->id,
        'po_number' => 'PO-BERKAH-001',
        'status' => PoStatus::ACTIVE,
    ]);

    $poItem = PoItem::factory()->create([
        'purchase_order_id' => $po->id,
        'item_name' => 'Snack Box Spesial',
        'unit_price' => 15000,
    ]);

    // Confirmed delivery with uninvoiced item
    $delivery = Delivery::factory()->confirmed()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'code' => 'DLV-2026-00002',
    ]);

    DeliveryItem::factory()->create([
        'delivery_id' => $delivery->id,
        'po_item_id' => $poItem->id,
        'qty_sent' => 10.00,
        'qty_received' => 10.00,
        'is_invoiced' => false,
    ]);

    Livewire::actingAs($vendorUser)
        ->test(Dashboard::class)
        ->assertSee('Portal Rekanan: CV Berkah Snack')
        ->assertSee('Pengiriman Anda Telah Diterima SPV dan Siap Ditagihkan!')
        ->assertSee('PO-BERKAH-001')
        ->assertSee('DLV-2026-00002');
});

test('vendor dashboard does not display other vendors data', function () {
    $vendorA = Vendor::factory()->create(['name' => 'Vendor Alpha']);
    $vendorB = Vendor::factory()->create(['name' => 'Vendor Beta']);

    $userVendorA = User::factory()->vendor($vendorA->id)->create();

    $poB = PurchaseOrder::factory()->create([
        'vendor_id' => $vendorB->id,
        'po_number' => 'PO-SECRET-BETA-999',
        'status' => PoStatus::ACTIVE,
    ]);

    Livewire::actingAs($userVendorA)
        ->test(Dashboard::class)
        ->assertSee('Portal Rekanan: Vendor Alpha')
        ->assertDontSee('PO-SECRET-BETA-999')
        ->assertDontSee('Vendor Beta');
});

test('spv dashboard correctly displays delivered value when delivery is confirmed', function () {
    $spv = User::factory()->spv()->create();
    $vendor = Vendor::factory()->create(['name' => 'CV Yulia Catering']);

    $po = PurchaseOrder::factory()->create([
        'vendor_id' => $vendor->id,
        'status' => PoStatus::ACTIVE,
    ]);

    $poItem = PoItem::factory()->create([
        'purchase_order_id' => $po->id,
        'qty_ordered' => 100,
        'unit_price' => 10000,
    ]);

    $delivery = Delivery::factory()->confirmed()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
    ]);

    DeliveryItem::factory()->create([
        'delivery_id' => $delivery->id,
        'po_item_id' => $poItem->id,
        'qty_sent' => 40,
        'qty_received' => 40,
    ]);

    Livewire::actingAs($spv)
        ->test(Dashboard::class)
        ->assertSee('Rp 400.000');
});
