<div class="space-y-6 max-w-7xl mx-auto pb-12 w-full min-w-0">
    <!-- Header Hero Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="min-w-0">
            <div class="flex items-start sm:items-center gap-2.5 sm:gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white shadow-md shadow-indigo-600/20 shrink-0">
                    <flux:icon icon="document-chart-bar" class="size-5 text-white" />
                </div>
                <div class="min-w-0 flex-1">
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white">Pusat Laporan & Ekspor</h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Cetak dan unduh rekapitulasi data pengadaan ke format Excel (.xlsx) dan PDF resmi ber-tata letak rapi.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <!-- Modal Pop-Up Filter Trigger -->
            <x-report-export-modal
                :default-type="$reportType"
                button-label="Filter & Unduh Pop-up"
                wrapper-class="w-full sm:w-auto"
                trigger-class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition transform active:scale-95 cursor-pointer"
            />
        </div>
    </div>

    <!-- Top KPI Highlights -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs space-y-1">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Total Nilai PO Aktif</span>
            <div class="text-lg sm:text-xl font-black text-slate-900 dark:text-white">
                Rp {{ number_format($stats['total_po_value'], 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-indigo-600 dark:text-indigo-400 font-semibold">Plafon Kontrak Berjalan</span>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs space-y-1">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Total Realisasi Fisik</span>
            <div class="text-lg sm:text-xl font-black text-emerald-600 dark:text-emerald-400">
                Rp {{ number_format($stats['total_delivered_value'], 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-slate-500">Barang Diterima Tim TC</span>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs space-y-1">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Total Nilai Tagihan</span>
            <div class="text-lg sm:text-xl font-black text-amber-600 dark:text-amber-400">
                Rp {{ number_format($stats['total_invoiced_value'], 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-slate-500">Invoice Diajukan Rekanan</span>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs space-y-1">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Total Lunas SAP</span>
            <div class="text-lg sm:text-xl font-black text-purple-600 dark:text-purple-400">
                Rp {{ number_format($stats['total_paid_value'], 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-emerald-600 font-semibold">Pelunasan Riil Tercatat</span>
        </div>
    </div>

    <!-- Report Type Selector Tabs -->
    <div class="border-b border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-xl p-1.5 shadow-xs">
        <div class="flex items-center gap-1 sm:gap-2 overflow-x-auto no-scrollbar">
            <button
                type="button"
                wire:click="$set('reportType', 'po')"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold whitespace-nowrap transition cursor-pointer {{ $reportType === 'po' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800' }}"
            >
                <flux:icon icon="clipboard-document-list" class="size-4" />
                <span>Purchase Order (PO)</span>
            </button>

            <button
                type="button"
                wire:click="$set('reportType', 'deliveries')"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold whitespace-nowrap transition cursor-pointer {{ $reportType === 'deliveries' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800' }}"
            >
                <flux:icon icon="truck" class="size-4" />
                <span>Pengiriman (DO)</span>
            </button>

            <button
                type="button"
                wire:click="$set('reportType', 'invoices')"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold whitespace-nowrap transition cursor-pointer {{ $reportType === 'invoices' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800' }}"
            >
                <flux:icon icon="document-text" class="size-4" />
                <span>Tagihan (Invoice)</span>
            </button>

            <button
                type="button"
                wire:click="$set('reportType', 'postings')"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold whitespace-nowrap transition cursor-pointer {{ $reportType === 'postings' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800' }}"
            >
                <flux:icon icon="check-badge" class="size-4" />
                <span>Dokumen Posting SAP</span>
            </button>

            @if (! $isVendor)
                <button
                    type="button"
                    wire:click="$set('reportType', 'vendor_balances')"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold whitespace-nowrap transition cursor-pointer {{ $reportType === 'vendor_balances' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800' }}"
                >
                    <flux:icon icon="building-storefront" class="size-4" />
                    <span>Rekap Saldo Rekanan</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Live Filter Bar & Quick Actions -->
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-zinc-800 pb-3">
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                    <flux:icon icon="funnel" class="size-4 text-indigo-600" />
                    <span>Filter & Kriteria Cetak</span>
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Filter di bawah langsung menyaring data pratinjau dan hasil unduhan file.</p>
            </div>

            <!-- Quick Date Shortcut Pills -->
            <div class="flex items-center gap-1.5 flex-wrap text-xs">
                <span class="text-[11px] text-slate-400 font-medium">Periode Cepat:</span>
                <button type="button" wire:click="setDateShortcut('today')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-semibold text-slate-700 dark:text-zinc-300 transition cursor-pointer">Hari Ini</button>
                <button type="button" wire:click="setDateShortcut('this_month')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-semibold text-slate-700 dark:text-zinc-300 transition cursor-pointer">Bulan Ini</button>
                <button type="button" wire:click="setDateShortcut('this_year')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-semibold text-slate-700 dark:text-zinc-300 transition cursor-pointer">Tahun Ini</button>
                <button type="button" wire:click="setDateShortcut('all')" class="px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 text-xs font-bold text-indigo-700 dark:text-indigo-300 transition cursor-pointer">Semua Waktu</button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Tanggal Dari -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Dari Tanggal</label>
                <input
                    type="date"
                    wire:model.live="date_from"
                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                />
            </div>

            <!-- Tanggal Sampai -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Sampai Tanggal</label>
                <input
                    type="date"
                    wire:model.live="date_to"
                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                />
            </div>

            <!-- Vendor Filter -->
            @if (! $isVendor)
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Rekanan Vendor</label>
                    <select
                        wire:model.live="vendor_id"
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    >
                        <option value="all">Semua Rekanan (Tanpa Filter)</option>
                        @foreach ($vendors as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Status Filter -->
            @if ($reportType !== 'vendor_balances' && $reportType !== 'postings')
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status</label>
                    <select
                        wire:model.live="status"
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    >
                        <option value="all">Semua Status (Tanpa Filter)</option>
                        @if ($reportType === 'po')
                            <option value="active">Aktif</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                            <option value="draft">Draft</option>
                        @elseif ($reportType === 'deliveries')
                            <option value="submitted">Menunggu Verifikasi Tim TC</option>
                            <option value="confirmed">Terkonfirmasi Tim TC</option>
                            <option value="rejected">Ditolak</option>
                        @elseif ($reportType === 'invoices')
                            <option value="submitted">Diajukan (Menunggu Tim TC)</option>
                            <option value="approved">Disetujui Tim TC</option>
                            <option value="in_payment">Dalam Proses Bayar</option>
                            <option value="paid">Lunas (Paid)</option>
                            <option value="rejected">Ditolak</option>
                        @endif
                    </select>
                </div>
            @endif
        </div>

        <!-- Filter Status Summary & Export Download Buttons -->
        <div class="pt-3 border-t border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-zinc-400">
                <span>Filter Aktif: <strong class="text-slate-800 dark:text-zinc-200">{{ $filterDesc }}</strong></span>
                <button
                    type="button"
                    wire:click="resetFilters"
                    class="font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 hover:underline cursor-pointer ml-1"
                >
                    ✕ Reset Tanpa Filter
                </button>
            </div>

            <!-- Direct Download Buttons from current filters -->
            <div class="flex items-center gap-2">
                <!-- Excel Download Form -->
                <form action="{{ route('reports.export') }}" method="POST" target="_blank" class="inline">
                    @csrf
                    <input type="hidden" name="format" value="excel">
                    <input type="hidden" name="report_type" value="{{ $reportType }}">
                    <input type="hidden" name="date_from" value="{{ $date_from }}">
                    <input type="hidden" name="date_to" value="{{ $date_to }}">
                    <input type="hidden" name="vendor_id" value="{{ $vendor_id }}">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-xs transition transform active:scale-95 cursor-pointer"
                        title="Unduh file Excel (.xlsx)"
                    >
                        <flux:icon icon="table-cells" class="size-4" />
                        <span>Unduh Excel</span>
                    </button>
                </form>

                <!-- PDF Download Form -->
                <form action="{{ route('reports.export') }}" method="POST" target="_blank" class="inline">
                    @csrf
                    <input type="hidden" name="format" value="pdf">
                    <input type="hidden" name="inline" value="0">
                    <input type="hidden" name="report_type" value="{{ $reportType }}">
                    <input type="hidden" name="date_from" value="{{ $date_from }}">
                    <input type="hidden" name="date_to" value="{{ $date_to }}">
                    <input type="hidden" name="vendor_id" value="{{ $vendor_id }}">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-xs transition transform active:scale-95 cursor-pointer"
                        title="Unduh file PDF (.pdf)"
                    >
                        <flux:icon icon="document-text" class="size-4" />
                        <span>Unduh PDF</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Live Preview Table Card -->
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/50 dark:bg-zinc-800/40 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                <span>Pratinjau Data: {{ $reportTitle }}</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60">
                    {{ count($data) }} Baris Data
                </span>
            </h3>
            <span class="text-xs text-slate-400">Menampilkan data sesuai filter yang dipilih</span>
        </div>

        <div class="w-full max-w-full overflow-x-auto">
            <!-- 1. PO Preview Table -->
            @if ($reportType === 'po')
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50/60 dark:bg-zinc-800/40 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <th class="px-5 py-3 text-center">No</th>
                            <th class="px-5 py-3">Nomor PO</th>
                            <th class="px-5 py-3">Vendor</th>
                            <th class="px-5 py-3 text-center">Tgl PO</th>
                            <th class="px-5 py-3 text-right">Plafon PO</th>
                            <th class="px-5 py-3 text-right">Realisasi Diterima</th>
                            <th class="px-5 py-3 text-right">Sisa Kuota</th>
                            <th class="px-5 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        @forelse ($data as $idx => $po)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                                <td class="px-5 py-3 text-center text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-5 py-3 font-bold text-indigo-600 dark:text-indigo-400">
                                    <a href="{{ route('purchase-orders.show', $po) }}" wire:navigate class="hover:underline">{{ $po->po_number }}</a>
                                </td>
                                <td class="px-5 py-3 font-semibold text-slate-800 dark:text-zinc-200">{{ $po->vendor?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-center text-slate-500">{{ $po->po_date->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 text-right font-black text-slate-900 dark:text-white">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right text-emerald-600 font-bold">Rp {{ number_format($po->total_amount_delivered, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right text-amber-600 font-bold">Rp {{ number_format($po->total_amount_remaining, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-center">
                                    <flux:badge size="sm" :color="$po->status->badgeColor()">{{ $po->status->label() }}</flux:badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-slate-400">Tidak ada data Purchase Order sesuai kriteria filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            <!-- 2. Deliveries Preview Table -->
            @elseif ($reportType === 'deliveries')
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50/60 dark:bg-zinc-800/40 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <th class="px-5 py-3 text-center">No</th>
                            <th class="px-5 py-3">Kode DO</th>
                            <th class="px-5 py-3">No SJ Vendor</th>
                            <th class="px-5 py-3">PO Acuan</th>
                            <th class="px-5 py-3">Vendor</th>
                            <th class="px-5 py-3 text-center">Tgl Kirim</th>
                            <th class="px-5 py-3 text-right">Qty Kirim</th>
                            <th class="px-5 py-3 text-right">Qty Diterima</th>
                            <th class="px-5 py-3 text-center">Status TC</th>
                            <th class="px-5 py-3 text-center">Penagihan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        @forelse ($data as $idx => $dlv)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                                <td class="px-5 py-3 text-center text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-5 py-3 font-bold text-indigo-600 dark:text-indigo-400">
                                    <a href="{{ route('deliveries.show', $dlv) }}" wire:navigate class="hover:underline">{{ $dlv->code }}</a>
                                </td>
                                <td class="px-5 py-3 font-mono text-slate-600 dark:text-zinc-400">{{ $dlv->vendor_do_number ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-zinc-400">{{ $dlv->purchaseOrder?->po_number ?? '-' }}</td>
                                <td class="px-5 py-3 font-semibold text-slate-800 dark:text-zinc-200">{{ $dlv->vendor?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-center text-slate-500">{{ $dlv->delivery_date->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 text-right font-medium text-slate-700 dark:text-zinc-300">{{ number_format($dlv->total_qty_sent, 2, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right font-black text-emerald-600">{{ number_format($dlv->total_qty_received, 2, ',', '.') }}</td>
                                <td class="px-5 py-3 text-center">
                                    <flux:badge size="sm" :color="$dlv->status->badgeColor()">{{ $dlv->status->label() }}</flux:badge>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $dlv->isFullyInvoiced() ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                                        {{ $dlv->isFullyInvoiced() ? 'Sudah Ditagih' : 'Belum Ditagih' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-10 text-center text-slate-400">Tidak ada data pengiriman fisik sesuai kriteria filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            <!-- 3. Invoices Preview Table -->
            @elseif ($reportType === 'invoices')
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50/60 dark:bg-zinc-800/40 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <th class="px-5 py-3 text-center">No</th>
                            <th class="px-5 py-3">Nomor Invoice</th>
                            <th class="px-5 py-3">PO Acuan</th>
                            <th class="px-5 py-3">Vendor</th>
                            <th class="px-5 py-3 text-center">Tgl Invoice</th>
                            <th class="px-5 py-3 text-center">Jatuh Tempo</th>
                            <th class="px-5 py-3 text-right">Total Tagihan</th>
                            <th class="px-5 py-3 text-center">3-Way Match</th>
                            <th class="px-5 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        @forelse ($data as $idx => $inv)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                                <td class="px-5 py-3 text-center text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-5 py-3 font-bold text-indigo-600 dark:text-indigo-400">
                                    <a href="{{ route('invoices.show', $inv) }}" wire:navigate class="hover:underline">{{ $inv->invoice_number }}</a>
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-zinc-400">{{ $inv->purchaseOrder?->po_number ?? '-' }}</td>
                                <td class="px-5 py-3 font-semibold text-slate-800 dark:text-zinc-200">{{ $inv->vendor?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-center text-slate-500">{{ $inv->invoice_date->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 text-center text-slate-500">
                                    {{ $inv->due_date?->format('d/m/Y') ?? '-' }}
                                    @if ($inv->isOverdue())
                                        <span class="block text-[10px] font-bold text-rose-600">Overdue ({{ $inv->days_overdue }}h)</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right font-black text-slate-900 dark:text-white">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $inv->isThreeWayMatched() ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' }}">
                                        {{ $inv->isThreeWayMatched() ? '✓ Match Valid' : '✕ Anomali' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <flux:badge size="sm" :color="$inv->status->badgeColor()">{{ $inv->status->label() }}</flux:badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-10 text-center text-slate-400">Tidak ada data Tagihan Invoice sesuai kriteria filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            <!-- 4. Postings Preview Table -->
            @elseif ($reportType === 'postings')
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50/60 dark:bg-zinc-800/40 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <th class="px-5 py-3 text-center">No</th>
                            <th class="px-5 py-3">Nomor Posting SAP</th>
                            <th class="px-5 py-3 text-center">Tanggal Posting</th>
                            <th class="px-5 py-3 text-right">Realisasi Bayar</th>
                            <th class="px-5 py-3">Nomor PR</th>
                            <th class="px-5 py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        @forelse ($data as $idx => $pst)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                                <td class="px-5 py-3 text-center text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-5 py-3 font-bold text-indigo-600 dark:text-indigo-400">
                                    <a href="{{ route('postings.show', $pst) }}" wire:navigate class="hover:underline">{{ $pst->posting_number }}</a>
                                </td>
                                <td class="px-5 py-3 text-center text-slate-500">{{ $pst->posting_date->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 text-right font-black text-emerald-600">Rp {{ number_format($pst->total_paid, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-slate-700 dark:text-zinc-300">{{ $pst->paymentRequests->pluck('number')->implode(', ') ?: '-' }}</td>
                                <td class="px-5 py-3 text-slate-500 italic">{{ $pst->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-400">Tidak ada data Dokumen Posting SAP sesuai kriteria filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            <!-- 5. Vendor Balances Preview Table -->
            @elseif ($reportType === 'vendor_balances')
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50/60 dark:bg-zinc-800/40 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <th class="px-5 py-3 text-center">No</th>
                            <th class="px-5 py-3">Nama Rekanan Vendor</th>
                            <th class="px-5 py-3 text-center">PO Aktif</th>
                            <th class="px-5 py-3 text-right">Plafon PO</th>
                            <th class="px-5 py-3 text-right">Diterima Fisik</th>
                            <th class="px-5 py-3 text-right">Sisa Kuota</th>
                            <th class="px-5 py-3 text-right">Total Ditagih</th>
                            <th class="px-5 py-3 text-right">Lunas SAP</th>
                            <th class="px-5 py-3 text-right">Sisa Tagihan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        @forelse ($data as $idx => $v)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                                <td class="px-5 py-3 text-center text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-5 py-3 font-bold text-slate-900 dark:text-white">{{ $v['name'] }}</td>
                                <td class="px-5 py-3 text-center font-bold text-slate-600 dark:text-zinc-400">{{ $v['active_po_count'] }}</td>
                                <td class="px-5 py-3 text-right font-medium">Rp {{ number_format($v['po_value'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right font-bold text-emerald-600">Rp {{ number_format($v['delivered_value'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right font-medium text-amber-600">Rp {{ number_format($v['remaining_value'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right font-medium">Rp {{ number_format($v['invoiced_value'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right font-black text-purple-600">Rp {{ number_format($v['paid_value'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right font-black {{ $v['unpaid_value'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    Rp {{ number_format($v['unpaid_value'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-10 text-center text-slate-400">Tidak ada data rekanan vendor.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

