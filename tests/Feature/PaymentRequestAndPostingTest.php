<?php

use App\Enums\InvoiceStatus;
use App\Livewire\PaymentRequests\Create as PaymentRequestCreate;
use App\Livewire\PaymentRequests\Index as PaymentRequestsIndex;
use App\Livewire\PaymentRequests\Show as PaymentRequestShow;
use App\Livewire\PaymentStages\Index as PaymentStagesIndex;
use App\Livewire\Postings\Create as PostingCreate;
use App\Livewire\Postings\Index as PostingsIndex;
use App\Livewire\Postings\Show as PostingShow;
use App\Models\Invoice;
use App\Models\PaymentRequest;
use App\Models\PaymentStage;
use App\Models\Posting;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('spv can create payment request bundling multiple approved invoices', function () {
    $spv = User::factory()->spv()->create();
    $vendor = Vendor::factory()->create();
    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);

    $stage1 = PaymentStage::factory()->create(['name' => 'Proses ACC', 'sort_order' => 1]);
    $stage2 = PaymentStage::factory()->create(['name' => 'Proses Finance', 'sort_order' => 2]);

    $invoice1 = Invoice::factory()->approved()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'invoice_number' => 'INV-001',
        'total_amount' => 150000,
    ]);

    $invoice2 = Invoice::factory()->approved()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'invoice_number' => 'INV-002',
        'total_amount' => 250000,
    ]);

    Livewire::actingAs($spv)
        ->test(PaymentRequestCreate::class)
        ->set('selected_invoice_ids', [$invoice1->id, $invoice2->id])
        ->set('current_stage_id', $stage1->id)
        ->set('notes', 'Pengajuan berkas catering periode 1-15 November')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $pr = PaymentRequest::latest('id')->first();
    expect($pr)->not->toBeNull()
        ->and($pr->number)->toContain('TC/PC/')
        ->and($pr->current_stage_id)->toBe($stage1->id)
        ->and($pr->invoices)->toHaveCount(2)
        ->and($pr->total_amount)->toBe(400000)
        ->and($pr->stageLogs)->toHaveCount(1)
        ->and($pr->stageLogs->first()->to_stage_id)->toBe($stage1->id);
});

test('spv can update payment request stage and stage log is recorded', function () {
    $spv = User::factory()->spv()->create();
    $stage1 = PaymentStage::factory()->create(['name' => 'Proses ACC', 'sort_order' => 1]);
    $stage2 = PaymentStage::factory()->create(['name' => 'Proses Finance', 'sort_order' => 2]);

    $pr = PaymentRequest::factory()->create([
        'current_stage_id' => $stage1->id,
    ]);

    Livewire::actingAs($spv)
        ->test(PaymentRequestShow::class, ['paymentRequest' => $pr])
        ->set('new_stage_id', $stage2->id)
        ->set('stage_notes', 'Berkas telah disetujui Pak SPV dan diserahkan ke Finance')
        ->call('updateStage')
        ->assertHasNoErrors();

    $pr->refresh();
    expect($pr->current_stage_id)->toBe($stage2->id)
        ->and($pr->stageLogs)->toHaveCount(1)
        ->and($pr->stageLogs->first()->from_stage_id)->toBe($stage1->id)
        ->and($pr->stageLogs->first()->to_stage_id)->toBe($stage2->id)
        ->and($pr->stageLogs->first()->notes)->toBe('Berkas telah disetujui Pak SPV dan diserahkan ke Finance');
});

test('spv can record sap posting document which transitions pr to final stage and marks invoices as paid', function () {
    Storage::fake('local');
    $spv = User::factory()->spv()->create();
    $vendor = Vendor::factory()->create();
    $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id]);

    $stageAcc = PaymentStage::factory()->create(['name' => 'Proses ACC', 'sort_order' => 1]);
    $stageFinal = PaymentStage::factory()->create(['name' => 'Posted SAP', 'sort_order' => 3, 'is_final' => true]);

    $inv1 = Invoice::factory()->approved()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'total_amount' => 500000,
    ]);
    $inv2 = Invoice::factory()->approved()->create([
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'total_amount' => 350000,
    ]);

    $pr = PaymentRequest::factory()->create([
        'current_stage_id' => $stageAcc->id,
    ]);
    $pr->invoices()->attach([$inv1->id, $inv2->id]);

    $file = UploadedFile::fake()->create('bukti_bayar_sap.pdf', 200, 'application/pdf');

    Livewire::actingAs($spv)
        ->test(PostingCreate::class)
        ->set('selected_pr_ids', [$pr->id])
        ->set('document_number', '5000098765')
        ->set('posting_date', '2026-11-03')
        ->set('total_paid', 850000)
        ->set('payment_proof', $file)
        ->set('notes', 'Pelunasan transfer Bank Mandiri')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $posting = Posting::where('document_number', '5000098765')->first();
    expect($posting)->not->toBeNull()
        ->and($posting->total_paid)->toBe(850000)
        ->and($posting->documents)->toHaveCount(1);

    $pr->refresh();
    expect($pr->posting_id)->toBe($posting->id)
        ->and($pr->current_stage_id)->toBe($stageFinal->id)
        ->and($pr->isPosted())->toBeTrue();

    // Invoices are updated to PAID
    $inv1->refresh();
    $inv2->refresh();
    expect($inv1->status)->toBe(InvoiceStatus::PAID)
        ->and($inv2->status)->toBe(InvoiceStatus::PAID);
});

