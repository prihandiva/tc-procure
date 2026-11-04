# TC-Procure

**TC-Procure** adalah aplikasi web khusus untuk Supervisor (SPV) Training Center guna mengelola pengadaan snack dan katering luar (*external catering*), penerimaan surat jalan harian (*delivery order* / DO), penagihan bertahap (*invoice*), pencocokan 3 arah (*3-way matching*), pengajuan berkas pembayaran (*payment request*), hingga pencatatan pelunasan dokumen akuntansi SAP.

Aplikasi ini dilengkapi portal rekanan khusus (*vendor portal*) untuk pihak katering (seperti **CV Yulia Catering**) guna menginput surat jalan pengiriman, mengunggah pindaian fisik, menggabungkan beberapa surat jalan menjadi tagihan resmi, serta memantau sisa kuota PO dan status pembayaran secara transparan dan terisolasi.

---

## Daftar Isi
1. [Fitur Utama](#fitur-utama)
2. [Arsitektur & Keamanan](#arsitektur--keamanan)
3. [Teknologi](#teknologi)
4. [Prasyarat Sistem](#prasyarat-sistem)
5. [Panduan Instalasi (Laragon / Windows)](#panduan-instalasi-laragon--windows)
6. [Tabel Kredensial Akun Default](#tabel-kredensial-akun-default)
7. [Pengujian Otomatis (Automated Testing)](#pengujian-otomatis-automated-testing)
8. [Struktur Folder Utama](#struktur-folder-utama)
9. [Alur Bisnis Aplikasi](#alur-bisnis-aplikasi)

---

## Fitur Utama

### 1. Manajemen Master Data & Purchase Order (PO)
- **Master Vendor & Katalog Item**: Katalog snack basah, snack kering, kopi, creamer, air mineral, permen, snack box, nasi box, hingga ongkos kirim standar.
- **Penerbitan PO**: Mendukung PO resmi dan Non-PO, pemilihan vendor rekanan, kuantitas, harga snapshot kontrak, batas waktu berlaku, dan unggah pindaian fisik PO.
- **Proteksi Integritas PO**: PO yang telah memiliki transaksi pengiriman terkunci dari perubahan harga atau kuantitas secara sepihak.
- **7 Kartu Metrik PO**: Visualisasi instan untuk *Dipesan (Qty & Rp)*, *Terkirim (Qty & Rp)*, *Sisa Kuota (Qty & Rp)*, *Sudah Ditagih (Rp)*, *Belum Ditagih (Rp)*, *Sudah Dibayar (Rp)*, dan *Belum Dibayar (Rp)*.

### 2. Pengiriman Harian (Delivery Order / DO)
- **Input Mandiri oleh Vendor**: Vendor menginput kuantitas kirim (`qty_sent`), nomor surat jalan fisik, nama pelatihan/agenda, dan foto/scan surat jalan.
- **Kode Serial Otomatis**: Format serial anti-tabrakan `DLV-YYYY-XXXXX` (contoh: `DLV-2026-00001`).
- **Verifikasi Fisik oleh SPV**: Kuantitas kirim tidak memotong kuota PO sampai dikonfirmasi oleh SPV berdasarkan barang yang benar-benar diterima (`qty_received`).
- **Pencatatan Selisih (Discrepancy Tracking)**: Sistem otomatis mendeteksi dan mencatat barang rusak atau kurang kirim. Jika ditolak (`rejected`), alasan penolakan wajib dicatat dan kuota PO tidak terpotong.
- **Perlindungan Anti Over-Delivery**: Validasi kuantitas kirim terhadap sisa PO dengan penguncian baris basis data (`lockForUpdate()`) guna mencegah pengiriman paralel melampaui sisa kuota.

### 3. Tagihan (Invoice) & 3-Way Match
- **Multi-Delivery Bundling**: Vendor dapat menggabungkan beberapa surat jalan terkonfirmasi dari tanggal berbeda dalam satu invoice untuk satu PO acuan.
- **Verifikasi 3-Way Match Otomatis**:
  1. Keunikan nomor faktur vendor (mencegah duplikasi klaim).
  2. Kesesuaian harga satuan dengan harga kontrak terkunci PO.
  3. Kesesuaian kuantitas tagihan dengan kuantitas fisik yang diverifikasi SPV (`qty_received`).
- **Anti Double-Billing**: Setiap baris pengiriman memiliki flag `is_invoiced`. Penguncian atomik menjamin surat jalan yang sama tidak pernah dapat ditagihkan lebih dari satu kali.
- **Persetujuan & Penolakan**: Tagihan disetujui SPV untuk lanjut ke tahap pembayaran. Jika ditolak, status surat jalan otomatis dilepaskan agar vendor dapat merevisi tagihannya.

### 4. Pengajuan Berkas Pembayaran (Payment Request)
- **Pengelompokan Tagihan**: SPV menggabungkan invoice-invoice yang telah disetujui menjadi satu berkas pengajuan.
- **Format Penomoran Standar**: Pola serial resmi `TC/PC/{bulan-romawi}/{tahun}/{nomor-urut}` (contoh: `TC/PC/XI/2026/001`).
- **Pelacakan Alur Tahapan (Stage Timeline)**: Status pergerakan berkas (*Proses ACC* $\to$ *Proses Finance* $\to$ *Posted SAP*) dicatat dalam log riwayat lengkap dengan waktu, nama PIC pengubah, dan catatan.
- **Dokumen Lampiran**: Unggah tanda terima berkas dan surat pengantar internal.

### 5. Pencatatan Dokumen Posting SAP & Pelunasan
- **Pencatatan Dokumen Akuntansi**: SPV mencatat nomor dokumen SAP (misal: `5000012345`), tanggal posting, nominal pembayaran riil, dan mengunggah pindaian bukti transfer kasir.
- **Pelunasan Otomatis**: Dalam transaksi atomik, seluruh invoice yang tergabung di dalam Payment Request otomatis berubah status menjadi **LUNAS (`PAID`)**, tahapan PR bergerak ke status akhir, dan berkas terkunci dari modifikasi lanjutan.

### 6. Dashboard Berbasis Peran
- **Dashboard SPV**: Peringatan aksi kerja hari ini (DO menunggu konfirmasi, invoice menunggu 3-way match, PR aktif), metrik finansial global, tabel rekapitulasi kuota dan saldo per rekanan, serta umpan transaksi terkini.
- **Dashboard Vendor**: Pengingat otomatis item surat jalan yang telah diverifikasi SPV dan siap ditagihkan, sisa kuota PO aktif, bilah kemajuan pemenuhan (*progress bar*), dan status pelunasan.

---

## Arsitektur & Keamanan

1. **Isolasi Data Rekanan Multi-Tenant (Tenant-Level Data Isolation)**:
   - Diterapkan menggunakan Eloquent Global Scope (`VendorScope`) dan trait `BelongsToVendor`.
   - Pengguna dengan peran `vendor` secara otomatis hanya dapat melihat dan memanipulasi data PO, DO, dan Invoice miliknya sendiri. Akses URL langsung ke ID vendor lain menghasilkan respons `404 Not Found` (mencegah kerentanan IDOR).
2. **Penyimpanan Berkas Terlindungi (Private Storage)**:
   - Seluruh dokumen (PO, surat jalan, invoice fisik, bukti bayar SAP) disimpan di direktori privat (`storage/app/private/documents/`), bukan di folder publik `public/storage/`.
   - Pengunduhan dilayani melalui controller streaming (`DocumentController`) yang memverifikasi kepemilikan via `DocumentPolicy`.
3. **Pencegahan Kondisi Lomba (Race Condition Prevention)**:
   - Seluruh operasi transaksi finansial dan kuota (pengurangan sisa PO, penandaan `is_invoiced`, pembuatan posting pelunasan) dibungkus dalam `DB::transaction()` dengan `lockForUpdate()` pada level baris basis data.
4. **Jejak Audit Menyeluruh (Audit Trail)**:
   - Tabel `audit_logs` merekam setiap tindakan kritis (pembuatan PO, konfirmasi/penolakan DO, persetujuan/penolakan invoice, pergeseran tahap PR, posting SAP) lengkap dengan user ID, IP address, user agent, data sebelum (`old_values`), dan data sesudah (`new_values`).
5. **Integritas Moneter & Presisi Satuan**:
   - Seluruh nilai uang disimpan dalam satuan Rupiah bulat positif menggunakan kolom `unsignedBigInteger` tanpa koma/float.
   - Kuantitas menggunakan `decimal(12, 2)` untuk mendukung satuan presisi desimal (seperti `1.50 kg keripik`).

---

## Teknologi

- **Backend Framework**: [Laravel 13](https://laravel.com)
- **Bahasa Pemrograman**: PHP 8.4.9 (ZTS x64)
- **Basis Data**: MySQL 8.0.30 (Dedicated user `tcprocure`)
- **Frontend Reactive**: [Livewire v4](https://livewire.laravel.com) + [Alpine.js](https://alpinejs.dev)
- **Styling & Komponen**: [Tailwind CSS v4](https://tailwindcss.com) + [Flux UI](https://fluxui.dev)
- **Testing Framework**: [Pest v4](https://pestphp.com)
- **Asset Bundler**: [Vite](https://vitejs.dev)

---

## Prasyarat Sistem

- Windows 10/11 atau Linux / macOS
- Laragon (atau XAMPP / Native Environment) dengan:
  - PHP >= 8.2 (disarankan PHP 8.4)
  - Ekstensi PHP: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`
  - MySQL >= 8.0
- Composer >= 2.6
- Node.js >= 20.x (disarankan Node.js v22 LTS)

---

## Panduan Instalasi (Laragon / Windows)

### 1. Masuk ke Direktori Root Web
Buka terminal (PowerShell atau Git Bash) di folder web Laragon:
```bash
cd D:\laragon\www\tcprocure
```

### 2. Konfigurasi Database MySQL Khusus
Aplikasi menggunakan database terdedikasi `tc_procure` dengan akun pengguna `tcprocure` agar aman berdampingan dengan aplikasi lain.

Jalankan perintah SQL berikut di MySQL Client / HeidiSQL / phpMyAdmin:
```sql
CREATE DATABASE IF NOT EXISTS tc_procure CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'tcprocure'@'localhost' IDENTIFIED BY 'tcprocure_pass_2026';
GRANT ALL PRIVILEGES ON tc_procure.* TO 'tcprocure'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Konfigurasi Environment (`.env`)
Salin file template environment jika belum ada:
```bash
copy .env.example .env
php artisan key:generate
```

Pastikan variabel database pada file `.env` terkonfigurasi sebagai berikut:
```ini
APP_NAME="TC-Procure"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://tcprocure.test
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tc_procure
DB_USERNAME=tcprocure
DB_PASSWORD=tcprocure_pass_2026
```

### 4. Instalasi Dependensi PHP & JavaScript
```bash
composer install
npm install
```

### 5. Menjalankan Migrasi & Seeding Data Awal
Jalankan migrasi tabel dan seeding master data:
```bash
php artisan migrate --seed
```

### 6. Kompilasi Aset Frontend
Kompilasi aset Tailwind CSS dan Flux UI:
```bash
# Untuk mode produksi:
npm run build

# Atau untuk mode pengembangan (hot-reloading):
npm run dev
```

### 7. Menjalankan Server Lokal
Jika menggunakan Laragon, virtual host `http://tcprocure.test` akan otomatis terdeteksi. Atau jalankan server bawaan artisan:
```bash
php artisan serve
```
Akses aplikasi melalui peramban di `http://127.0.0.1:8000` atau `http://tcprocure.test`.

---

## Tabel Kredensial Akun Default

Setelah menjalankan seeder bawaan (`php artisan db:seed`), akun demo berikut siap digunakan langsung cukup dengan **username** (atau email) dan password:

| Peran (Role) | Username Cepat | Alamat Email | Password | Keterangan Akses |
| :--- | :--- | :--- | :--- | :--- |
| **Super Administrator** | `superadmin` | `admin@tcprocure.test` | `password` | Akses penuh seluruh sistem & konfigurasi master data |
| **Supervisor (SPV)** | `tc` | `spv@tcprocure.test` | `password` | Penerbitan PO, konfirmasi fisik DO, verifikasi 3-way match, PR, posting SAP |
| **Vendor** | `vendor` | `vendor@tcprocure.test` | `password` | Portal rekanan CV Yulia Catering, input DO, penagihan invoice, cek saldo |

> **Catatan**: Pada form login, Anda dapat langsung mengetikkan username (`superadmin`, `tc`, atau `vendor`) pada input **"Username atau Email"**. Password untuk semua akun adalah `password`.

---

## Pengujian Otomatis (Automated Testing)

Aplikasi dibangun menggunakan metodologi *test-driven hardening* dengan framework pengujian Pest v4. Pengujian mencakup pengujian relasi model, aturan peran, siklus hidup PO, pengiriman, penagihan, pembayaran, dasbor, dan pencegahan kerentanan keamanan lintas vendor.

Jalankan seluruh test suite dengan perintah:
```bash
vendor/bin/pest
```

### Ringkasan Hasil Pengujian Terakhir:
```text
   PASS  Tests\Feature\SecurityHardeningTest (9 tests, 21 assertions)
   PASS  Tests\Feature\DashboardTest (4 tests, 14 assertions)
   PASS  Tests\Feature\PaymentRequestAndPostingTest (7 tests, 36 assertions)
   PASS  Tests\Feature\InvoiceTest (9 tests, 32 assertions)
   PASS  Tests\Feature\DeliveryTest (8 tests, 30 assertions)
   PASS  Tests\Feature\PurchaseOrderTest (8 tests, 34 assertions)
   PASS  Tests\Feature\ModelRelationTest (15 tests, 78 assertions)
   PASS  Tests\Feature\UserRoleTest (5 tests, 9 assertions)
   PASS  Tests\Feature\Auth\AuthenticationTest (5 tests, 12 assertions)
   ...
   Tests:    87 passed (295 assertions)
   Duration: 17.17s
```

---

## Struktur Folder Utama

```text
tcprocure/
├── app/
│   ├── Enums/                     # UserRole, PoType, PoStatus, DeliveryStatus, InvoiceStatus, ItemType, DocumentType
│   ├── Http/
│   │   └── Controllers/
│   │       └── DocumentController.php  # Streaming berkas terlindungi dari private storage
│   ├── Livewire/                  # Komponen reaktif Livewire v4
│   │   ├── Dashboard/             # Dashboard.php (Multi-role dynamic view)
│   │   ├── Deliveries/            # Index.php, Create.php, Show.php
│   │   ├── Invoices/              # Index.php, Create.php, Show.php
│   │   ├── Items/                 # Index.php (Katalog item & harga)
│   │   ├── PaymentRequests/       # Index.php, Create.php, Show.php
│   │   ├── PaymentStages/         # Index.php (Konfigurasi tahapan pengajuan)
│   │   ├── Postings/              # Index.php, Create.php, Show.php
│   │   ├── PurchaseOrders/        # Index.php, Create.php, Edit.php, Show.php
│   │   └── Vendors/               # Index.php (Master rekanan katering)
│   ├── Models/                    # Eloquent Models (15 tabel relasional)
│   ├── Policies/                  # Otorisasi hak akses (PurchaseOrder, Delivery, Invoice, Document, dll)
│   └── Traits/
│       ├── BelongsToVendor.php    # Scoping data otomatis ke vendor pengguna
│       └── HasAuditTrail.php      # Pencatatan otomatis created_by / updated_by
├── database/
│   ├── factories/                 # Model factories untuk testing
│   ├── migrations/                # 15 file migrasi database
│   └── seeders/                   # DatabaseSeeder, MasterDataSeeder
├── docs/
│   └── ASSUMPTIONS.md             # Dokumentasi keputusan arsitektur dan bisnis (Fase 0 - Fase 6)
├── resources/
│   └── views/
│       ├── layouts/               # Layouts app & sidebar Flux UI
│       └── livewire/              # Template Blade untuk setiap komponen Livewire
├── storage/
│   └── app/
│       └── private/               # Direktori privat dokumen fisik (terlindungi)
│           └── documents/         # po/, deliveries/, invoices/, postings/, payment_requests/
└── tests/
    └── Feature/                   # Test suite komprehensif Pest (87 tests)
```

---

## Alur Bisnis Aplikasi

```text
[SPV: Terbitkan PO]
         │
         ▼
[Vendor: Terima PO & Kirim Barang Harian (Input DO)]
         │
         ▼
[SPV: Terima Fisik & Konfirmasi DO (Cek Rusak / Kurang)]
         │ (Kuantitas Terverifikasi Mengurangi Sisa Kuota PO)
         ▼
[Vendor: Gabungkan Beberapa DO Menjadi 1 Invoice Tagihan]
         │
         ▼
[Sistem & SPV: 3-Way Match Verification (PO - DO - Invoice)]
         │
         ▼
[SPV: Gabungkan Invoice Disetujui Menjadi Payment Request (PR)]
         │ (Format Serial: TC/PC/XI/2026/001)
         ▼
[SPV: Alur Berkas Melalui Tahapan (ACC -> Finance)]
         │
         ▼
[SPV: Catat Nomor Dokumen Posting SAP & Upload Bukti Bayar]
         │
         ▼
[Sistem: Invoice Berstatus LUNAS (PAID) & Berkas Selesai]
```

---

## Lisensi & Hak Cipta
Aplikasi ini dikembangkan untuk kebutuhan operasional internal Training Center.
Hak Cipta © 2026 TC-Procure. Seluruh hak cipta dilindungi undang-undang.

