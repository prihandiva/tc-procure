# Asumsi & Keputusan Desain: TC-Procure

Dokumen ini mencatat keputusan arsitektural dan asumsi bisnis yang diambil selama implementasi aplikasi TC-Procure, sesuai pedoman pada `prompt-tc-procure.md`.

---

## 1. Lingkungan & Stack (Fase 0)
- **Framework**: Laravel 13 (v13.39.0) berjalan di atas PHP 8.4.9 (ZTS x64, Laragon).
- **Database**: MySQL 8.0.30 dengan database khusus `tc_procure`, charset `utf8mb4`, collation `utf8mb4_unicode_ci`.
- **Database User**: User dedicated `tcprocure`@`localhost` (bukan user `root`), hak akses terbatas pada database `tc_procure` sehingga aman berdampingan dengan database lain di server yang sama.
- **Node.js & Tooling**: Node.js v22.23.4 (LTS) dipasang untuk mendukung Vite & Vite-Plus bundling pipeline dengan dukungan modern node features (`node:util.styleText`).
- **Frontend**: Blade + Livewire v4 + Tailwind CSS (Starter Kit resmi).
- **Testing**: Pest v4 (`pestphp/pest` ^4.7 + `pest-plugin-laravel` ^4.1).
- **Lokalisasi**: Zona waktu `Asia/Jakarta`, locale `id`, fallback `id`, faker `id_ID`.

---

## 2. Model Data & Tipe Kolom
- **Mata Uang**: Disimpan sebagai bilangan bulat positif Rupiah (`unsignedBigInteger`) tanpa desimal sen, untuk integritas kalkulasi akuntansi.
- **Kuantitas (Qty)**: Menggunakan tipe `decimal(12, 2)` untuk mendukung satuan non-bulat seperti `kg` (misal 1.50 kg keripik) serta satuan diskrit (`pc`, `box`, `pak`).
- **Audit Trails & Soft Deletes**: Semua entitas transaksi (`purchase_orders`, `po_items`, `deliveries`, `delivery_items`, `invoices`, `invoice_lines`, `payment_requests`, `postings`) menggunakan `softDeletes` dan field `created_by` / `updated_by`. Tidak ada hard delete untuk data transaksi.
- **Penyimpanan Dokumen**: File disimpan pada disk privat (`storage/app/private/documents`), tidak dapat diakses langsung lewat web root. Pengunduhan wajib melalui endpoint terproteksi Policy.

---

## 3. Autentikasi, Role & Otorisasi
- **Tiga Peran Pengguna**:
  1. `superadmin`: Manajemen master data (user, vendor, item, payment stages), audit trail, dan monitoring menyeluruh.
  2. `spv`: Pembuatan PO, verifikasi penerimaan (GR/DO), verifikasi 3-way match invoice, pembuatan payment request dan posting.
  3. `vendor`: Penginputan pengiriman harian (DO), pembuatan tagihan/invoice, pemantauan status PO & pembayaran.
- **Isolasi Data Vendor**: Pengguna dengan role `vendor` wajib memiliki `vendor_id`. Filter data vendor diterapkan di level query (Eloquent Global Scope & Laravel Policy), bukan sekadar penyembunyian antarmuka.

---

## 4. Alur Pembayaran & Rekonsiliasi
- **Payment Request**: Menggunakan nomor unik berformat internal departemen (contoh: `TC/PC/10-X/2025/509`).
- **Payment Stages**: Dikelola secara dinamis melalui tabel `payment_stages`. Tahap awal bawaan:
  1. *Pengajuan Baru* (order: 1)
  2. *Proses ACC* (order: 2)
  3. *Proses Finance* (order: 3)
  4. *Selesai / Posted* (order: 4, `is_final = true`)
- **Posting**: Merefleksikan nomor dokumen akuntansi (seperti nomor dokumen SAP `500007...`), tanggal posting, dan nilai pembayaran riil. Posting otomatis mengubah status seluruh invoice yang terkait menjadi `paid`.


---

