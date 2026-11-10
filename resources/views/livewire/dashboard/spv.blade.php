<div class="space-y-6 w-full max-w-full min-w-0">
    {{-- Header & Quick Actions (Executive Hero Banner) --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-5 sm:p-8 text-white shadow-lg border border-slate-800">
        {{-- Subtle ambient glow --}}
        <div class="absolute -right-12 -top-12 size-64 rounded-full bg-indigo-500/15 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-12 size-64 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium backdrop-blur-md border border-white/10 text-indigo-200">
                    <span class="size-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Monitoring Real-Time Training Center</span>
                    <span class="text-white/40">•</span>
                    <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Dashboard Pengadaan Training Center
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Ringkasan eksekutif pemenuhan snack & catering external. Pantau batas kuota PO, verifikasi fisik Surat Jalan (DO), dan validasi 3-Way Match tagihan secara terpusat.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                <a href="{{ route('purchase-orders.create') }}" wire:navigate class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-600/30 transition transform active:scale-95 border border-indigo-400/30">
                    <flux:icon icon="plus" class="size-4" />
                    <span>Terbitkan PO Baru</span>
                </a>
                <a href="{{ route('invoices.index') }}" wire:navigate class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm backdrop-blur-md border border-white/15 transition transform active:scale-95">
                    <flux:icon icon="clipboard-document-check" class="size-4" />
                    <span>Verifikasi Tagihan</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Pending Action Alert Cards (Prioritas Kerja Hari Ini) --}}
    <div class="space-y-2.5">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400 flex items-center gap-1.5">
                <flux:icon icon="bolt" class="size-3.5 text-amber-500" />
                Prioritas Tindakan Operasional
            </h2>
            <span class="text-[11px] text-slate-400">Tindakan tertunda yang memerlukan keputusan</span>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            {{-- DO Menunggu Konfirmasi --}}
            <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 border border-amber-200/80 dark:border-amber-900/40 p-5 shadow-xs hover:shadow-md transition">
                <div class="absolute top-0 left-0 h-1 w-full bg-gradient-to-r from-amber-500 to-amber-400"></div>
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <span class="text-xs font-semibold uppercase text-amber-700 dark:text-amber-400">DO Menunggu Konfirmasi</span>
                        <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            {{ $pendingDeliveriesCount }}
                        </div>
                        <p class="text-xs text-slate-500 dark:text-zinc-400">Penerimaan fisik barang dari vendor</p>
                    </div>
                    <div class="flex size-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400 border border-amber-200/50 dark:border-amber-900/50">
                        <flux:icon icon="truck" class="size-5" />
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                    <span class="text-[11px] text-amber-700 dark:text-amber-400 font-medium">Perlu verifikasi fisik</span>
                    <a href="{{ route('deliveries.index', ['status' => 'submitted']) }}" wire:navigate class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-400">
                        <span>Periksa DO</span>
                        <flux:icon icon="arrow-right" class="size-3.5" />
                    </a>
                </div>
            </div>

            {{-- Tagihan Menunggu 3-Way Match --}}
            <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 border border-indigo-200/80 dark:border-indigo-900/40 p-5 shadow-xs hover:shadow-md transition">
                <div class="absolute top-0 left-0 h-1 w-full bg-gradient-to-r from-indigo-500 to-indigo-400"></div>
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <span class="text-xs font-semibold uppercase text-indigo-700 dark:text-indigo-400">Invoice Menunggu 3-Way Match</span>
                        <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            {{ $pendingInvoicesCount }}
                        </div>
                        <p class="text-xs text-slate-500 dark:text-zinc-400">Tagihan baru diajukan rekanan</p>
                    </div>
                    <div class="flex size-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-900/50">
                        <flux:icon icon="document-text" class="size-5" />
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                    <span class="text-[11px] text-indigo-700 dark:text-indigo-400 font-medium">Validasi PO, DO & Tarif</span>
                    <a href="{{ route('invoices.index', ['status' => 'submitted']) }}" wire:navigate class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                        <span>Cocokkan</span>
                        <flux:icon icon="arrow-right" class="size-3.5" />
                    </a>
                </div>
            </div>

            {{-- Payment Request Aktif / Menunggu Posting --}}
            <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 border border-sky-200/80 dark:border-sky-900/40 p-5 shadow-xs hover:shadow-md transition">
                <div class="absolute top-0 left-0 h-1 w-full bg-gradient-to-r from-sky-500 to-sky-400"></div>
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <span class="text-xs font-semibold uppercase text-sky-700 dark:text-sky-400">PR Dalam Proses (Belum Posting)</span>
                        <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            {{ $unpostedPrsCount }}
                        </div>
                        <p class="text-xs text-slate-500 dark:text-zinc-400">Proses persetujuan & antrean kasir</p>
                    </div>
                    <div class="flex size-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400 border border-sky-200/50 dark:border-sky-900/50">
                        <flux:icon icon="banknotes" class="size-5" />
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                    <span class="text-[11px] text-sky-700 dark:text-sky-400 font-medium">Tahapan persetujuan aktif</span>
                    <a href="{{ route('payment-requests.index', ['posting_status' => 'unposted']) }}" wire:navigate class="inline-flex items-center gap-1 text-xs font-semibold text-sky-600 hover:text-sky-700 dark:text-sky-400">
                        <span>Lihat Berkas</span>
                        <flux:icon icon="arrow-right" class="size-3.5" />
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Global Procurement Financial KPIs (4 Cards) --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- KPI 1: Plafon Kontrak PO --}}
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">
                <span>Nilai Kontrak PO</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">
                    {{ $activePoCount }} PO
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Rp {{ number_format($totalPoValue, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Total komitmen plafon berjalan</p>
            </div>
            <div class="mt-4">
                <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-zinc-800 overflow-hidden">
                    <div class="h-full bg-slate-700 dark:bg-zinc-400 rounded-full" style="width: 100%"></div>
                </div>
            </div>
        </div>

        {{-- KPI 2: Realisasi Barang Diterima --}}
        @php
            $deliveredPct = $totalPoValue > 0 ? min(100, round(($totalDeliveredValue / $totalPoValue) * 100, 1)) : 0;
            $remainingPct = $totalPoValue > 0 ? max(0, min(100, round(($totalRemainingValue / $totalPoValue) * 100, 1))) : 0;
            $paidPct = $totalDeliveredValue > 0 ? min(100, round(($totalPaidValue / $totalDeliveredValue) * 100, 1)) : 0;
        @endphp
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">
                <span>Realisasi Diterima</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/50">
                    {{ $deliveredPct }}%
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
                    Rp {{ number_format($totalDeliveredValue, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Fisik barang terkonfirmasi SPV</p>
            </div>
            <div class="mt-4">
                <div class="h-1.5 w-full rounded-full bg-emerald-100 dark:bg-emerald-950/40 overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ $deliveredPct }}%"></div>
                </div>
            </div>
        </div>

        {{-- KPI 3: Sisa Kuota Belum Dikirim --}}
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">
                <span>Sisa Kuota Belum Kirim</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/50">
                    {{ $remainingPct }}%
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight">
                    Rp {{ number_format($totalRemainingValue, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Dapat dikirim sampai PO berakhir</p>
            </div>
            <div class="mt-4">
                <div class="h-1.5 w-full rounded-full bg-amber-100 dark:bg-amber-950/40 overflow-hidden">
                    <div class="h-full bg-amber-500 rounded-full transition-all duration-500" style="width: {{ $remainingPct }}%"></div>
                </div>
            </div>
        </div>

        {{-- KPI 4: Total Lunas SAP Posting --}}
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-xs font-semibold text-indigo-700 dark:text-indigo-400 uppercase tracking-wider">
                <span>Total Lunas SAP</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/50">
                    Lunas
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 tracking-tight">
                    Rp {{ number_format($totalPaidValue, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Pelunasan riil tercatat di SAP</p>
            </div>
            <div class="mt-4">
                <div class="h-1.5 w-full rounded-full bg-indigo-100 dark:bg-indigo-950/40 overflow-hidden">
                    <div class="h-full bg-indigo-600 rounded-full transition-all duration-500" style="width: {{ $paidPct }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan Kinerja & Saldo Per Rekanan (Vendor Table) --}}
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/50 dark:bg-zinc-900/50">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <flux:icon icon="building-storefront" class="size-5 text-indigo-600 dark:text-indigo-400" />
                    Status Saldo Pengadaan Per Rekanan (Vendor)
                </h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                    Monitoring batas kuota kontrak PO, realisasi pengiriman fisik, akumulasi tagihan, dan sisa kewajiban per vendor
                </p>
            </div>
            <a href="{{ route('purchase-orders.index') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-white dark:bg-zinc-800 dark:text-zinc-200 border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 transition shadow-2xs">
                <span>Lihat Semua PO</span>
                <flux:icon icon="arrow-top-right-on-square" class="size-3.5" />
            </a>
        </div>

        <div class="w-full max-w-full overflow-x-auto">
            <table class="min-w-[760px] w-full divide-y divide-slate-100 text-left text-sm dark:divide-zinc-800 whitespace-nowrap">
                <thead class="bg-slate-50/80 dark:bg-zinc-800/60 text-[11px] uppercase font-bold text-slate-500 dark:text-zinc-400 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Nama Vendor</th>
                        <th class="px-4 py-3.5 text-center">PO Aktif</th>
                        <th class="px-4 py-3.5 text-right">Plafon PO</th>
                        <th class="px-4 py-3.5 text-right">Diterima Fisik</th>
                        <th class="px-4 py-3.5 text-right">Sisa Kuota</th>
                        <th class="px-4 py-3.5 text-right">Total Ditagih</th>
                        <th class="px-4 py-3.5 text-right">Lunas SAP</th>
                        <th class="px-5 py-3.5 text-right">Sisa Tagihan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse ($vendorSummaries as $v)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 font-bold text-xs dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900/50">
                                        {{ strtoupper(substr($v['name'], 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $v['name'] }}</div>
                                        <div class="text-[11px] text-slate-400">Rekanan Catering</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $v['active_po_count'] }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-slate-800 dark:text-zinc-200">
                                Rp {{ number_format($v['po_value'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($v['delivered_value'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-amber-600 dark:text-amber-400">
                                Rp {{ number_format($v['remaining_value'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-slate-800 dark:text-zinc-200">
                                Rp {{ number_format($v['invoiced_value'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-right font-bold text-indigo-600 dark:text-indigo-400">
                                Rp {{ number_format($v['paid_value'], 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-right font-semibold {{ $v['unpaid_value'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}">
                                Rp {{ number_format($v['unpaid_value'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                Belum ada data rekanan vendor aktif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Transactions Activity (Feed 2 Kolom) --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Recent Deliveries (DO) --}}
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
                        <flux:icon icon="truck" class="size-4" />
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Pengiriman Terbaru (Surat Jalan DO)</h4>
                        <span class="text-[11px] text-slate-400">Penerimaan fisik makanan & snack</span>
                    </div>
                </div>
                <a href="{{ route('deliveries.index') }}" wire:navigate class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                @forelse ($recentDeliveries as $deliv)
                    <div class="flex items-center justify-between p-4 hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-slate-900 dark:text-white">
                                    {{ $deliv->delivery_code }}
                                </span>
                                <flux:badge size="sm" :color="$deliv->status->color()">
                                    {{ $deliv->status->label() }}
                                </flux:badge>
                            </div>
                            <div class="text-xs text-slate-500 dark:text-zinc-400">
                                {{ $deliv->vendor->name ?? '-' }} • PO: {{ $deliv->purchaseOrder?->po_number ?? '-' }} • {{ $deliv->delivery_date?->format('d/m/Y') }}
                            </div>
                        </div>
                        <a href="{{ route('deliveries.show', $deliv) }}" wire:navigate class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition">
                            Detail
                        </a>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">
                        Belum ada aktivitas pengiriman.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Invoices (Tagihan) --}}
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                        <flux:icon icon="document-text" class="size-4" />
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Tagihan Terbaru (Invoice Rekanan)</h4>
                        <span class="text-[11px] text-slate-400">Pengajuan klaim pembayaran & 3-Way Match</span>
                    </div>
                </div>
                <a href="{{ route('invoices.index') }}" wire:navigate class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                @forelse ($recentInvoices as $inv)
                    <div class="flex items-center justify-between p-4 hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-slate-900 dark:text-white">
                                    {{ $inv->invoice_number }}
                                </span>
                                <flux:badge size="sm" :color="$inv->status->badgeColor()">
                                    {{ $inv->status->label() }}
                                </flux:badge>
                            </div>
                            <div class="text-xs text-slate-500 dark:text-zinc-400">
                                {{ $inv->vendor->name ?? '-' }} • <strong class="text-slate-800 dark:text-zinc-200">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</strong> • {{ $inv->invoice_date?->format('d/m/Y') }}
                            </div>
                        </div>
                        <a href="{{ route('invoices.show', $inv) }}" wire:navigate class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition">
                            Detail
                        </a>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">
                        Belum ada invoice diajukan.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
