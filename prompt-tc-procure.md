# Prompt AI Agent: TC-Procure (nama sementara)

Salin seluruh isi di bawah garis ini ke AI agent kamu (Claude Code, Cursor, dll).

---

## 1. Peran dan tujuan

Kamu adalah senior Laravel engineer. Bangun aplikasi web **TC-Procure**: sistem untuk SPV departemen Training Center mencatat PO snack/makanan dari vendor catering luar, pengiriman harian, invoice, dan status pembayarannya. Vendor punya akun sendiri untuk menginput pengiriman dan invoice, serta memantau sisa barang dan status pembayaran.

Bekerja bertahap sesuai bagian 11. Setelah setiap fase, berhenti dan ringkas apa yang sudah jadi sebelum lanjut. Kalau ada keputusan yang tidak tercakup di prompt ini, ambil opsi paling sederhana, catat asumsinya di `docs/ASSUMPTIONS.md`, jangan menebak diam-diam.

## 2. Stack

- **Laravel versi stabil terbaru** (saat ini Laravel 13, butuh PHP 8.3+). Sebelum mulai, verifikasi versi terbaru lewat `composer`/dokumentasi resmi, jangan mengandalkan ingatan. Pakai installer resmi `laravel new`.
- **MySQL 8.4 LTS**, engine InnoDB, charset `utf8mb4`. Database khusus `tc_procure` dengan user MySQL sendiri (bukan `root`). Database ini harus bisa berjalan berdampingan dengan project lain di server MySQL yang sama, jadi jangan memakai nama tabel atau konfigurasi global yang bentrok.
- Frontend: Blade + Livewire + Tailwind (starter kit resmi Laravel).
- Testing: Pest.
- Bahasa antarmuka: Indonesia. Kode, nama tabel, dan nama kolom: Inggris.
- Zona waktu `Asia/Jakarta`, locale `id`.

## 3. Konteks bisnis

- Pembeli: SPV Training Center. Vendor: catering luar (contoh: Yulia Catering). Sistem harus mendukung banyak vendor.
- **PO** diterbitkan pihak pembeli. Nomor PO berasal dari sistem lain (format seperti `47157028`), jadi SPV **mengetik nomor PO**, sistem tidak membuatnya. Nomor PO unik.
- Ada dua jenis dokumen pesanan: `po` dan `non_po` (pembelian langsung tanpa PO).
- **Surat pengiriman (DO)** dibuat vendor dan **tidak punya nomor unik yang bisa diandalkan** (nomor di kertas bisa sama dengan nomor PO). Sistem harus membuat kode pengiriman sendiri, misalnya `DLV-2026-00001`. Nomor DO vendor hanya kolom opsional.
- **Invoice** dibuat vendor. **Satu invoice menggabungkan beberapa pengiriman dari tanggal berbeda.** Asumsi awal: satu invoice untuk satu PO.
- **Harga flat sesuai PO.** Harga disimpan di item PO dan terkunci. Perubahan harga pasar tidak mempengaruhi PO yang sudah dibuat. Pengiriman dan invoice tidak punya harga sendiri, nilainya dihitung dari qty × harga PO.
- Item bisa berupa barang (pc, kg, box, pak) atau jasa (misalnya ongkos kirim motor/mobil, satuan "au"). Item jasa berkurang 1 setiap kali pengiriman memakainya.
- Setiap baris pengiriman punya nama training dan tanggal diperlukan (opsional), serta catatan rincian varian (contoh: "12 lontong, 12 pudding, 12 bolen, 12 roll").
- **Pembayaran berlapis:** invoice dikelompokkan ke **Payment Request** (nomor internal, contoh `TC/PC/10-X/2025/509`), payment request melewati beberapa **tahap status** yang bisa dikonfigurasi (contoh: Proses ACC, Proses Finance), lalu satu **Posting** (nomor dokumen, tanggal posting, total bayar) bisa mencakup beberapa payment request.

## 4. Role dan akses

| Role           | Hak                                                                                                        |
| -------------- | ---------------------------------------------------------------------------------------------------------- |
| `superadmin` | Kelola user, vendor, master item, tahap payment request, lihat semua data                                  |
| `spv`        | Buat PO, konfirmasi penerimaan, verifikasi invoice, kelola payment request dan posting, lihat semua vendor |
| `vendor`     | Hanya melihat PO milik vendornya, input pengiriman, buat dan unggah invoice, lihat status pembayaran       |

