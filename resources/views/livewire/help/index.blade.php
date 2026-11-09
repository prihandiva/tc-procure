<div class="space-y-6 pb-12" x-data="{
    activeTab: @entangle('tab'),
    searchQuery: @entangle('search'),
    openFaq: null,
    toggleFaq(id) {
        this.openFaq = this.openFaq === id ? null : id;
    }
}">
    <!-- Header Hero Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-900 text-white p-6 sm:p-8 shadow-xl border border-indigo-800/40">
        <!-- Background Ambient Glow -->
        <div class="absolute -top-24 -right-24 size-96 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 size-80 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 border border-indigo-400/30 text-indigo-200">
                    <flux:icon icon="book-open" class="size-3.5 text-indigo-300" />
                    <span>Pusat Bantuan & Panduan Sistem Operasional</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Panduan & Alur Pengadaan TC-Procure
                </h1>
                <p class="text-sm sm:text-base text-indigo-100/80 leading-relaxed">
                    Pelajari tata cara alur pengadaan makanan ringan & catering Training Center dari penerbitan kontrak PO, pengiriman fisik bersurat jalan, verifikasi 3-way match, hingga pencairan SAP.
                </p>

                <!-- Current User Role Context Badge -->
                <div class="pt-1 flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-indigo-300">Anda saat ini login sebagai:</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md font-bold {{ $isVendor ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-indigo-400/20 text-indigo-200 border border-indigo-300/30' }}">
                        <span class="size-2 rounded-full {{ $isVendor ? 'bg-emerald-400' : 'bg-indigo-400' }}"></span>
                        {{ $currentUser?->name }} ({{ $currentUser?->role?->label() }})
                    </span>
                    @if ($isVendor)
                        <span class="text-indigo-300/80">&bull; {{ $currentUser?->vendor?->name }}</span>
                    @endif
                </div>
            </div>

            <!-- Quick Action & Search -->
            <div class="w-full md:w-80 space-y-3 shrink-0">
                <div class="relative">
                    <flux:icon icon="magnifying-glass" class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-slate-400" />
                    <input
                        type="text"
                        x-model="searchQuery"
                        placeholder="Cari topik bantuan / FAQ..."
                        class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm rounded-xl bg-white/10 text-white placeholder-indigo-200/60 border border-white/20 focus:outline-hidden focus:ring-2 focus:ring-indigo-400 focus:bg-white/15 transition backdrop-blur-md"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="window.print()"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg bg-white/10 hover:bg-white/20 text-white border border-white/20 transition cursor-pointer"
                    >
                        <flux:icon icon="printer" class="size-3.5" />
                        Cetak Panduan
                    </button>
                    <a
                        href="{{ route('dashboard') }}"
                        wire:navigate
                        class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs transition"
                    >
                        <flux:icon icon="home" class="size-3.5" />
                        Ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-xl p-1.5 shadow-xs sticky top-14 lg:top-0 z-30">
        <div class="flex items-center gap-1 sm:gap-2 overflow-x-auto no-scrollbar">
            <button
                type="button"
                @click="activeTab = 'flow'; $wire.setTab('flow')"
                :class="activeTab === 'flow' ? 'bg-indigo-600 text-white shadow-xs font-bold' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 font-semibold'"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs sm:text-sm whitespace-nowrap transition cursor-pointer"
            >
                <flux:icon icon="arrow-path" class="size-4" />
                <span>🌐 Alur Pengadaan (End-to-End)</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'tc'; $wire.setTab('tc')"
                :class="activeTab === 'tc' ? 'bg-indigo-600 text-white shadow-xs font-bold' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 font-semibold'"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs sm:text-sm whitespace-nowrap transition cursor-pointer"
            >
                <flux:icon icon="clipboard-document-check" class="size-4" />
                <span>🏢 Panduan Tim TC (Pengadaan & SPV)</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'vendor'; $wire.setTab('vendor')"
                :class="activeTab === 'vendor' ? 'bg-indigo-600 text-white shadow-xs font-bold' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 font-semibold'"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs sm:text-sm whitespace-nowrap transition cursor-pointer"
            >
                <flux:icon icon="truck" class="size-4" />
                <span>🚚 Panduan Vendor (Rekanan Katering)</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'faq'; $wire.setTab('faq')"
                :class="activeTab === 'faq' ? 'bg-indigo-600 text-white shadow-xs font-bold' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 font-semibold'"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs sm:text-sm whitespace-nowrap transition cursor-pointer"
            >
                <flux:icon icon="question-mark-circle" class="size-4" />
                <span>❓ FAQ & Troubleshooting</span>
            </button>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: ALUR PENGADAAN (END-TO-END FLOW)     -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'flow'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        <!-- Overview Banner -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="flex size-7 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 text-xs font-black">6</span>
                        Siklus Tahapan Pengadaan Snack & Catering
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 mt-1">
                        Sistem dirancang dengan tata kelola akuntansi ketat (*strict governance*) menggunakan verifikasi fisik, validasi 3-Way Match otomatis, dan integrasi Dokumen Posting SAP.
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs font-medium">
                    <span class="flex items-center gap-1.5 text-indigo-600 dark:text-indigo-400 font-bold">
                        <span class="size-3 rounded-full bg-indigo-600"></span> Tim TC / SPV
                    </span>
                    <span class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-bold">
                        <span class="size-3 rounded-full bg-emerald-600"></span> Vendor Rekanan
                    </span>
                    <span class="flex items-center gap-1.5 text-purple-600 dark:text-purple-400 font-bold">
                        <span class="size-3 rounded-full bg-purple-600"></span> Bagian Finance
                    </span>
                </div>
            </div>

            <!-- Visual Flow Stepper Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
                <!-- Step 1: Terbit PO -->
                <div class="relative bg-slate-50/80 dark:bg-zinc-800/50 rounded-xl p-5 border-2 border-indigo-200 dark:border-indigo-900/60 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center justify-center size-8 rounded-lg bg-indigo-600 text-white font-extrabold text-sm shadow-xs">01</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/80 dark:text-indigo-300 border border-indigo-200">
                            Aktor: Tim TC
                        </span>
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Penerbitan PO Kontrak</h3>
                    <p class="text-xs text-slate-600 dark:text-zinc-400 mt-2 leading-relaxed">
                        TC menerbitkan Purchase Order resmi yang berisi daftar item snack, harga flat satuan, dan batas kuota plafon pemesanan maksimal.
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-200/80 dark:border-zinc-700/60 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500">Output:</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400">PO Status Active</span>
                    </div>
                </div>

                <!-- Step 2: Kirim DO & Foto -->
                <div class="relative bg-slate-50/80 dark:bg-zinc-800/50 rounded-xl p-5 border-2 border-emerald-200 dark:border-emerald-900/60 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center justify-center size-8 rounded-lg bg-emerald-600 text-white font-extrabold text-sm shadow-xs">02</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-200">
                            Aktor: Vendor
                        </span>
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Pengiriman & Foto Surat Jalan</h3>
                    <p class="text-xs text-slate-600 dark:text-zinc-400 mt-2 leading-relaxed">
                        Vendor mengantar snack ke Training Center, lalu memotret Surat Jalan fisik menggunakan fitur <strong>Kamera Smartphone</strong> langsung pada sistem.
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-200/80 dark:border-zinc-700/60 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500">Output:</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400">DO Menunggu Verifikasi</span>
                    </div>
                </div>

                <!-- Step 3: Verifikasi Fisik TC -->
                <div class="relative bg-slate-50/80 dark:bg-zinc-800/50 rounded-xl p-5 border-2 border-indigo-200 dark:border-indigo-900/60 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center justify-center size-8 rounded-lg bg-indigo-600 text-white font-extrabold text-sm shadow-xs">03</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/80 dark:text-indigo-300 border border-indigo-200">
                            Aktor: SPV TC
                        </span>
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Verifikasi Fisik & Selisih</h3>
                    <p class="text-xs text-slate-600 dark:text-zinc-400 mt-2 leading-relaxed">
                        SPV TC menghitung fisik makanan riil yang diterima. Jika ada yang rusak/kurang, sistem mencatat selisih. Kuota PO otomatis terpotong riil.
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-200/80 dark:border-zinc-700/60 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500">Output:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">DO Terkonfirmasi & Realisasi Masuk</span>
                    </div>
                </div>

                <!-- Step 4: Ajukan Tagihan -->
                <div class="relative bg-slate-50/80 dark:bg-zinc-800/50 rounded-xl p-5 border-2 border-emerald-200 dark:border-emerald-900/60 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center justify-center size-8 rounded-lg bg-emerald-600 text-white font-extrabold text-sm shadow-xs">04</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-200">
                            Aktor: Vendor
                        </span>
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Pengajuan Tagihan (Invoice)</h3>
                    <p class="text-xs text-slate-600 dark:text-zinc-400 mt-2 leading-relaxed">
                        Vendor memilih DO yang sudah <strong>Terkonfirmasi</strong>, memasukkan nomor faktur resmi vendor, jatuh tempo, serta mengunggah bukti invoice fisik.
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-200/80 dark:border-zinc-700/60 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500">Output:</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400">Invoice Submitted</span>
                    </div>
                </div>

                <!-- Step 5: 3-Way Match & Approval -->
                <div class="relative bg-slate-50/80 dark:bg-zinc-800/50 rounded-xl p-5 border-2 border-indigo-200 dark:border-indigo-900/60 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center justify-center size-8 rounded-lg bg-indigo-600 text-white font-extrabold text-sm shadow-xs">05</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/80 dark:text-indigo-300 border border-indigo-200">
                            Aktor: Tim TC
                        </span>
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Validasi 3-Way Match</h3>
                    <p class="text-xs text-slate-600 dark:text-zinc-400 mt-2 leading-relaxed">
                        Sistem mengaudit kesesuaian nilai 3 berkas: <strong>PO Acuan</strong> vs <strong>DO Fisik Diterima</strong> vs <strong>Invoice Vendor</strong>. Jika cocok, TC menyetujui tagihan.
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-200/80 dark:border-zinc-700/60 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500">Output:</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400">Invoice Approved</span>
                    </div>
                </div>

                <!-- Step 6: PR & SAP Posting -->
                <div class="relative bg-slate-50/80 dark:bg-zinc-800/50 rounded-xl p-5 border-2 border-purple-200 dark:border-purple-900/60 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center justify-center size-8 rounded-lg bg-purple-600 text-white font-extrabold text-sm shadow-xs">06</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800 dark:bg-purple-950/80 dark:text-purple-300 border border-purple-200">
                            Aktor: TC & Finance
                        </span>
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Payment Request & SAP</h3>
                    <p class="text-xs text-slate-600 dark:text-zinc-400 mt-2 leading-relaxed">
                        TC membundel invoice ke berkas PR untuk diajukan ke Finance. Setelah dana cair, nomor Dokumen SAP diinput dan tagihan berstatus Lunas.
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-200/80 dark:border-zinc-700/60 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500">Output:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">Invoice Lunas (Paid)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Responsibility Matrix (Tabel Tanggung Jawab) -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs">
            <h3 class="font-bold text-slate-900 dark:text-white text-base mb-4 flex items-center gap-2">
                <flux:icon icon="table-cells" class="size-5 text-indigo-600 dark:text-indigo-400" />
                Matriks Hak Akses & Pembagian Tanggung Jawab
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-zinc-800 text-slate-500 dark:text-zinc-400 bg-slate-50/50 dark:bg-zinc-800/40">
                            <th class="py-3 px-4 font-bold">Aktivitas / Modul</th>
                            <th class="py-3 px-4 font-bold text-center">Super Admin</th>
                            <th class="py-3 px-4 font-bold text-center">Supervisor (SPV)</th>
                            <th class="py-3 px-4 font-bold text-center">Vendor Rekanan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 font-medium">
                        <tr>
                            <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">Penerbitan PO & Master Data Item/Vendor</td>
                            <td class="py-3 px-4 text-center text-emerald-600 font-bold">✓ Penuh</td>
                            <td class="py-3 px-4 text-center text-emerald-600 font-bold">✓ Penuh</td>
                            <td class="py-3 px-4 text-center text-slate-400">- Hanya Lihat</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">Pembuatan Surat Jalan Pengiriman (DO)</td>
                            <td class="py-3 px-4 text-center text-slate-400">- Monitoring</td>
                            <td class="py-3 px-4 text-center text-slate-400">- Monitoring</td>
                            <td class="py-3 px-4 text-center text-emerald-600 font-bold">✓ Wajib Mengajukan</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">Verifikasi Fisik & Konfirmasi DO (Hitung Selisih)</td>
                            <td class="py-3 px-4 text-center text-emerald-600 font-bold">✓ Otoritas</td>
                            <td class="py-3 px-4 text-center text-emerald-600 font-bold">✓ Otoritas Utama</td>
                            <td class="py-3 px-4 text-center text-slate-400">- Tidak Berhak</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">Pengajuan Tagihan / Invoice</td>
                            <td class="py-3 px-4 text-center text-slate-400">- Review</td>
                            <td class="py-3 px-4 text-center text-slate-400">- Review</td>
                            <td class="py-3 px-4 text-center text-emerald-600 font-bold">✓ Wajib Mengajukan</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">Audit 3-Way Match & Persetujuan Invoice</td>
                            <td class="py-3 px-4 text-center text-emerald-600 font-bold">✓ Otoritas</td>
                            <td class="py-3 px-4 text-center text-emerald-600 font-bold">✓ Otoritas</td>
                            <td class="py-3 px-4 text-center text-slate-400">- Tidak Berhak</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">Pembuatan Payment Request (PR) & Dokumen SAP</td>
                            <td class="py-3 px-4 text-center text-emerald-600 font-bold">✓ Penuh</td>
                            <td class="py-3 px-4 text-center text-emerald-600 font-bold">✓ Penuh</td>
                            <td class="py-3 px-4 text-center text-slate-400">- Hanya Pantau Lunas</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: PANDUAN TIM TC (PENGADAAN & SPV)    -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'tc'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        <!-- Subheader -->
        <div class="bg-indigo-50/80 dark:bg-indigo-950/40 p-4 rounded-xl border border-indigo-200 dark:border-indigo-900/50 flex items-start gap-3">
            <div class="p-2 rounded-lg bg-indigo-600 text-white shrink-0 mt-0.5">
                <flux:icon icon="shield-check" class="size-5" />
            </div>
            <div>
                <h3 class="font-bold text-indigo-950 dark:text-indigo-200 text-sm">SOP & Panduan Khusus Tim Training Center (TC & SPV)</h3>
                <p class="text-xs text-indigo-800/80 dark:text-indigo-300/80 mt-1 leading-relaxed">
                    Sebagai pengelola pengadaan, tugas utama Anda adalah memastikan kepatuhan plafon kontrak PO, menjaga integritas penerimaan fisik di lapangan, dan memverifikasi kelayakan pembayaran vendor secara akuntabel.
                </p>
            </div>
        </div>

        <!-- Step-by-Step TC Modules -->
        <div class="space-y-4">
            <!-- Modul 1: PO -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-3">
                <div class="flex items-center gap-3">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-bold text-xs">1</span>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Cara Menerbitkan & Mengelola Purchase Order (PO)</h3>
                </div>
                <div class="space-y-2 text-xs sm:text-sm text-slate-600 dark:text-zinc-400 pl-10">
                    <p>1. Buka menu navigasi <strong>Purchase Order (PO)</strong> lalu klik tombol <strong>+ Terbitkan PO Baru</strong>.</p>
                    <p>2. Tentukan <strong>Nomor PO Resmi</strong> (contoh: <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-zinc-800 font-mono text-indigo-600">PO-2026-0001</code>), tanggal kontrak, dan pilih <strong>Vendor Rekanan</strong> yang ditunjuk.</p>
                    <p>3. Tambahkan rincian item snack/makanan: pilih nama item, masukkan kuantitas plafon total (contoh: 500 porsi snack box), dan tentukan harga satuan flat.</p>
                    <p>4. Unggah salinan dokumen kontrak PO (PDF/Foto) sebagai arsip digital pengadaan.</p>
                    <div class="p-3 bg-amber-50 dark:bg-amber-950/40 rounded-lg border border-amber-200/60 dark:border-amber-900/40 text-amber-900 dark:text-amber-200 text-xs">
                        <strong>📌 Aturan Plafon:</strong> Total nilai PO menjadi pagu anggaran maksimal. Vendor tidak akan bisa membuat Surat Jalan melebihi sisa kuota yang terdaftar pada PO ini.
                    </div>
                </div>
            </div>

            <!-- Modul 2: Konfirmasi DO -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-3">
                <div class="flex items-center gap-3">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-bold text-xs">2</span>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Cara Verifikasi Fisik & Konfirmasi Surat Jalan (DO)</h3>
                </div>
                <div class="space-y-2 text-xs sm:text-sm text-slate-600 dark:text-zinc-400 pl-10">
                    <p>1. Ketika kurir/driver katering tiba di lokasi Training Center, buka menu <strong>Pengiriman (DO)</strong>.</p>
                    <p>2. Pilih pengiriman yang berstatus <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">Menunggu Verifikasi SPV</span>.</p>
                    <p>3. Periksa fisik makanan yang tiba di meja resepsi/training:
                        <ul class="list-disc pl-5 mt-1 space-y-1">
                            <li>Jika kuantitas pas dan bagus: Biarkan nilai di kolom <strong>Qty Diterima</strong> sama dengan Qty Kirim (Selisih otomatis <span class="text-emerald-600 font-bold">0,00 Sesuai</span>).</li>
                            <li>Jika ada barang yang rusak/kurang: Ubah angka pada kolom <strong>Qty Diterima</strong> sesuai fisik riil yang layak konsumsi (Sistem otomatis mencatat <span class="text-rose-600 font-bold">Kurang X</span>).</li>
                        </ul>
                    </p>
                    <p>4. Klik pratinjau scan <strong>Surat Jalan Fisik</strong> dari vendor untuk memastikan tanda tangan basah penerima training tercantum.</p>
                    <p>5. Klik tombol hijau <strong>✓ Konfirmasi Diterima</strong>. Seketika itu juga, kuota PO terpotong riil dan nilai barang diterima (realisasi) langsung bertambah di Dashboard.</p>
                </div>
            </div>

            <!-- Modul 3: 3-Way Match & Invoice -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-3">
                <div class="flex items-center gap-3">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-bold text-xs">3</span>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Pemeriksaan Otomatis 3-Way Match & Persetujuan Tagihan</h3>
                </div>
                <div class="space-y-2 text-xs sm:text-sm text-slate-600 dark:text-zinc-400 pl-10">
                    <p>1. Buka menu <strong>Tagihan (Invoice)</strong> dan klik tagihan vendor yang berstatus <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">Diajukan</span>.</p>
                    <p>2. Perhatikan kartu <strong>Pemeriksaan Validasi 3-Way Match</strong>:
                        <ul class="list-disc pl-5 mt-1 space-y-1">
                            <li><strong class="text-indigo-600">Pilar 1 (Kontrak PO):</strong> Memastikan harga satuan snack tidak dimark-up oleh vendor.</li>
                            <li><strong class="text-emerald-600">Pilar 2 (Realisasi DO):</strong> Memastikan jumlah yang ditagihkan hanya barang yang telah dikonfirmasi diterima oleh SPV.</li>
                            <li><strong class="text-purple-600">Pilar 3 (Invoice):</strong> Memastikan total nilai tagihan tidak melebihi hasil kali kuantitas diterima dengan harga kontrak.</li>
                        </ul>
                    </p>
                    <p>3. Jika seluruh indikator valid, klik <strong>✓ Setujui Tagihan</strong>. Tagihan siap diajukan pencairannya ke bagian Finance.</p>
                </div>
            </div>

            <!-- Modul 4: PR & SAP Posting -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-3">
                <div class="flex items-center gap-3">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-bold text-xs">4</span>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Pembuatan Payment Request (PR) & Dokumen SAP</h3>
                </div>
                <div class="space-y-2 text-xs sm:text-sm text-slate-600 dark:text-zinc-400 pl-10">
                    <p>1. Buka menu <strong>Payment Request (PR)</strong> ➔ Klik <strong>+ Buat Pengajuan PR</strong>.</p>
                    <p>2. Centang tagihan-tagihan vendor yang sudah disetujui, pilih nomor rekening bank tujuan vendor, dan tentukan nominal bayar.</p>
                    <p>3. Cetak lembar pengajuan PR untuk diserahkan ke Pejabat Otorisasi & Finance.</p>
                    <p>4. Setelah Finance mentransfer dana, buka menu <strong>Dokumen Posting SAP</strong> ➔ Klik <strong>+ Catat Posting SAP</strong>.</p>
                    <p>5. Masukkan <strong>Nomor Dokumen SAP</strong> (misal: <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-zinc-800 font-mono text-purple-600">5100012345</code>), tanggal transfer, dan lampirkan bukti bayar kasir/voucher transfer.</p>
                    <p>6. Klik Simpan. Tagihan otomatis berganti status menjadi <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">Lunas</span>.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 3: PANDUAN VENDOR (REKANAN KATERING)   -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'vendor'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        <!-- Subheader -->
        <div class="bg-emerald-50/80 dark:bg-emerald-950/40 p-4 rounded-xl border border-emerald-200 dark:border-emerald-900/50 flex items-start gap-3">
            <div class="p-2 rounded-lg bg-emerald-600 text-white shrink-0 mt-0.5">
                <flux:icon icon="truck" class="size-5" />
            </div>
            <div>
                <h3 class="font-bold text-emerald-950 dark:text-emerald-200 text-sm">Panduan Operasional untuk Rekanan Vendor & Katering</h3>
                <p class="text-xs text-emerald-800/80 dark:text-emerald-300/80 mt-1 leading-relaxed">
                    Panduan ringkas bagi penyedia jasa untuk memantau kuota kontrak, membuat Surat Jalan pengiriman menggunakan kamera smartphone, mengajukan tagihan, dan melacak status pembayaran secara transparan.
                </p>
            </div>
        </div>

        <!-- Step-by-Step Vendor Modules -->
        <div class="space-y-4">
            <!-- Modul 1: Cek Kuota -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-3">
                <div class="flex items-center gap-3">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 font-bold text-xs">1</span>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Mengecek Kuota PO Aktif Sebelum Memasak / Mengantar</h3>
                </div>
                <div class="space-y-2 text-xs sm:text-sm text-slate-600 dark:text-zinc-400 pl-10">
                    <p>1. Masuk ke aplikasi menggunakan <strong>Akun Vendor</strong> Anda.</p>
                    <p>2. Pada halaman utama (Dashboard), periksa kartu:
                        <ul class="list-disc pl-5 mt-1 space-y-1">
                            <li><strong>Plafon PO:</strong> Total pesanan yang disepakati dengan Training Center.</li>
                            <li><strong>Realisasi Diterima:</strong> Makanan yang sudah Anda kirim dan telah disetujui SPV.</li>
                            <li><strong>Sisa Kuota Belum Dikirim:</strong> Maksimal barang yang masih boleh Anda kirimkan.</li>
                        </ul>
                    </p>
                    <div class="p-3 bg-rose-50 dark:bg-rose-950/40 rounded-lg border border-rose-200/60 dark:border-rose-900/40 text-rose-900 dark:text-rose-200 text-xs">
                        <strong>⚠️ Peringatan:</strong> Jangan menyiapkan atau mengirim makanan melebihi sisa kuota PO aktif. Sistem akan menolak pembuatan Surat Jalan jika kuantitas melebihi sisa kuota kontrak.
                    </div>
                </div>
            </div>

            <!-- Modul 2: Buat DO & Kamera -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-3">
                <div class="flex items-center gap-3">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 font-bold text-xs">2</span>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Membuat Surat Jalan (DO) & Memotret Bukti Fisik</h3>
                </div>
                <div class="space-y-2 text-xs sm:text-sm text-slate-600 dark:text-zinc-400 pl-10">
                    <p>1. Buka menu <strong>Pengiriman (DO)</strong> lalu klik tombol <strong>+ Buat Surat Jalan DO</strong>.</p>
                    <p>2. Pilih <strong>PO Acuan</strong> yang sedang berjalan.</p>
                    <p>3. Masukkan <strong>Nomor Surat Jalan Fisik Vendor</strong> (contoh: <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-zinc-800 font-mono text-emerald-600">SJ-089/YC/XI/2026</code>) dan tanggal pengiriman.</p>
                    <p>4. Masukkan kuantitas item yang Anda kirimkan untuk sesi pelatihan tersebut.</p>
                    <p>5. Tuliskan <strong>Nama Training</strong> (misal: <em>Leadership Batch 2</em>) dan catatan varian (misal: <em>15 Box Rasa Ayam, 10 Box Manis</em>).</p>
                    <p>6. <strong>Memotret Surat Jalan Fisik (Kamera Langsung):</strong>
                        <ul class="list-disc pl-5 mt-1 space-y-1">
                            <li>Klik tombol <strong>📸 Buka Kamera (Foto Fisik)</strong> dari browser HP driver/kurir Anda.</li>
                            <li>Arahkan kamera ke kertas Surat Jalan fisik yang <strong>sudah ditandatangani oleh penerima di TC</strong>.</li>
                            <li>Foto akan langsung tampil dalam pratinjau thumbnail lengkap dengan tombol periksa.</li>
                        </ul>
                    </p>
                    <p>7. Klik <strong>Simpan & Ajukan Pengiriman</strong>. Status akan menjadi <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">Menunggu Verifikasi SPV</span>.</p>
                </div>
            </div>

            <!-- Modul 3: Ajukan Invoice -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-3">
                <div class="flex items-center gap-3">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 font-bold text-xs">3</span>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Mengajukan Tagihan / Invoice</h3>
                </div>
                <div class="space-y-2 text-xs sm:text-sm text-slate-600 dark:text-zinc-400 pl-10">
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 rounded-lg border border-emerald-200/60 dark:border-emerald-900/40 text-emerald-900 dark:text-emerald-200 text-xs mb-2">
                        <strong>✅ Syarat Mutlak Tagihan:</strong> Pengiriman Anda harus sudah diverifikasi dan berstatus <span class="font-bold underline">Terkonfirmasi</span> oleh SPV TC. Surat Jalan yang belum diverifikasi tidak akan muncul di form tagihan.
                    </div>
                    <p>1. Buka menu <strong>Tagihan (Invoice)</strong> lalu klik tombol <strong>+ Ajukan Tagihan Baru</strong>.</p>
                    <p>2. Centang item-item Surat Jalan yang sudah terkonfirmasi yang ingin Anda tagihkan.</p>
                    <p>3. Masukkan <strong>Nomor Invoice Resmi Vendor</strong> (contoh: <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-zinc-800 font-mono text-emerald-600">INV-2026-0042</code>), tanggal invoice, dan tanggal jatuh tempo.</p>
                    <p>4. Unggah foto atau file PDF berkas kwitansi / faktur tagihan resmi vendor.</p>
                    <p>5. Klik <strong>Kirim Tagihan</strong>. Tagihan akan masuk ke antrean verifikasi Tim TC.</p>
                </div>
            </div>

            <!-- Modul 4: Pantau Pembayaran -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-3">
                <div class="flex items-center gap-3">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 font-bold text-xs">4</span>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Melacak Status Pembayaran & Mengunduh Bukti SAP</h3>
                </div>
                <div class="space-y-2 text-xs sm:text-sm text-slate-600 dark:text-zinc-400 pl-10">
                    <p>Anda dapat memantau setiap tahap pembayaran invoice Anda:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 pt-1">
                        <div class="p-2.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-200/60 text-xs">
                            <span class="font-bold text-amber-700 block">1. Diajukan</span>
                            <span class="text-[11px] text-slate-500">Menunggu audit 3-way match TC</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200/60 text-xs">
                            <span class="font-bold text-indigo-700 block">2. Disetujui</span>
                            <span class="text-[11px] text-slate-500">Lolos audit, siap diajukan ke Finance</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-purple-50 dark:bg-purple-950/40 border border-purple-200/60 text-xs">
                            <span class="font-bold text-purple-700 block">3. Proses PR</span>
                            <span class="text-[11px] text-slate-500">Berkas sedang diproses kasir Finance</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 text-xs">
                            <span class="font-bold text-emerald-700 block">4. Lunas (Paid)</span>
                            <span class="text-[11px] text-slate-500">Dana telah ditransfer via SAP</span>
                        </div>
                    </div>
                    <p class="pt-2">Saat tagihan berstatus <strong>Lunas</strong>, buka detail invoice untuk melihat <strong>Nomor Dokumen SAP</strong> dan mengunduh bukti transfer resmi untuk pembukuan vendor.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 4: FAQ & TROUBLESHOOTING               -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'faq'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
        <!-- Subheader -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs">
            <h3 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                <flux:icon icon="question-mark-circle" class="size-5 text-indigo-600 dark:text-indigo-400" />
                Pertanyaan yang Sering Diajukan (FAQ & Solusi Masalah)
            </h3>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 mt-1">
                Temukan jawaban cepat atas kendala operasional, aturan sistem, dan penanganan ketidaksesuaian dokumen.
            </p>
        </div>

        <!-- FAQ Items (Accordion Style) -->
        <div class="space-y-3">
            <!-- FAQ 1 -->
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 shadow-xs overflow-hidden">
                <button
                    type="button"
                    @click="toggleFaq(1)"
                    class="w-full p-4 text-left font-bold text-xs sm:text-sm text-slate-900 dark:text-white flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-zinc-800/50 transition cursor-pointer"
                >
                    <span class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-indigo-500"></span>
                        Bagaimana jika makanan yang dikirim ada yang basi, tumpah, atau kurang kuantitasnya?
                    </span>
                    <flux:icon icon="chevron-down" class="size-4 text-slate-400 transition-transform duration-200" ::class="openFaq === 1 ? 'rotate-180' : ''" />
                </button>
                <div x-show="openFaq === 1" x-collapse class="px-4 pb-4 text-xs sm:text-sm text-slate-600 dark:text-zinc-400 space-y-2 border-t border-slate-100 dark:border-zinc-800/80 pt-3">
                    <p>SPV TC cukup memasukkan angka riil barang yang layak diterima pada kolom <strong>Qty Diterima</strong>. Sistem secara otomatis menghitung selisih kuantitas dengan label <strong class="text-rose-600">Kurang [X]</strong>.</p>
                    <p>Vendor <strong>hanya dapat menagihkan barang yang benar-benar diterima dalam kondisi layak</strong>. Sisa kuota PO tidak akan terpotong untuk barang yang rusak/kurang, sehingga vendor dapat mengirimkan penggantinya pada sesi berikutnya selama kuota PO masih ada.</p>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 shadow-xs overflow-hidden">
                <button
                    type="button"
                    @click="toggleFaq(2)"
                    class="w-full p-4 text-left font-bold text-xs sm:text-sm text-slate-900 dark:text-white flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-zinc-800/50 transition cursor-pointer"
                >
                    <span class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-indigo-500"></span>
                        Mengapa pengiriman saya tidak muncul saat membuka form "Ajukan Tagihan Baru"?
                    </span>
                    <flux:icon icon="chevron-down" class="size-4 text-slate-400 transition-transform duration-200" ::class="openFaq === 2 ? 'rotate-180' : ''" />
                </button>
                <div x-show="openFaq === 2" x-collapse class="px-4 pb-4 text-xs sm:text-sm text-slate-600 dark:text-zinc-400 space-y-2 border-t border-slate-100 dark:border-zinc-800/80 pt-3">
                    <p>Pengiriman (Surat Jalan DO) hanya bisa ditagihkan jika statusnya sudah <strong class="text-emerald-600">Terkonfirmasi</strong> oleh SPV Training Center.</p>
                    <p>Jika pengiriman Anda masih berstatus <strong class="text-amber-600">Menunggu Verifikasi SPV</strong>, harap hubungi Tim Pengadaan atau SPV di lokasi pelatihan untuk segera melakukan konfirmasi penerimaan fisik di sistem.</p>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 shadow-xs overflow-hidden">
                <button
                    type="button"
                    @click="toggleFaq(3)"
                    class="w-full p-4 text-left font-bold text-xs sm:text-sm text-slate-900 dark:text-white flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-zinc-800/50 transition cursor-pointer"
                >
                    <span class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-indigo-500"></span>
                        Mengapa Nilai Realisasi Barang Diterima di Dashboard sempat Rp 0 padahal DO sudah dibuat?
                    </span>
                    <flux:icon icon="chevron-down" class="size-4 text-slate-400 transition-transform duration-200" ::class="openFaq === 3 ? 'rotate-180' : ''" />
                </button>
                <div x-show="openFaq === 3" x-collapse class="px-4 pb-4 text-xs sm:text-sm text-slate-600 dark:text-zinc-400 space-y-2 border-t border-slate-100 dark:border-zinc-800/80 pt-3">
                    <p>Nilai Realisasi Barang Diterima di Dashboard hanya mengakui barang yang <strong>telah diverifikasi fisik dan dikonfirmasi</strong> oleh SPV TC. Pembuatan DO oleh vendor adalah sebatas klaim pengiriman.</p>
                    <p>Segera setelah SPV mengklik tombol <strong>Konfirmasi Diterima</strong> pada detail DO, rumus otomatis $\text{Qty Diterima} \times \text{Harga Satuan PO}$ akan terhitung dan angka di Dashboard seketika ter-update.</p>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 shadow-xs overflow-hidden">
                <button
                    type="button"
                    @click="toggleFaq(4)"
                    class="w-full p-4 text-left font-bold text-xs sm:text-sm text-slate-900 dark:text-white flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-zinc-800/50 transition cursor-pointer"
                >
                    <span class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-indigo-500"></span>
                        Bagaimana cara kerja fitur Kamera Smartphone saat mengunggah Surat Jalan?
                    </span>
                    <flux:icon icon="chevron-down" class="size-4 text-slate-400 transition-transform duration-200" ::class="openFaq === 4 ? 'rotate-180' : ''" />
                </button>
                <div x-show="openFaq === 4" x-collapse class="px-4 pb-4 text-xs sm:text-sm text-slate-600 dark:text-zinc-400 space-y-2 border-t border-slate-100 dark:border-zinc-800/80 pt-3">
                    <p>Pada form upload pengiriman, tersedia tombol <strong>📸 Buka Kamera (Foto Fisik)</strong>. Saat diklik di browser smartphone (Chrome, Safari, Edge), perangkat Anda langsung menyalakan kamera belakang.</p>
                    <p>Pastikan browser memiliki izin akses kamera (*Camera Permission: Allow*). Setelah memotret, sistem langsung menampilkan pratinjau foto sebelum disimpan.</p>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 shadow-xs overflow-hidden">
                <button
                    type="button"
                    @click="toggleFaq(5)"
                    class="w-full p-4 text-left font-bold text-xs sm:text-sm text-slate-900 dark:text-white flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-zinc-800/50 transition cursor-pointer"
                >
                    <span class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-indigo-500"></span>
                        Apakah Vendor bisa mengirim barang melebihi kuota PO yang sudah ditetapkan?
                    </span>
                    <flux:icon icon="chevron-down" class="size-4 text-slate-400 transition-transform duration-200" ::class="openFaq === 5 ? 'rotate-180' : ''" />
                </button>
                <div x-show="openFaq === 5" x-collapse class="px-4 pb-4 text-xs sm:text-sm text-slate-600 dark:text-zinc-400 space-y-2 border-t border-slate-100 dark:border-zinc-800/80 pt-3">
                    <p><strong>Tidak bisa.</strong> Sistem TC-Procure membatasi secara ketat (*hard cap enforcement*). Jika kuota PO telah habis, vendor tidak dapat membuat DO baru untuk PO tersebut.</p>
                    <p>Jika ada kebutuhan snack tambahan di luar PO, Tim TC harus menerbitkan PO Kontrak Baru atau melakukan addendum kontrak terlebih dahulu.</p>
                </div>
            </div>
        </div>

        <!-- Helpdesk Contact Card -->
        <div class="mt-8 bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-2xl p-6 border border-slate-800 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="space-y-1.5 text-center sm:text-left">
                <div class="inline-flex items-center gap-2 text-xs font-bold text-indigo-300">
                    <flux:icon icon="lifebuoy" class="size-4" />
                    Bantuan Tambahan & Kontak Administrator
                </div>
                <h4 class="text-base sm:text-lg font-bold">Memerlukan bantuan teknis atau koordinasi pengadaan?</h4>
                <p class="text-xs text-indigo-200/80">Tim Pengadaan Training Center dan Tim IT siap melayani setiap hari kerja.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <div class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-center">
                    <span class="block text-[10px] text-indigo-300 uppercase font-bold tracking-wider">Jam Operasional</span>
                    <span class="font-extrabold text-xs">Senin - Jumat (08:00 - 17:00 WIB)</span>
                </div>
            </div>
        </div>
    </div>
</div>

