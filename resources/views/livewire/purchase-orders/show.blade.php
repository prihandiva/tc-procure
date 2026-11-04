<div class="p-6 max-w-7xl mx-auto space-y-6">
    <!-- Breadcrumb & Back -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchase-orders.index') }}" wire:navigate class="p-2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                &larr;
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                        {{ $purchaseOrder->po_number }}
                    </h1>
                    @if ($purchaseOrder->type->value === 'non_po')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            Non-PO
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                            PO Resmi
                        </span>
                    @endif

                    @if ($purchaseOrder->is_delivery_completed)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                            ✓ Pengiriman Lengkap
                        </span>
                    @endif

                    @if ($purchaseOrder->is_paid_in_full)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                            ★ Lunas
                        </span>
                    @endif
                </div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Vendor: <strong class="text-zinc-700 dark:text-zinc-200">{{ $purchaseOrder->vendor->name }}</strong> &bull; 
                    Tanggal PO: {{ $purchaseOrder->po_date->format('d F Y') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if ($canEdit)
                <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" wire:navigate class="px-4 py-2 text-sm font-medium text-amber-700 bg-amber-50 rounded-lg border border-amber-200 hover:bg-amber-100 dark:bg-amber-900/30 dark:border-amber-800 dark:text-amber-300 transition">
                    Ubah PO
                </a>
            @endif
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm dark:bg-green-900/30 dark:border-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    <!-- Metric Cards: Status Kuantitas & Keuangan (Section 6) -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
        <!-- Dipesan -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider">1. Dipesan</div>
            <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                {{ number_format($purchaseOrder->total_qty_ordered, 0, ',', '.') }}
            </div>
            <div class="text-xs text-blue-600 font-semibold mt-0.5">
                Rp {{ number_format($purchaseOrder->total_amount_ordered, 0, ',', '.') }}
            </div>
        </div>

        <!-- Terkirim / Konfirmasi -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider">2. Terkirim</div>
            <div class="text-lg font-bold text-green-600 mt-1">
                {{ number_format($purchaseOrder->total_qty_delivered, 0, ',', '.') }}
            </div>
            <div class="text-xs text-green-600 font-semibold mt-0.5">
                Rp {{ number_format($purchaseOrder->total_amount_delivered, 0, ',', '.') }}
            </div>
        </div>

        <!-- Sisa Belum Kirim -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider">3. Sisa Kirim</div>
            <div class="text-lg font-bold text-amber-600 mt-1">
                {{ number_format($purchaseOrder->total_qty_remaining, 0, ',', '.') }}
            </div>
            <div class="text-xs text-amber-600 font-semibold mt-0.5">
                Rp {{ number_format($purchaseOrder->total_amount_remaining, 0, ',', '.') }}
            </div>
        </div>

        <!-- Sudah Ditagih -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider">4. Ditagih</div>
            <div class="text-base font-bold text-purple-600 mt-2 truncate">
                Rp {{ number_format($purchaseOrder->total_amount_invoiced, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-zinc-400 mt-1">Dari invoice aktif</div>
        </div>

        <!-- Belum Ditagih -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider">5. Belum Ditagih</div>
            <div class="text-base font-bold text-zinc-700 dark:text-zinc-300 mt-2 truncate">
                Rp {{ number_format($purchaseOrder->total_amount_uninvoiced, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-zinc-400 mt-1">Terkirim blm ditagih</div>
        </div>

        <!-- Sudah Dibayar -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider">6. Sudah Dibayar</div>
            <div class="text-base font-bold text-emerald-600 mt-2 truncate">
                Rp {{ number_format($purchaseOrder->total_amount_paid, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-zinc-400 mt-1">Status paid/posted</div>
        </div>

        <!-- Belum Dibayar -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider">7. Belum Bayar</div>
            <div class="text-base font-bold text-red-600 mt-2 truncate">
                Rp {{ number_format($purchaseOrder->total_amount_unpaid, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-zinc-400 mt-1">Tagihan dlm proses</div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-zinc-200 dark:border-zinc-800 flex items-center gap-6">
        <button wire:click="$set('activeTab', 'items')" class="py-3 text-sm font-medium border-b-2 transition {{ $activeTab === 'items' ? 'border-blue-600 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
            Daftar Item PO ({{ $purchaseOrder->poItems->count() }})
        </button>
        <button wire:click="$set('activeTab', 'deliveries')" class="py-3 text-sm font-medium border-b-2 transition {{ $activeTab === 'deliveries' ? 'border-blue-600 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
            Riwayat Pengiriman / DO ({{ $purchaseOrder->deliveries->count() }})
        </button>
        <button wire:click="$set('activeTab', 'invoices')" class="py-3 text-sm font-medium border-b-2 transition {{ $activeTab === 'invoices' ? 'border-blue-600 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
            Riwayat Tagihan / Invoice ({{ $purchaseOrder->invoices->count() }})
        </button>
        <button wire:click="$set('activeTab', 'documents')" class="py-3 text-sm font-medium border-b-2 transition {{ $activeTab === 'documents' ? 'border-blue-600 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
            Dokumen Lampiran ({{ $purchaseOrder->documents->count() }})
        </button>
    </div>

    <!-- Tab 1: Item PO -->
    @if ($activeTab === 'items')
        <div class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-700 dark:text-zinc-300 divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 font-semibold text-zinc-600 dark:text-zinc-300">
                        <tr>
                            <th class="px-6 py-3">Nama Item</th>
                            <th class="px-6 py-3 text-center">Satuan</th>
                            <th class="px-6 py-3 text-center">Dipesan</th>
                            <th class="px-6 py-3 text-center">Terkirim</th>
                            <th class="px-6 py-3 text-center">Sisa</th>
                            <th class="px-6 py-3 text-right">Harga Terkunci (Rp)</th>
                            <th class="px-6 py-3 text-right">Subtotal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($purchaseOrder->poItems as $item)
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition">
                                <td class="px-6 py-4 font-medium text-zinc-900 dark:text-zinc-100">{{ $item->item_name }}</td>
                                <td class="px-6 py-4 text-center font-mono text-xs">{{ $item->unit }}</td>
                                <td class="px-6 py-4 text-center font-semibold">{{ number_format($item->qty_ordered, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center text-green-600 font-semibold">{{ number_format($item->qty_delivered, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center text-amber-600 font-semibold">{{ number_format($item->qty_remaining, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-zinc-50 dark:bg-zinc-800/60 font-bold">
                        <tr>
                            <td colspan="6" class="px-6 py-3 text-right text-sm">TOTAL NILAI PO:</td>
                            <td class="px-6 py-3 text-right text-base text-blue-600 dark:text-blue-400">
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
        <div class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
            @if ($purchaseOrder->deliveries->isEmpty())
                <div class="text-center py-8 text-zinc-400">
                    Belum ada riwayat pengiriman untuk PO ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-zinc-700 dark:text-zinc-300">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60 font-semibold">
                            <tr>
                                <th class="px-4 py-2">Kode Pengiriman</th>
                                <th class="px-4 py-2">Tanggal</th>
                                <th class="px-4 py-2">No. DO Vendor</th>
                                <th class="px-4 py-2 text-center">Status</th>
                                <th class="px-4 py-2">Rincian Item</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($purchaseOrder->deliveries as $deliv)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-zinc-100">{{ $deliv->code }}</td>
                                    <td class="px-4 py-3">{{ $deliv->delivery_date->format('d M Y') }}</td>
                                    <td class="px-4 py-3">{{ $deliv->vendor_do_number ?: '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $deliv->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs">{{ $deliv->deliveryItems->count() }} item</td>
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
        <div class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
            @if ($purchaseOrder->invoices->isEmpty())
                <div class="text-center py-8 text-zinc-400">
                    Belum ada tagihan/invoice yang diajukan untuk PO ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-zinc-700 dark:text-zinc-300">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60 font-semibold">
                            <tr>
                                <th class="px-4 py-2">Nomor Invoice</th>
                                <th class="px-4 py-2">Tanggal</th>
                                <th class="px-4 py-2 text-right">Nilai Tagihan</th>
                                <th class="px-4 py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($purchaseOrder->invoices as $inv)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-zinc-100">{{ $inv->invoice_number }}</td>
                                    <td class="px-4 py-3">{{ $inv->invoice_date->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ $inv->status->label() }}
                                        </span>
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
                <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-3">Unggah Berkas Lampiran PO Baru</h3>
                    <form wire:submit="uploadDocument" class="flex flex-col sm:flex-row items-end gap-3">
                        <div class="w-full sm:w-48">
                            <label class="block text-xs font-medium text-zinc-500 mb-1">Jenis Dokumen</label>
                            <select wire:model="document_type" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100">
                                <option value="po">Dokumen PO Resmi</option>
                                <option value="other">Lampiran Tambahan</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-zinc-500 mb-1">Pilih File (PDF, JPG, PNG &bull; Maks 5 MB)</label>
                            <input type="file" wire:model="new_document" class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-zinc-800 dark:file:text-zinc-300">
                            @error('new_document') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                            Unggah Dokumen
                        </button>
                    </form>
                </div>
            @endif

            <!-- List of Documents -->
            <div class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 font-semibold text-sm">
                    Berkas Terlampir (Tersimpan Aman di Private Storage)
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($purchaseOrder->documents as $doc)
                        <div class="p-4 flex items-center justify-between hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-xs font-bold uppercase">
                                    {{ pathinfo($doc->original_name, PATHINFO_EXTENSION) }}
                                </div>
                                <div>
                                    <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100">{{ $doc->original_name }}</div>
                                    <div class="text-xs text-zinc-400">
                                        {{ $doc->formatted_size }} &bull; Diunggah oleh {{ $doc->uploader->name ?? 'User' }} &bull; {{ $doc->created_at->format('d M Y H:i') }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('documents.show', $doc) }}" target="_blank" class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg dark:bg-blue-900/30 dark:text-blue-300 transition">
                                    Buka File
                                </a>
                                <a href="{{ route('documents.show', ['document' => $doc, 'download' => 1]) }}" class="px-3 py-1.5 text-xs font-medium text-zinc-700 bg-zinc-100 hover:bg-zinc-200 rounded-lg dark:bg-zinc-800 dark:text-zinc-300 transition">
                                    Unduh
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-zinc-400 text-sm">
                            Tidak ada berkas dokumen terlampir pada PO ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>