## 5. Implementasi Skema & Model (Fase 1)
- **Tabel Basis Data**: Telah dibuat 15 tabel migrasi lengkap:
  `vendors`, `items`, `purchase_orders`, `po_items`, `deliveries`, `delivery_items`, `invoices`, `invoice_lines`, `payment_stages`, `postings`, `payment_requests`, `payment_request_invoices`, `payment_request_stage_logs`, `documents`, `audit_logs`.
- **Enums**:
  - `UserRole`: `superadmin`, `spv`, `vendor`
  - `PoType`: `po`, `non_po`
  - `PoStatus`: `draft`, `active`, `completed`, `cancelled`
  - `DeliveryStatus`: `submitted`, `confirmed`, `rejected`
  - `InvoiceStatus`: `draft`, `submitted`, `approved`, `rejected`, `in_payment`, `paid`, `void`
  - `ItemType`: `goods`, `service`
  - `DocumentType`: `po`, `delivery_order`, `invoice`, `payment_proof`, `other`
- **Global Scope `VendorScope`**: Diterapkan pada model `PurchaseOrder`, `Delivery`, dan `Invoice` melalui trait `BelongsToVendor`. Jika user yang login adalah `vendor`, query otomatis dibatasi pada `vendor_id` miliknya. Superadmin dan SPV dapat melihat seluruh data.
- **Audit Trail Trait**: Trait `HasAuditTrail` mengotomatisasi pengisian `created_by` dan `updated_by` dari session pengguna aktif.
- **Katalog Master Item**: 18 item standar disinkronkan dari lembar `daftar harga` (termasuk Snack Basah, Snack Kering, Keripik, Saos, Aneka Kopi, Buavita, Permen, Creamer, Air Mineral, Snack Box, Nasi Box, dan Jasa Ongkir).

---

## 6. Implementasi Master Data & Purchase Order (Fase 2)
- **Komponen Master Vendor & Item**:
  - `App\Livewire\Vendors\Index`: Manajemen data vendor untuk Superadmin dengan pencarian, filter status, modal form tambah/ubah, dan tombol switch status aktif.
  - `App\Livewire\Items\Index`: Katalog item snack dan jasa (ongkir) dengan klasifikasi `goods` dan `service`, satuan, serta harga default acuan katalog.
- **Penerbitan & Manajemen PO (`App\Livewire\PurchaseOrders`)**:
  - `Create`: SPV memasukkan nomor PO resmi/Non-PO, memilih vendor, tanggal PO, catatan, dan baris item dinamis. Memilih item master otomatis mengisi nama snapshot, satuan, dan harga satuan yang dapat disesuaikan. Mendukung unggahan file fisik PO (PDF/JPG/PNG maksimal 5 MB) ke private storage (`storage/app/private/documents/po`).
  - `Edit`: Memungkinkan pengubahan data PO sebelum ada pengiriman pertama. Sesuai Bagian 6 aturan bisnis, PO yang sudah memiliki pengiriman barang terkunci dari perubahan harga/kuantitas (dijaga oleh Policy dan Livewire check).
  - `Show`: Menampilkan kartu metrik lengkap (7 metrik: Dipesan [Qty & Rp], Terkirim [Qty & Rp], Sisa Kirim [Qty & Rp], Sudah Ditagih [Rp], Belum Ditagih [Rp], Sudah Dibayar [Rp], dan Belum Dibayar [Rp]). Dilengkapi 4 tab: Rincian Item PO, Riwayat Pengiriman (DO), Riwayat Tagihan (Invoice), dan Dokumen Lampiran.
- **Pengunduhan Dokumen Privat**:
  - Controller `App\Http\Controllers\DocumentController` memvalidasi hak akses via `DocumentPolicy` sebelum mengirimkan file streaming/download, memastikan file private tidak dapat diakses langsung via URL publik web root dan data antar vendor terisolasi.

---

