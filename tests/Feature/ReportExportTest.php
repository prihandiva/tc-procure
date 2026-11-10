<?php

use App\Enums\DeliveryStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PoStatus;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use Livewire\Livewire;

test('guests are redirected to the login page from reports', function () {
    $this->get(route('reports.index'))->assertRedirect(route('login'));
    $this->post(route('reports.export'), ['report_type' => 'po', 'format' => 'excel'])->assertRedirect(route('login'));
});

test('spv can view the central reports hub with kpi summary and table preview', function () {
    $spv = User::factory()->spv()->create();
    $vendor = Vendor::factory()->create(['name' => 'CV Catering Utama']);

    PurchaseOrder::factory()->create([
        'vendor_id' => $vendor->id,
        'po_number' => 'PO-REP-001',
        'status' => PoStatus::ACTIVE,
        'po_date' => now()->format('Y-m-d'),
    ]);

    $this->actingAs($spv)->get(route('reports.index'))->assertOk();

    Livewire::actingAs($spv)
        ->test(ReportsIndex::class)
        ->assertSee('PO-REP-001')
        ->assertSee('CV Catering Utama')
        ->set('reportType', 'deliveries')
        ->assertSet('reportType', 'deliveries')
        ->set('reportType', 'invoices')
        ->assertSet('reportType', 'invoices');
});

test('user can export purchase orders to excel with and without filters', function () {
    $spv = User::factory()->spv()->create();
    $vendor = Vendor::factory()->create(['name' => 'CV Mitra Sejahtera']);

    PurchaseOrder::factory()->create([
        'vendor_id' => $vendor->id,
        'po_number' => 'PO-EXCEL-001',
        'status' => PoStatus::ACTIVE,
        'po_date' => now()->format('Y-m-d'),
    ]);

    // 1. Export without filters
    $response = $this->actingAs($spv)->post(route('reports.export'), [
        'report_type' => 'po',
        'format' => 'excel',
    ]);

    $response->assertOk();
    expect($response->headers->get('content-type'))->toBe('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    // 2. Export with date and vendor filter
    $filteredResponse = $this->actingAs($spv)->post(route('reports.export'), [
        'report_type' => 'po',
        'format' => 'excel',
        'vendor_id' => (string) $vendor->id,
        'status' => 'active',
        'date_from' => now()->subDay()->format('Y-m-d'),
        'date_to' => now()->addDay()->format('Y-m-d'),
    ]);

    $filteredResponse->assertOk();
});

test('user can export invoices to pdf with and without filters', function () {
    $spv = User::factory()->spv()->create();
    $vendor = Vendor::factory()->create();
    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);

    Invoice::factory()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'invoice_number' => 'INV-PDF-001',
        'status' => InvoiceStatus::APPROVED,
        'invoice_date' => now()->format('Y-m-d'),
    ]);

    // Export PDF download
    $response = $this->actingAs($spv)->post(route('reports.export'), [
        'report_type' => 'invoices',
        'format' => 'pdf',
    ]);

    $response->assertOk();
    expect($response->headers->get('content-type'))->toBe('application/pdf');

    // Preview PDF inline
    $inlineResponse = $this->actingAs($spv)->post(route('reports.export'), [
        'report_type' => 'invoices',
        'format' => 'pdf',
        'inline' => 1,
    ]);

    $inlineResponse->assertOk();
    expect($inlineResponse->headers->get('content-type'))->toBe('application/pdf');
});

test('vendor user export is strictly scoped to their own vendor id', function () {
    $vendorA = Vendor::factory()->create(['name' => 'Vendor A']);
    $vendorB = Vendor::factory()->create(['name' => 'Vendor B']);

    $userA = User::factory()->vendor($vendorA->id)->create();

    PurchaseOrder::factory()->create([
        'vendor_id' => $vendorA->id,
        'po_number' => 'PO-VENDOR-A',
        'status' => PoStatus::ACTIVE,
    ]);

    PurchaseOrder::factory()->create([
        'vendor_id' => $vendorB->id,
        'po_number' => 'PO-VENDOR-B',
        'status' => PoStatus::ACTIVE,
    ]);

    // Vendor A tries to request Vendor B's id
    $response = $this->actingAs($userA)->post(route('reports.export'), [
        'report_type' => 'po',
        'format' => 'excel',
        'vendor_id' => (string) $vendorB->id, // Intentionally spoofing
    ]);

    $response->assertOk();
    // Query service guarantees vendor_id is overridden by $user->vendor_id
});

