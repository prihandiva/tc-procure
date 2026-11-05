<div class="space-y-6">
    {{-- Header & Quick Actions (Vendor Hero Banner) --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 sm:p-8 text-white shadow-lg border border-slate-800">
        <div class="absolute -right-12 -top-12 size-64 rounded-full bg-emerald-500/15 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-12 size-64 rounded-full bg-indigo-500/15 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium backdrop-blur-md border border-white/10 text-emerald-200">
                    <span class="size-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Portal Mitra Rekanan Training Center</span>
                    <span class="text-white/40">•</span>
                    <span>Monitoring Real-Time</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Portal Rekanan: {{ $vendor->name ?? 'Vendor' }}
                </h1>
                <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Kelola pengiriman Surat Jalan (DO), monitor batas plafon PO yang dapat dikirimkan, serta ajukan faktur tagihan (Invoice) secara otomatis dan transparan.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('deliveries.create') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm shadow-md shadow-emerald-600/30 transition transform active:scale-95 border border-emerald-400/30">
                    <flux:icon icon="plus" class="size-4" />
                    <span>Input Surat Jalan (DO)</span>
                </a>
                <a href="{{ route('invoices.create') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm backdrop-blur-md border border-white/15 transition transform active:scale-95">
                    <flux:icon icon="document-plus" class="size-4" />
                    <span>Buat Tagihan (Invoice)</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Alert Siap Tagih (Uninvoiced Confirmed DOs) --}}
    @if ($uninvoicedCount > 0)
        <div class="relative overflow-hidden rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60 p-5 shadow-xs">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-xs">
                        <flux:icon.check-circle class="size-5" />
                    </div>
                    <div class="space-y-0.5">
                        <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-100">
                            Pengiriman Anda Telah Diterima SPV dan Siap Ditagihkan!
                        </h3>
                        <p class="text-xs text-emerald-700 dark:text-emerald-300 leading-relaxed">
                            Terdapat <strong>{{ $uninvoicedCount }} item</strong> pengiriman yang telah diverifikasi fisik oleh Training Center dengan total estimasi tagihan <strong>Rp {{ number_format($uninvoicedAmount, 0, ',', '.') }}</strong>.
                        </p>
                    </div>
                </div>
                <div>
                    <a href="{{ route('invoices.create') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-sm transition whitespace-nowrap">
                        <span>Buat Tagihan Sekarang</span>
                        <flux:icon icon="arrow-right" class="size-3.5" />
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Financial KPI Cards for Vendor (4 Cards) --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- KPI 1: Sisa Kuota Kirim PO --}}
        @php
            $remainingPct = $totalPoValue > 0 ? max(0, min(100, round(($totalRemainingValue / $totalPoValue) * 100, 1))) : 0;
            $deliveredPct = $totalPoValue > 0 ? max(0, min(100, round((($totalPoValue - $totalRemainingValue) / $totalPoValue) * 100, 1))) : 0;
        @endphp
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">
                <span>Sisa Kuota Kirim PO</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/50">
                    {{ $remainingPct }}%
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight">
                    Rp {{ number_format($totalRemainingValue, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Plafon total: Rp {{ number_format($totalPoValue, 0, ',', '.') }}</p>
            </div>
            <div class="mt-4">
                <div class="h-1.5 w-full rounded-full bg-amber-100 dark:bg-amber-950/40 overflow-hidden">
                    <div class="h-full bg-amber-500 rounded-full" style="width: {{ $remainingPct }}%"></div>
                </div>
            </div>
        </div>

        {{-- KPI 2: Siap Ditagihkan (DO Approved) --}}
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">
                <span>Siap Ditagihkan (DO Approved)</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/50">
                    {{ $uninvoicedCount }} Item
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
                    Rp {{ number_format($uninvoicedAmount, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Barang diterima belum ditagih</p>
            </div>
            <div class="mt-4">
                <div class="h-1.5 w-full rounded-full bg-emerald-100 dark:bg-emerald-950/40 overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $uninvoicedAmount > 0 ? 100 : 0 }}%"></div>
                </div>
            </div>
        </div>

        {{-- KPI 3: Tagihan Dalam Proses --}}
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-xs font-semibold text-indigo-700 dark:text-indigo-400 uppercase tracking-wider">
                <span>Tagihan Dalam Proses</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/50">
                    Review
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 tracking-tight">
                    Rp {{ number_format($pendingApprovalAmount + $inProcessAmount, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Verifikasi SPV, ACC & Finance</p>
            </div>
            <div class="mt-4">
                <div class="h-1.5 w-full rounded-full bg-indigo-100 dark:bg-indigo-950/40 overflow-hidden">
                    <div class="h-full bg-indigo-600 rounded-full" style="width: 100%"></div>
                </div>
            </div>
        </div>

        {{-- KPI 4: Total Pembayaran Lunas --}}
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">
                <span>Total Pembayaran Lunas</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">
                    Lunas
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Rp {{ number_format($paidAmount, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Telah dibayar kasir & diposting</p>
            </div>
            <div class="mt-4">
                <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-zinc-800 overflow-hidden">
                    <div class="h-full bg-slate-700 dark:bg-zinc-400 rounded-full" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Active POs for Vendor Table --}}
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between bg-slate-50/50 dark:bg-zinc-900/50">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <flux:icon icon="clipboard-document-list" class="size-5 text-indigo-600 dark:text-indigo-400" />
                    Purchase Order (PO) Aktif Anda
                </h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">Daftar kontrak PO yang menjadi dasar pengiriman fisik harian dan penagihan berkala</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60">
                {{ $activePos->count() }} PO Aktif
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm dark:divide-zinc-800">
                <thead class="bg-slate-50/80 dark:bg-zinc-800/60 text-[11px] uppercase font-bold text-slate-500 dark:text-zinc-400 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">No. PO</th>
                        <th class="px-4 py-3.5">Tgl PO</th>
                        <th class="px-4 py-3.5 text-right">Plafon PO</th>
                        <th class="px-4 py-3.5 text-right">Terkirim</th>
                        <th class="px-4 py-3.5 text-right">Sisa Kuota</th>
                        <th class="px-4 py-3.5 text-center">Pemenuhan</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse ($activePos as $po)
                        @php
                            $delivered = $po->delivered_amount;
                            $pct = $po->total_amount > 0 ? round(($delivered / $po->total_amount) * 100) : 0;
                            $remaining = max(0, $po->total_amount - $delivered);
                        @endphp
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-5 py-4 font-bold text-slate-900 dark:text-white">
                                <div class="flex items-center gap-2">
                                    <span>{{ $po->po_number }}</span>
                                    @if ($po->isNonPo())
                                        <flux:badge color="amber" size="xs">Non-PO</flux:badge>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-500 dark:text-zinc-400">
                                {{ $po->po_date?->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-slate-800 dark:text-zinc-200">
                                Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($delivered, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-amber-600 dark:text-amber-400">
                                Rp {{ number_format($remaining, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-20 bg-slate-100 rounded-full h-2 dark:bg-zinc-800 overflow-hidden">
                                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: {{ min(100, $pct) }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-700 dark:text-zinc-300">{{ $pct }}%</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('deliveries.create', ['po_id' => $po->id]) }}" wire:navigate class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-2xs transition">
                                        Kirim DO
                                    </a>
                                    <a href="{{ route('purchase-orders.show', $po) }}" wire:navigate class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300 transition">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                Belum ada Purchase Order (PO) aktif yang diterbitkan untuk akun Anda.
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
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Pengiriman Terbaru (Surat Jalan)</h4>
                        <span class="text-[11px] text-slate-400">Status penerimaan fisik di TC</span>
                    </div>
                </div>
                <a href="{{ route('deliveries.index') }}" wire:navigate class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                    Semua DO &rarr;
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
                                PO: {{ $deliv->purchaseOrder?->po_number ?? '-' }} • Tgl Kirim: {{ $deliv->delivery_date?->format('d/m/Y') }}
                            </div>
                        </div>
                        <a href="{{ route('deliveries.show', $deliv) }}" wire:navigate class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300 transition">
                            Detail
                        </a>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">
                        Belum ada riwayat pengiriman.
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
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Tagihan Terbaru (Invoice)</h4>
                        <span class="text-[11px] text-slate-400">Status verifikasi & pembayaran</span>
                    </div>
                </div>
                <a href="{{ route('invoices.index') }}" wire:navigate class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                    Semua Invoice &rarr;
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
                                <strong class="text-slate-800 dark:text-zinc-200">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</strong> • {{ $inv->invoice_date?->format('d/m/Y') }}
                            </div>
                        </div>
                        <a href="{{ route('invoices.show', $inv) }}" wire:navigate class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300 transition">
                            Detail
                        </a>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">
                        Belum ada riwayat tagihan.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