## 7. Implementasi Pengiriman / Deliveries (Fase 3)
- **Alur & Kode Pengiriman Otomatis**:
  - Sistem membuat kode pengiriman berformat serial `DLV-YYYY-XXXXX` (contoh: `DLV-2026-00001`) yang digenerate secara otomatis dan aman dari tabrakan data (mengambil nomor urut tertinggi tahun berjalan).
  - Kolom nomor surat jalan fisik vendor (`vendor_do_number`) disediakan sebagai opsional, karena nomor fisik vendor kerap tidak unik atau menduplikasi nomor PO.
- **Validasi Over-Delivery & Transaksi Atomik**:
  - Kuantitas kirim (`qty_sent`) diperiksa ketat terhadap sisa PO (`qty_remaining`). Pengiriman yang melebihi sisa PO ditolak otomatis.
  - Proses input pengiriman dan verifikasi konfirmasi dilindungi oleh DB transaction dengan `lockForUpdate()` pada entitas `PurchaseOrder` dan `PoItem` untuk mencegah race condition (misalnya dua pengiriman paralel yang melampaui sisa kuota PO).
- **Pengurangan Kuota PO Berdasarkan Konfirmasi Fisik SPV**:
  - Kuota PO tidak berkurang saat pengiriman baru berstatus `submitted`. Sisa PO hanya berkurang secara efektif ketika SPV melakukan konfirmasi penerimaan (`confirmed`) berdasarkan `qty_received`.
  - Jika terdapat barang kurang atau rusak (`qty_received < qty_sent`), sistem mencatat selisih tersebut (`hasDiscrepancy() = true`) dan yang dihitung mengurangi PO hanyalah barang yang benar-benar diterima.
  - Jika SPV menolak pengiriman (`rejected`), kuota sisa PO tidak terpotong sama sekali, dan alasan penolakan wajib dicatat (`reject_reason`).
- **Lampiran DO Fisik**:
  - Foto atau pindaian surat jalan fisik diunggah ke private disk (`storage/app/private/documents/deliveries`) dan terikat secara polymorphic ke entitas `Delivery`.

---

## 8. Implementasi Tagihan / Invoice & 3-Way Match (Fase 4)
- **Pembuatan Invoice Multi-Delivery**:
  - Vendor dapat menggabungkan beberapa pengiriman surat jalan (DO) yang telah terkonfirmasi SPV (`status = confirmed`) dari tanggal-tanggal berbeda dalam satu invoice untuk satu PO acuan.
  - Nilai tagihan dihitung flat secara otomatis: $\text{kuantitas fisik diterima} \times \text{harga terkunci PO}$. Pengiriman dan invoice tidak menentukan harga sendiri.
- **Pencegahan Penagihan Ganda (Anti Double-Billing)**:
  - Setiap baris `delivery_items` memiliki penanda `is_invoiced`.
  - Saat invoice dibuat di dalam DB transaction dengan `lockForUpdate()`, sistem memvalidasi bahwa tidak ada baris pengiriman terpilih yang sudah pernah masuk ke invoice aktif lain. Baris yang ditagihkan langsung ditandai `is_invoiced = true`.
- **Verifikasi 3-Way Match Otomatis**:
  - Sistem melakukan pemeriksaan 3 arah otomatis (PO - DO - Invoice):
    1. *Keunikan Nomor Faktur*: Memastikan vendor tidak menggunakan nomor faktur yang sama lebih dari satu kali (mencegah duplikasi klaim).
    2. *Kesesuaian Harga Satuan*: Menjamin setiap baris tagihan menggunakan harga kontrak PO yang terkunci.
    3. *Kesesuaian Kuantitas Fisik*: Menjamin kuantitas yang ditagihkan sama persis dengan kuantitas yang diterima secara fisik oleh SPV pada saat serah terima barang di DO (`qty_received`).
  - Menampilkan badge visual "3-Way Match Valid" atau tanda peringatan jika ditemukan anomali.
- **Persetujuan & Penolakan Tagihan**:
  - SPV dapat menyetujui tagihan (`approved`) untuk diteruskan ke tahap Payment Request.
  - Jika tagihan ditolak (`rejected`), SPV wajib menyertakan alasan penolakan (`reject_reason`). Sistem secara atomik melepaskan baris pengiriman terkait (`is_invoiced = false`) sehingga vendor dapat merevisi tagihannya kembali.