test('cannot modify stage of already posted payment request', function () {
    $spv = User::factory()->spv()->create();
    $stage1 = PaymentStage::factory()->create(['sort_order' => 1]);
    $stageFinal = PaymentStage::factory()->create(['sort_order' => 2, 'is_final' => true]);

    $posting = Posting::factory()->create();
    $pr = PaymentRequest::factory()->create([
        'posting_id' => $posting->id,
        'current_stage_id' => $stageFinal->id,
    ]);

    Livewire::actingAs($spv)
        ->test(PaymentRequestShow::class, ['paymentRequest' => $pr])
        ->set('new_stage_id', $stage1->id)
        ->call('updateStage')
        ->assertForbidden();
});

test('vendor can view payment requests containing their invoices but not other vendors', function () {
    $vendorA = Vendor::factory()->create(['name' => 'Vendor A']);
    $vendorB = Vendor::factory()->create(['name' => 'Vendor B']);

    $userVendorA = User::factory()->vendor($vendorA->id)->create();

    $poA = PurchaseOrder::factory()->create(['vendor_id' => $vendorA->id]);
    $poB = PurchaseOrder::factory()->create(['vendor_id' => $vendorB->id]);

    $invA = Invoice::factory()->approved()->create(['vendor_id' => $vendorA->id, 'purchase_order_id' => $poA->id]);
    $invB = Invoice::factory()->approved()->create(['vendor_id' => $vendorB->id, 'purchase_order_id' => $poB->id]);

    $prA = PaymentRequest::factory()->create();
    $prA->invoices()->attach($invA->id);

    $prB = PaymentRequest::factory()->create();
    $prB->invoices()->attach($invB->id);

    // Vendor A can view PR A
    $responseA = $this->actingAs($userVendorA)->get(route('payment-requests.show', $prA));
    $responseA->assertOk();

    // Vendor A cannot view PR B
    $responseB = $this->actingAs($userVendorA)->get(route('payment-requests.show', $prB));
    $responseB->assertForbidden();
});

test('vendor cannot create payment request or sap posting', function () {
    $vendor = Vendor::factory()->create();
    $userVendor = User::factory()->vendor($vendor->id)->create();

    $this->actingAs($userVendor)
        ->get(route('payment-requests.create'))
        ->assertForbidden();

    $this->actingAs($userVendor)
        ->get(route('postings.create'))
        ->assertForbidden();
});

test('superadmin can create and update master payment stages', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(PaymentStagesIndex::class)
        ->call('create')
        ->set('name', 'Tahap Review Kadiv')
        ->set('sort_order', 5)
        ->set('is_final', false)
        ->call('save')
        ->assertHasNoErrors();

    $stage = PaymentStage::where('name', 'Tahap Review Kadiv')->first();
    expect($stage)->not->toBeNull()
        ->and($stage->sort_order)->toBe(5)
        ->and($stage->is_final)->toBeFalse();

    // Edit stage
    Livewire::actingAs($admin)
        ->test(PaymentStagesIndex::class)
        ->call('edit', $stage->id)
        ->set('name', 'Tahap Review Kadiv Approved')
        ->call('save')
        ->assertHasNoErrors();

    expect($stage->fresh()->name)->toBe('Tahap Review Kadiv Approved');
});

test('spv can upload supporting document to payment request', function () {
    Storage::fake('local');
    $spv = User::factory()->spv()->create();
    $pr = PaymentRequest::factory()->create();

    $file = UploadedFile::fake()->create('tanda_terima_berkas.pdf', 300, 'application/pdf');

    Livewire::actingAs($spv)
        ->test(PaymentRequestShow::class, ['paymentRequest' => $pr])
        ->set('document_file', $file)
        ->set('document_notes', 'Tanda terima berkas dari bagian Keuangan')
        ->call('uploadDocument')
        ->assertHasNoErrors();

    $pr->refresh();
    expect($pr->documents)->toHaveCount(1)
        ->and($pr->documents->first()->type)->toBe(\App\Enums\DocumentType::PAYMENT_REQUEST)
        ->and($pr->documents->first()->original_name)->toBe('tanda_terima_berkas.pdf');
});

