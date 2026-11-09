<?php

use App\Livewire\Help\Index as HelpIndex;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use Livewire\Livewire;

test('guests are redirected to the login page from help', function () {
    $response = $this->get(route('help'));
    $response->assertRedirect(route('login'));
});

test('spv or tc user can view help page with procurement flow, exception handling, and tc guide', function () {
    $spv = User::factory()->spv()->create(['name' => 'Budi Anggota Tim TC']);

    $response = $this->actingAs($spv)->get(route('help'));
    $response->assertOk();
    $response->assertSee('Pusat Bantuan');

    Livewire::actingAs($spv)
        ->test(HelpIndex::class)
        ->assertSee('Siklus Tahapan Pengadaan Snack')
        ->assertSee('Penerbitan PO Kontrak')
        ->assertSee('Standing Contract')
        ->assertSee('Prosedur Penanganan Skenario Penyimpangan')
        ->assertSee('Toleransi Selisih Pembulatan')
        ->assertSee('Sistem Jatuh Tempo Pembayaran')
        ->set('tab', 'tc')
        ->assertSee('Panduan Khusus Tim TC')
        ->assertSee('Pemeriksaan Otomatis 3-Way Match');
});

test('vendor user can view help page with vendor guide and camera instructions', function () {
    $vendor = Vendor::factory()->create(['name' => 'CV Berkah Catering']);
    $vendorUser = User::factory()->vendor($vendor->id)->create(['name' => 'Siti Rekanan Vendor']);

    $response = $this->actingAs($vendorUser)->get(route('help'));
    $response->assertOk();

    Livewire::actingAs($vendorUser)
        ->test(HelpIndex::class)
        ->assertSet('tab', 'vendor')
        ->assertSee('Panduan Operasional untuk Rekanan Vendor')
        ->assertSee('Buka Kamera (Foto Fisik)')
        ->assertSee('Syarat Mutlak Tagihan')
        ->set('tab', 'faq')
        ->assertSee('Pertanyaan yang Sering Diajukan');
});

test('invoice model supports due date calculation and overdue detection', function () {
    $vendor = Vendor::factory()->create();
    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);

    // Active invoice created 20 days ago (past the 14-day TOP)
    $overdueInvoice = Invoice::factory()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'invoice_date' => now()->subDays(20),
        'status' => \App\Enums\InvoiceStatus::SUBMITTED,
    ]);

    expect($overdueInvoice->due_date)->not->toBeNull()
        ->and($overdueInvoice->isOverdue())->toBeTrue()
        ->and($overdueInvoice->days_overdue)->toBeGreaterThanOrEqual(5);

    // Recent invoice created today
    $recentInvoice = Invoice::factory()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'invoice_date' => now(),
        'status' => \App\Enums\InvoiceStatus::SUBMITTED,
    ]);

    expect($recentInvoice->isOverdue())->toBeFalse()
        ->and($recentInvoice->days_overdue)->toBe(0);
});