---

## 9. Implementasi Payment Request & Posting SAP (Fase 5)
- **Pengelompokan Payment Request (PR)**:
  - SPV dapat menggabungkan satu atau beberapa tagihan yang telah disetujui (`status = approved`) ke dalam satu berkas Payment Request.
  - Penomoran Payment Request mengikuti pola serial dinamis internal TC: `TC/PC/{bulan-romawi}/{tahun}/{nomor-urut-3-digit}` (contoh: `TC/PC/XI/2026/001`).
  - Invoice yang telah terikat dalam Payment Request aktif yang belum diposting tidak dapat dipilih kembali untuk pengajuan baru (mencegah double-claim di tingkat pengajuan berkas).
- **Alur & Riwayat Tahapan (Stage Transition & Audit Trail)**:
  - Perjalanan berkas pengajuan pembayaran dipantau secara bertahap melalui entitas `payment_stages` (contoh urutan: *Proses ACC* $\to$ *Proses Finance* $\to$ *Posted SAP*).
  - Setiap perubahan tahapan oleh SPV dicatat dalam tabel `payment_request_stage_logs` lengkap dengan `from_stage_id`, `to_stage_id`, `changed_by`, cap waktu `created_at`, dan catatan pergerakan berkas.
  - Dilengkapi antarmuka visual timeline riwayat alur berkas sehingga SPV maupun Vendor dapat melacak posisi berkas saat ini.
- **Pencatatan Dokumen Posting SAP & Transisi Pelunasan Otomatis**:
  - Ketika pembayaran selesai diproses di sistem akuntansi perusahaan, SPV mencatat nomor dokumen posting SAP (contoh: `5000012345`), tanggal posting, total nominal yang dibayarkan, dan mengunggah dokumen/scan bukti bayar kasir (`DocumentType::PAYMENT_PROOF`).
  - Dalam satu transaksi database atomik dengan `lockForUpdate()`:
    1. Sistem mengunci Payment Request terpilih dan memeriksa bahwa belum ada yang pernah diposting.
    2. Membuat entitas `Posting` dan melampirkan berkas bukti bayar pada storage lokal terlindungi.
    3. Menghubungkan Payment Request ke dokumen posting (`posting_id`), memajukan tahapannya ke tahapan akhir (`is_final = true`), serta mencatat log perpindahan tahap.
    4. Mengunci seluruh invoice yang tergabung di dalam Payment Request tersebut dan mengubah statusnya menjadi **LUNAS (`InvoiceStatus::PAID`)**.
  - Setelah Payment Request memiliki dokumen posting (`posting_id !== null`), tahapan terkunci secara permanen dari perubahan lebih lanjut.
- **Isolasi Data Vendor**:
  - Vendor hanya dapat mengakses dan melihat Payment Request serta Dokumen Posting yang memuat invoice milik mereka sendiri.
  - Vendor dilarang keras membuat atau memodifikasi Payment Request maupun Dokumen Posting (dilindungi oleh `PaymentRequestPolicy` dan `PostingPolicy`).
- **Pengaturan Master Tahapan**:
  - Superadmin memiliki wewenang mengelola daftar tahapan pembayaran (`payment-stages.index`), termasuk mengubah urutan (`sort_order`), menetapkan tahapan akhir (`is_final`), serta mengaktifkan/menonaktifkan tahapan.

---

## 10. Implementasi Dashboard & Monitoring (Fase 6)
- **Arsitektur Dashboard Berbasis Role (`App\Livewire\Dashboard\Dashboard`)**:
  - Antarmuka dashboard secara otomatis menampilkan tampilan yang disesuaikan dengan peran pengguna (`isVendor()` vs `isSpv() / isSuperAdmin()`).
