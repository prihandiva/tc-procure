<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Breadcrumb & Back -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchase-orders.index') }}" wire:navigate class="flex size-10 items-center justify-center text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-100 bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 shadow-2xs hover:shadow-xs transition shrink-0">
                <flux:icon icon="arrow-left" class="size-4" />
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                        {{ $purchaseOrder->po_number }}
                    </h1>
                    @if ($purchaseOrder->type->value === 'non_po')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/60">
                            Non-PO
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60">
                            PO Resmi
                        </span>
                    @endif

                    @if ($purchaseOrder->is_delivery_completed)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                            Pengiriman Lengkap
                        </span>
                    @endif

                    @if ($purchaseOrder->is_paid_in_full)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-200 border border-emerald-300/60">
                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                            Lunas Penuh
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1 flex flex-wrap items-center gap-2">
                    <span>Vendor: <strong class="text-slate-800 dark:text-zinc-200">{{ $purchaseOrder->vendor->name }}</strong></span>
                    <span class="text-slate-300 dark:text-zinc-700">&bull;</span>
                    <span>Tgl PO: <strong class="text-slate-800 dark:text-zinc-200">{{ $purchaseOrder->po_date->translatedFormat('d F Y') }}</strong></span>
                </p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
            @if ($canEdit)
                <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" wire:navigate class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-bold text-amber-700 bg-amber-50 rounded-xl border border-amber-200 hover:bg-amber-100 dark:bg-amber-950/40 dark:border-amber-800/60 dark:text-amber-300 transition shadow-2xs">
                    <flux:icon icon="pencil-square" class="size-3.5" />
                    <span>Ubah PO</span>
                </a>
            @endif
        </div>
    </div>

    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm dark:bg-emerald-950/40 dark:border-emerald-800/60 dark:text-emerald-300 shadow-2xs">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600" />
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Metric Cards: Status Kuantitas & Keuangan (Executive 7 Metrics Grid) -->
    <div class="space-y-3">
        <!-- Row A: Kuota & Pemenuhan Fisik Barang -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <!-- 1. Dipesan (Kontrak PO) -->
            <div class="rounded-2xl bg-white dark:bg-zinc-900 p-4 border border-slate-200/80 dark:border-zinc-800 shadow-xs">
                <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">
                    <span>1. Plafon Dipesan</span>
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300 text-[10px]">Kontrak</span>
                </div>
                <div class="text-xl font-black text-slate-900 dark:text-white mt-2">
                    {{ number_format($purchaseOrder->total_qty_ordered, 0, ',', '.') }} <span class="text-xs font-normal text-slate-400">Porsi</span>
                </div>
                <div class="text-xs text-indigo-600 dark:text-indigo-400 font-bold mt-1">
                    Rp {{ number_format($purchaseOrder->total_amount_ordered, 0, ',', '.') }}
                </div>
            </div>

            <!-- 2. Terkirim (Realisasi DO) -->
            <div class="rounded-2xl bg-white dark:bg-zinc-900 p-4 border border-emerald-200/80 dark:border-emerald-900/40 shadow-xs">
                <div class="flex items-center justify-between text-[11px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">
                    <span>2. Diterima Fisik</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 text-[10px]">Realisasi</span>
                </div>
                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-2">
                    {{ number_format($purchaseOrder->total_qty_delivered, 0, ',', '.') }} <span class="text-xs font-normal text-emerald-500">Porsi</span>
                </div>
                <div class="text-xs text-emerald-700 dark:text-emerald-300 font-bold mt-1">
                    Rp {{ number_format($purchaseOrder->total_amount_delivered, 0, ',', '.') }}
                </div>
            </div>

            <!-- 3. Sisa Belum Kirim -->
            <div class="rounded-2xl bg-white dark:bg-zinc-900 p-4 border border-amber-200/80 dark:border-amber-900/40 shadow-xs">
                <div class="flex items-center justify-between text-[11px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider">
                    <span>3. Sisa Kuota Kirim</span>
                    <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 text-[10px]">Tersedia</span>
                </div>
                <div class="text-xl font-black text-amber-600 dark:text-amber-400 mt-2">
                    {{ number_format($purchaseOrder->total_qty_remaining, 0, ',', '.') }} <span class="text-xs font-normal text-amber-500">Porsi</span>
                </div>
                <div class="text-xs text-amber-700 dark:text-amber-300 font-bold mt-1">
                    Rp {{ number_format($purchaseOrder->total_amount_remaining, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Row B: Siklus Tagihan & Pembayaran -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- 4. Sudah Ditagih -->
            <div class="rounded-2xl bg-white dark:bg-zinc-900 p-4 border border-slate-200/80 dark:border-zinc-800 shadow-xs">
                <div class="text-[11px] font-bold text-sky-700 dark:text-sky-400 uppercase tracking-wider">4. Total Ditagih</div>
                <div class="text-base font-extrabold text-sky-600 dark:text-sky-400 mt-2 truncate">
                    Rp {{ number_format($purchaseOrder->total_amount_invoiced, 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-slate-400 mt-1">Dari invoice aktif</div>
            </div>

            <!-- 5. Belum Ditagih -->
            <div class="rounded-2xl bg-white dark:bg-zinc-900 p-4 border border-slate-200/80 dark:border-zinc-800 shadow-xs">
                <div class="text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">5. Siap Ditagih</div>
                <div class="text-base font-extrabold text-slate-800 dark:text-zinc-200 mt-2 truncate">
                    Rp {{ number_format($purchaseOrder->total_amount_uninvoiced, 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-slate-400 mt-1">Terkirim blm difaktur</div>
            </div>

            <!-- 6. Sudah Dibayar -->
            <div class="rounded-2xl bg-white dark:bg-zinc-900 p-4 border border-emerald-200/80 dark:border-emerald-900/40 shadow-xs">
                <div class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">6. Lunas SAP</div>
                <div class="text-base font-extrabold text-emerald-600 dark:text-emerald-400 mt-2 truncate">
                    Rp {{ number_format($purchaseOrder->total_amount_paid, 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-slate-400 mt-1">Kasir / SAP posted</div>
            </div>

            <!-- 7. Belum Dibayar -->
            <div class="rounded-2xl bg-white dark:bg-zinc-900 p-4 border border-slate-200/80 dark:border-zinc-800 shadow-xs">
                <div class="text-[11px] font-bold text-rose-700 dark:text-rose-400 uppercase tracking-wider">7. Sisa Bayar</div>
                <div class="text-base font-extrabold text-rose-600 dark:text-rose-400 mt-2 truncate">
                    Rp {{ number_format($purchaseOrder->total_amount_unpaid, 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-slate-400 mt-1">Tagihan dlm proses</div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 dark:border-zinc-800 flex items-center gap-2 overflow-x-auto whitespace-nowrap pb-0.5 -mx-3 sm:mx-0 px-3 sm:px-0">
        <button wire:click="$set('activeTab', 'items')" class="flex items-center gap-2 px-4 py-3 text-xs font-bold border-b-2 transition shrink-0 {{ $activeTab === 'items' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
            <flux:icon icon="cube" class="size-4" />
            <span>Daftar Item PO</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300">
                {{ $purchaseOrder->poItems->count() }}
            </span>
        </button>
        <button wire:click="$set('activeTab', 'deliveries')" class="flex items-center gap-2 px-4 py-3 text-xs font-bold border-b-2 transition shrink-0 {{ $activeTab === 'deliveries' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
            <flux:icon icon="truck" class="size-4" />
            <span>Riwayat Pengiriman (DO)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300">
                {{ $purchaseOrder->deliveries->count() }}
            </span>
        </button>
        <button wire:click="$set('activeTab', 'invoices')" class="flex items-center gap-2 px-4 py-3 text-xs font-bold border-b-2 transition shrink-0 {{ $activeTab === 'invoices' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
            <flux:icon icon="document-text" class="size-4" />
            <span>Riwayat Tagihan (Invoice)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300">
                {{ $purchaseOrder->invoices->count() }}
            </span>
        </button>
        <button wire:click="$set('activeTab', 'documents')" class="flex items-center gap-2 px-4 py-3 text-xs font-bold border-b-2 transition shrink-0 {{ $activeTab === 'documents' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
            <flux:icon icon="paper-clip" class="size-4" />
            <span>Dokumen Lampiran</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300">
                {{ $purchaseOrder->documents->count() }}
            </span>
        </button>
    </div>

    <!-- Tab 1: Item PO -->
    @if ($activeTab === 'items')
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
            <div class="overflow-x-auto -mx-3 sm:mx-0">
                <table class="min-w-[640px] w-full text-left text-sm text-slate-700 dark:text-zinc-300 divide-y divide-slate-100 dark:divide-zinc-800 whitespace-nowrap">
                    <thead class="bg-slate-50/80 dark:bg-zinc-800/60 font-bold text-slate-500 dark:text-zinc-400 text-[11px] uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5">Nama Item</th>
                            <th class="px-6 py-3.5 text-center">Satuan</th>
                            <th class="px-6 py-3.5 text-center">Dipesan</th>
                            <th class="px-6 py-3.5 text-center">Terkirim</th>
                            <th class="px-6 py-3.5 text-center">Sisa</th>
                            <th class="px-6 py-3.5 text-right">Harga Terkunci</th>
                            <th class="px-6 py-3.5 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        @foreach ($purchaseOrder->poItems as $item)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $item->item_name }}</td>
                                <td class="px-6 py-4 text-center font-mono text-xs text-slate-500">{{ $item->unit }}</td>
                                <td class="px-6 py-4 text-center font-semibold text-slate-800 dark:text-zinc-200">{{ number_format($item->qty_ordered, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center text-emerald-600 dark:text-emerald-400 font-bold">{{ number_format($item->qty_delivered, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center text-amber-600 dark:text-amber-400 font-semibold">{{ number_format($item->qty_remaining, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right text-slate-600 dark:text-zinc-400">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-bold text-slate-900 dark:text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50/80 dark:bg-zinc-800/60 font-bold">
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-right text-xs uppercase tracking-wider text-slate-500">TOTAL NILAI PLAFON PO:</td>
                            <td class="px-6 py-4 text-right text-lg font-black text-indigo-600 dark:text-indigo-400">
                                Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    <!-- Tab 2: Pengiriman / DO -->
    @if ($activeTab === 'deliveries')
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
            @if ($purchaseOrder->deliveries->isEmpty())
                <div class="text-center py-12 text-slate-400 text-sm">
                    Belum ada riwayat pengiriman untuk PO ini.
                </div>
            @else
                <div class="overflow-x-auto -mx-3 sm:mx-0">
                    <table class="min-w-[640px] w-full text-left text-sm text-slate-700 dark:text-zinc-300 divide-y divide-slate-100 dark:divide-zinc-800 whitespace-nowrap">
                        <thead class="bg-slate-50/80 dark:bg-zinc-800/60 font-bold text-slate-500 dark:text-zinc-400 text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5">Kode Pengiriman</th>
                                <th class="px-6 py-3.5">Tanggal</th>
                                <th class="px-6 py-3.5">No. DO Vendor</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                                <th class="px-6 py-3.5">Rincian Item</th>
                                <th class="px-6 py-3.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            @foreach ($purchaseOrder->deliveries as $deliv)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                                    <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $deliv->code }}</td>
                                    <td class="px-6 py-4 text-xs text-slate-500">{{ $deliv->delivery_date->format('d M Y') }}</td>
                                    <td class="px-6 py-4 text-xs font-mono">{{ $deliv->vendor_do_number ?: '-' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <flux:badge size="sm" :color="$deliv->status->color()">
                                            {{ $deliv->status->label() }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-500">{{ $deliv->deliveryItems->count() }} item barang</td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('deliveries.show', $deliv) }}" wire:navigate class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300 transition">
                                            Detail DO
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    <!-- Tab 3: Tagihan / Invoice -->
    @if ($activeTab === 'invoices')
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
            @if ($purchaseOrder->invoices->isEmpty())
                <div class="text-center py-12 text-slate-400 text-sm">
                    Belum ada tagihan/invoice yang diajukan untuk PO ini.
                </div>
            @else
                <div class="overflow-x-auto -mx-3 sm:mx-0">
                    <table class="min-w-[640px] w-full text-left text-sm text-slate-700 dark:text-zinc-300 divide-y divide-slate-100 dark:divide-zinc-800 whitespace-nowrap">
                        <thead class="bg-slate-50/80 dark:bg-zinc-800/60 font-bold text-slate-500 dark:text-zinc-400 text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5">Nomor Invoice</th>
                                <th class="px-6 py-3.5">Tanggal</th>
                                <th class="px-6 py-3.5 text-right">Nilai Tagihan</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                                <th class="px-6 py-3.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            @foreach ($purchaseOrder->invoices as $inv)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                                    <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $inv->invoice_number }}</td>
                                    <td class="px-6 py-4 text-xs text-slate-500">{{ $inv->invoice_date->format('d M Y') }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-slate-900 dark:text-white">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <flux:badge size="sm" :color="$inv->status->badgeColor()">
                                            {{ $inv->status->label() }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('invoices.show', $inv) }}" wire:navigate class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300 transition">
                                            Detail Invoice
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    <!-- Tab 4: Dokumen Lampiran -->
    @if ($activeTab === 'documents')
        <div class="space-y-6">
            @if (! $isVendor)
                <!-- Form Upload Tambahan Dokumen -->
                <div class="rounded-2xl bg-white dark:bg-zinc-900 p-6 border border-slate-200/80 dark:border-zinc-800 shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Unggah Berkas Lampiran PO Baru</h3>
                    <form wire:submit="uploadDocument" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Jenis Dokumen</label>
                                <select wire:model="document_type" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white">
                                    <option value="po">Dokumen PO Resmi</option>
                                    <option value="other">Lampiran Tambahan</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <x-file-upload-camera
                                    model="new_document"
                                    :file="$new_document"
                                    label="Pilih Berkas / Foto Dokumen"
                                    help="Foto langsung dokumen PO atau unggah file PDF/JPG dari galeri."
                                />
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-xs transition" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="uploadDocument">Unggah Dokumen</span>
                                <span wire:loading wire:target="uploadDocument">Mengunggah...</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- List of Documents -->
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs p-5 space-y-3">
                <div class="font-bold text-xs uppercase tracking-wider text-slate-500 border-b border-slate-100 dark:border-zinc-800 pb-2.5">
                    Berkas Terlampir (Tersimpan Aman di Storage)
                </div>
                <x-document-preview-card :documents="$purchaseOrder->documents" />
            </div>
        </div>
    @endif
</div>