- User role `vendor` terikat ke satu `vendor_id`. **Pembatasan data vendor wajib di level query** (global scope atau policy), bukan hanya disembunyikan di menu. Tulis test yang membuktikan vendor A tidak bisa mengakses data vendor B lewat URL langsung.
- Vendor tidak bisa mengubah PO, harga, payment request, maupun posting.
- SPV tidak mengubah dokumen yang diunggah vendor, hanya menyetujui atau menolak dengan alasan.

## 5. Model data (acuan, boleh dirapikan)

Aturan umum: uang disimpan sebagai **bilangan bulat rupiah** (`unsignedBigInteger`), tidak pakai float. Qty `decimal(12,2)` (ada satuan kg). Semua tabel utama pakai `created_by`/`updated_by` dan `softDeletes`. **Tidak ada hard delete** untuk data transaksi.

- `users` (role, `vendor_id` nullable, is_active)
- `vendors` (name, contact, address, is_active)
- `items` (name, unit, `type`: goods|service, `default_price` nullable, is_active). `default_price` hanya saran saat membuat PO.
- `purchase_orders` (`po_number` unik, `type`: po|non_po, vendor_id, po_date, status, notes)
- `po_items` (purchase_order_id, item_id, qty_ordered, `unit_price` terkunci, snapshot nama dan satuan item)
- `deliveries` (`code` unik otomatis, purchase_order_id, vendor_id, delivery_date, `vendor_do_number` nullable, status: submitted|confirmed|rejected, reject_reason, submitted_by, confirmed_by, confirmed_at, notes)
- `delivery_items` (delivery_id, po_item_id, `qty_sent`, `qty_received` nullable, `training_name` nullable, `needed_date` nullable, `variant_note` nullable, penanda sudah masuk invoice aktif)
- `invoices` (`invoice_number`, vendor_id, purchase_order_id, invoice_date, total_amount, status: draft|submitted|approved|rejected|in_payment|paid|void, reject_reason)
- `invoice_lines` (invoice_id, delivery_item_id, qty, unit_price, amount). Satu `delivery_item` hanya boleh ada di satu invoice aktif.
- `payment_stages` (name, sort_order, is_final), bisa dikonfigurasi superadmin
- `payment_requests` (`number` unik, current_stage_id, submitted_at, notes, posting_id nullable) + pivot `payment_request_invoices`
- `payment_request_stage_logs` (riwayat perpindahan tahap: siapa, kapan, catatan)
- `postings` (`document_number`, posting_date, total_paid, notes)
- `documents` (polymorphic: `documentable`, `type`: po|delivery_order|invoice|payment_proof|other, path, original_name, mime, size, uploaded_by)
- `audit_logs` (pakai `spatie/laravel-activitylog` atau tabel sendiri; catat siapa mengubah apa dan kapan)

Tambahkan index pada kolom yang sering difilter (vendor_id, status, tanggal, nomor dokumen).

## 6. Alur dan aturan bisnis

1. **SPV menerbitkan PO:** input nomor PO, vendor, item, qty, harga, unggah dokumen PO. Setelah ada pengiriman pertama, harga dan qty item PO **tidak boleh diubah**. Perubahan dilakukan lewat PO baru atau addendum yang tercatat.
2. **Vendor input pengiriman:** pilih PO, isi tanggal, qty per item, keterangan varian, nama training, unggah foto/scan DO. Qty kirim tidak boleh melebihi sisa PO (kecuali ada pengaturan over-delivery yang eksplisit; default: ditolak).
3. **SPV konfirmasi penerimaan:** isi `qty_received` (default = qty_sent). **Sisa PO hanya berkurang dari qty yang sudah dikonfirmasi.** Kalau berbeda dari qty kirim, selisih tercatat dan terlihat.
4. **Vendor membuat invoice:** memilih pengiriman terkonfirmasi yang belum ditagih (boleh beda tanggal), nilai dihitung otomatis dari qty × harga PO, unggah file invoice. Kirim ke SPV.
5. **SPV verifikasi (3-way match):** bandingkan PO, DO, dan invoice. Beri tanda otomatis kalau qty invoice ≠ qty diterima, atau harga ≠ harga PO, atau nomor invoice sudah pernah dipakai vendor yang sama. SPV menyetujui, atau menolak dengan alasan wajib. Invoice yang ditolak kembali ke vendor untuk direvisi.
6. **SPV membuat payment request:** kelompokkan satu atau lebih invoice berstatus approved, isi nomor payment request. Invoice masuk status `in_payment`.
7. **SPV memperbarui tahap payment request** sesuai `payment_stages`, dengan riwayat di stage log.
8. **SPV membuat posting:** isi nomor dokumen, tanggal posting, total bayar, dan hubungkan ke satu atau lebih payment request. Invoice terkait menjadi `paid`.
9. **Vendor memantau status** semua PO dan invoice miliknya.