- **Dashboard SPV / Superadmin (`dashboard.spv`)**:
  - *Actionable Alert Cards*: Menampilkan peringatan tugas mendesak hari ini: jumlah DO yang menunggu konfirmasi fisik SPV, jumlah Invoice yang menunggu pencocokan 3-way match, dan jumlah Payment Request yang sedang berproses di ACC/Finance.
  - *Procurement Financial KPIs*: Total Plafon PO Aktif (Rp), Realisasi Barang Diterima (Rp & % pemenuhan kuota), Sisa Kuota PO Belum Terkirim (Rp), dan Total Pelunasan Dokumen SAP (Rp).
  - *Tabel Rekapitulasi Saldo Rekanan (Vendor Breakdown)*: Menampilkan matriks per vendor berisi jumlah PO aktif, plafon PO, nilai pengiriman fisik diterima, sisa kuota kirim, total ditagihkan, total lunas SAP, dan sisa kewajiban penagihan.
  - *Aktivitas Terkini*: Umpan 5 surat jalan (DO) terbaru dan 5 tagihan (invoice) terbaru lengkap dengan badge status terkini.
- **Dashboard Rekanan Vendor (`dashboard.vendor`)**:
  - *Banner Pengingat Siap Tagih*: Mendeteksi otomatis jika ada pengiriman fisik yang telah terkonfirmasi (`status = confirmed`) namun itemnya belum ditagihkan (`is_invoiced = false`). Memberikan informasi jumlah item dan estimasi nominal Rupiah dengan tombol aksi langsung "Buat Tagihan Sekarang".
  - *Vendor Financial KPIs*: Sisa Kuota Kirim PO (Rp), Tagihan Siap Ditagihkan (Rp), Tagihan Dalam Proses Verifikasi/ACC/Finance (Rp), dan Total Pembayaran Lunas Diterima (Rp).
  - *Tabel PO Aktif Rekanan*: Menampilkan daftar PO aktif vendor dengan bilah persentase pemenuhan (*progress bar*) dan tombol cepat pengiriman surat jalan ("Kirim DO").
  - *Isolasi Data Penuh*: Vendor hanya dapat melihat data PO, DO, tagihan, dan pembayaran miliknya sendiri. Data vendor lain tidak bocor ke antarmuka maupun respons komponen.

---

## 11. Penebalan Keamanan, Pengujian & Dokumentasi Akhir (Fase 7)
- **Suite Pengujian Keamanan Lintas Vendor (`tests/Feature/SecurityHardeningTest.php`)**:
  - *Pencegahan IDOR PO*: Memverifikasi bahwa Vendor A tidak dapat membuka, membaca, atau memodifikasi PO milik Vendor B (menghasilkan respons `404 Not Found` melalui Eloquent Global Scope).
  - *Pencegahan IDOR DO*: Memverifikasi Vendor A tidak dapat melihat atau mengonfirmasi surat jalan Vendor B.
  - *Pencegahan IDOR Tagihan*: Memverifikasi isolasi penagihan antar rekanan katering.
  - *Perlindungan Unduh Dokumen Fisik*: Memverifikasi bahwa endpoint streaming `DocumentController` menolak akses unduhan file private (`403 Forbidden`) jika pengguna mencoba mengunduh file milik entitas vendor lain.
  - *Validasi Ketat Berkas Unggahan*: Memverifikasi penolakan file berbahaya/skrip (misal `.php`, `.exe`) serta penolakan file yang melampaui batas maksimal 5 MB (5120 KB).
  - *Integritas Hak Akses Peran*: Memverifikasi bahwa peran `vendor` dilarang menyetujui DO, dilarang menyetujui tagihan, dilarang membuat PR, dilarang membuat posting SAP, dan dilarang memanipulasi master data katalog.
- **Validasi Jejak Audit (`audit_logs`)**:
  - Memverifikasi setiap aksi mutasi status transaksi menghasilkan baris log yang lengkap dengan user ID, jenis tindakan, referensi auditable polymorphic, dan nilai data.
- **Hasil Akhir Pengujian Sistem**:
  - Total 87 test case berjalan 100% lulus (295 assertions) tanpa kegagalan.
  - Aset frontend dikompilasi bersih menggunakan Vite (`npm run build`).
  - Dokumentasi operasional dan panduan deployment lengkap tersedia pada `README.md`.
