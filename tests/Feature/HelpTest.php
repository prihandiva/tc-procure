<?php

use App\Livewire\Help\Index as HelpIndex;
use App\Models\User;
use App\Models\Vendor;
use Livewire\Livewire;

test('guests are redirected to the login page from help', function () {
    $response = $this->get(route('help'));
    $response->assertRedirect(route('login'));
});

test('spv or tc user can view help page with procurement flow and tc guide', function () {
    $spv = User::factory()->spv()->create(['name' => 'Budi Supervisor TC']);

    $response = $this->actingAs($spv)->get(route('help'));
    $response->assertOk();
    $response->assertSee('Pusat Bantuan');

    Livewire::actingAs($spv)
        ->test(HelpIndex::class)
        ->assertSee('Siklus Tahapan Pengadaan Snack')
        ->assertSee('Penerbitan PO Kontrak')
        ->set('tab', 'tc')
        ->assertSee('Panduan Khusus Tim Training Center')
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