**Status dihitung dari data, bukan diketik manual.** Untuk setiap PO tampilkan (qty dan rupiah): dipesan, sudah dikirim (terkonfirmasi), sisa belum dikirim, sudah ditagih, belum ditagih, sudah dibayar, belum dibayar. PO dianggap selesai dikirim kalau sisa = 0, dan lunas kalau semua invoice terkait `paid`.

## 7. Dokumen dan file

- Simpan di disk privat (bukan `public`). Unduh lewat route yang melewati policy atau signed URL.
- Validasi: hanya `pdf`, `jpg`, `jpeg`, `png`; maksimal 5 MB per file; periksa MIME sebenarnya; nama file di disk di-randomize.
- Satu dokumen mencatat jenis, pengunggah, dan waktu. Dokumen yang sudah dikonfirmasi/diposting tidak bisa diganti, hanya ditambah.

## 8. Keamanan dan integritas data

- Semua aksi lewat Policy. Semua input lewat Form Request.
- Pengurangan sisa qty dan pemakaian pengiriman ke invoice dilakukan di **DB transaction** dengan `lockForUpdate()` agar tidak terjadi balapan data (dua pengiriman bersamaan melebihi sisa PO, atau satu pengiriman masuk dua invoice).
- Foreign key dan unique constraint dibuat di level database, bukan hanya validasi aplikasi.
- Jangan ada hard delete data transaksi. Pembatalan memakai status `void` atau `cancelled` dengan alasan.
- Rate limit pada login. Password di-hash (default Laravel). Sembunyikan data bank/NPWP vendor dari role yang tidak perlu.
- Konfigurasi sensitif hanya di `.env`. Jangan commit `.env`.

## 9. Antarmuka minimum

- Dashboard SPV: ringkasan angka per vendor, daftar PO aktif dengan progres, invoice menunggu verifikasi, payment request per tahap.
- Dashboard vendor: PO miliknya dengan sisa belum dikirim, belum ditagih, belum dibayar, sudah dibayar.
- Tabel dengan pencarian, filter (vendor, status, rentang tanggal), dan paginasi.
- Halaman detail PO menampilkan semua pengiriman, invoice, dan pembayaran terkait dalam satu tempat.
- Responsif (dipakai juga dari HP).

## 10. Di luar cakupan awal (jangan dibangun dulu)

- Integrasi langsung dengan SAP atau sistem finance.
- Notifikasi email/WhatsApp.
- Modul "pesanan harian per training" sebagai entitas terpisah. Untuk sekarang cukup kolom `training_name` dan `needed_date` di `delivery_items`.
- OCR dokumen.
- Impor data lama dari Excel (direncanakan setelah fase 7).

## 11. Fase kerja

0. **Setup:** proyek Laravel terbaru, koneksi MySQL, auth, role, struktur folder, Pest, seeder user awal. Buat `docs/ASSUMPTIONS.md`.
1. **Migrasi, model, relasi, factory, seeder** sesuai bagian 5, plus test relasi dasar.
2. **Master dan PO:** vendor, item, PO beserta item dan dokumen PO.
3. **Pengiriman:** input vendor, konfirmasi SPV, perhitungan sisa PO, test aturan qty.
4. **Invoice:** pembuatan dari pengiriman, 3-way match, setujui atau tolak, test penagihan ganda.
5. **Payment request dan posting:** tahap status, riwayat, posting, perubahan status invoice.
6. **Dashboard dan laporan** status per PO dan per vendor.
7. **Hardening:** policy test lintas vendor, audit log, validasi file, README dan petunjuk deploy.

Di akhir tiap fase: jalankan test, tulis ringkasan perubahan, daftar asumsi baru, dan pertanyaan yang perlu dijawab sebelum fase berikutnya.
