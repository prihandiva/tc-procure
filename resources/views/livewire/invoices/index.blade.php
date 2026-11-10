<div class="space-y-6 max-w-7xl mx-auto w-full min-w-0">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white">Tagihan & Faktur (Invoice)</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60 whitespace-nowrap shrink-0">
                    {{ $invoices->total() }} Total
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                {{ $isVendor ? 'Buat dan ajukan faktur tagihan dari pengiriman yang telah dikonfirmasi Tim TC, serta monitor status pembayaran.' : 'Verifikasi tagihan rekanan catering secara ketat melalui pencocokan 3-Way Match (PO-DO-Invoice).' }}
            </p>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
            <x-report-export-modal default-type="invoices" button-label="Ekspor Laporan" wrapper-class="w-full sm:w-auto" trigger-class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 text-xs font-bold hover:bg-slate-50 dark:hover:bg-zinc-700/60 shadow-2xs transition transform active:scale-95 cursor-pointer whitespace-nowrap" />
            @can('create', App\Models\Invoice::class)
                <a href="{{ route('invoices.create') }}" wire:navigate class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/30 transition transform active:scale-95 whitespace-nowrap">
                    <flux:icon icon="plus" class="size-4 shrink-0" />
                    <span>Buat Tagihan Baru</span>
                </a>
            @endcan
        </div>
    </div>

    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm dark:bg-emerald-950/40 dark:border-emerald-800/60 dark:text-emerald-300 shadow-2xs">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600" />
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Card Invoice Menunggu Verifikasi SPV -->
    @if ($isSpv && $pendingVerificationInvoices->isNotEmpty())
        <div class="rounded-2xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 p-4 shadow-xs space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-2xs">
                        <flux:icon icon="shield-check" class="size-4" />
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-sm font-bold text-amber-950 dark:text-amber-200">Invoice Masuk Menunggu Verifikasi (3-Way Match)</h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-500 text-white">
                                {{ $pendingVerificationInvoices->count() }} Menunggu
                            </span>
                        </div>
                        <p class="text-xs text-amber-800/80 dark:text-amber-300/80">Faktur tagihan dari rekanan yang perlu diverifikasi kecocokannya dengan PO &amp; DO.</p>
                    </div>
                </div>
                <button type="button" wire:click="$set('status', 'submitted')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold transition shadow-2xs shrink-0 self-start sm:self-auto cursor-pointer">
                    <span>Filter Menunggu Verifikasi</span>
                </button>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($pendingVerificationInvoices->take(6) as $pendingInv)
                    <a href="{{ route('invoices.show', $pendingInv) }}" wire:navigate class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-zinc-900 border border-amber-200 dark:border-amber-800/70 hover:border-amber-400 text-xs transition shadow-2xs">
                        <span class="font-mono font-bold text-amber-700 dark:text-amber-300">{{ $pendingInv->invoice_number }}</span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="font-bold text-slate-800 dark:text-zinc-200">Rp {{ number_format($pendingInv->total_amount, 0, ',', '.') }}</span>
                        <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400">Verifikasi &rarr;</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Card Daftar DO Terkonfirmasi Siap Dibuatkan Invoice -->
    @if ($readyForInvoicingPos->isNotEmpty())
        <div x-data="{ showAll: false }" class="rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/25 border border-indigo-200/80 dark:border-indigo-900/50 p-4 shadow-xs space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex items-start sm:items-center gap-2.5">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-xs">
                        <flux:icon icon="document-plus" class="size-4.5" />
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-sm font-bold text-indigo-950 dark:text-indigo-100">DO Terkonfirmasi Siap Dibuatkan Invoice</h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-600 text-white">
                                {{ $readyForInvoicingPos->count() }} PO Siap Tagih
                            </span>
                        </div>
                        <p class="text-xs text-indigo-800/80 dark:text-indigo-300/80 mt-0.5">Pengiriman berikut sudah dikonfirmasi Tim TC dan belum memiliki faktur tagihan. Klik <strong>Buat Invoice</strong> untuk langsung menagih.</p>
                    </div>
                </div>
                @if ($readyForInvoicingPos->count() > 3)
                    <button type="button" @click="showAll = !showAll" class="text-xs font-bold text-indigo-700 dark:text-indigo-300 hover:underline shrink-0 self-start sm:self-auto cursor-pointer">
                        <span x-text="showAll ? 'Tampilkan Lebih Sedikit' : 'Lihat Semua ({{ $readyForInvoicingPos->count() }})'"></span>
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($readyForInvoicingPos as $idx => $entry)
                    @php
                        $po = $entry['po'];
                        $doSummary = implode(', ', array_slice($entry['delivery_codes'], 0, 2)) . (count($entry['delivery_codes']) > 2 ? ' +' . (count($entry['delivery_codes']) - 2) . ' DO' : '');
                    @endphp
                    <div
                        wire:key="ready-inv-po-{{ $po->id }}"
                        @if ($idx >= 3) x-show="showAll" x-cloak @endif
                        class="flex items-center justify-between gap-3 p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-indigo-200/70 dark:border-zinc-800 shadow-2xs hover:border-indigo-400 dark:hover:border-indigo-700 transition"
                    >
                        <div class="min-w-0 space-y-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-mono font-bold text-xs text-slate-900 dark:text-white truncate">PO {{ $po->po_number }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                                    {{ $entry['deliveries_count'] }} DO
                                </span>
                            </div>
                            @if (! $isVendor)
                                <div class="text-[11px] font-medium text-slate-600 dark:text-zinc-300 truncate">{{ $po->vendor->name }}</div>
                            @endif
                            <div class="text-[11px] text-slate-400 truncate">
                                DO: <span class="font-mono text-slate-600 dark:text-zinc-300">{{ $doSummary }}</span> ({{ $entry['items_count'] }} item)
                            </div>
                            <div class="text-xs font-black text-indigo-600 dark:text-indigo-400">
                                Rp {{ number_format($entry['estimated_amount'], 0, ',', '.') }}
                            </div>
                        </div>

                        <a href="{{ route('invoices.create', ['po_id' => $po->id]) }}" wire:navigate class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-xs transition shrink-0 whitespace-nowrap">
                            <flux:icon icon="plus" class="size-3.5" />
                            <span>Buat Invoice</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Filter & Search -->
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-4 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Pencarian</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nomor invoice, nomor PO, vendor..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                    <flux:icon icon="magnifying-glass" class="size-4 text-slate-400 absolute left-3 top-2.5" />
                </div>
            </div>

            @if (! $isVendor)
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Vendor</label>
                    <select wire:model.live="vendor_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                        <option value="all">Semua Rekanan</option>
                        @foreach ($vendors as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status Tagihan</label>
                <select wire:model.live="status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                    <option value="all">Semua Status</option>
                    <option value="submitted">Diajukan (Menunggu Tim TC)</option>
                    <option value="approved">Disetujui Tim TC</option>
                    <option value="in_payment">Dalam Proses Bayar</option>
                    <option value="paid">Lunas (Paid)</option>
                    <option value="rejected">Ditolak</option>
                    <option value="void">Batal (Void)</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Dari Tanggal</label>
                    <input type="date" wire:model.live="date_from" class="w-full px-2 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Sampai</label>
                    <input type="date" wire:model.live="date_to" class="w-full px-2 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white">
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="w-full max-w-full rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
        <div class="w-full max-w-full overflow-x-auto">
            <table class="min-w-[760px] w-full text-left text-sm text-slate-700 dark:text-zinc-300 divide-y divide-slate-100 dark:divide-zinc-800 whitespace-nowrap">
                <thead class="bg-slate-50/80 dark:bg-zinc-800/60 font-bold text-slate-500 dark:text-zinc-400 text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Nomor Invoice</th>
                        <th class="px-6 py-3.5">PO Terkait</th>
                        @if (! $isVendor)
                            <th class="px-6 py-3.5">Vendor</th>
                        @endif
                        <th class="px-6 py-3.5">Tanggal Tagihan</th>
                        <th class="px-6 py-3.5 text-right">Total Tagihan</th>
                        <th class="px-6 py-3.5 text-center">3-Way Match</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse ($invoices as $inv)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('invoices.show', $inv) }}" wire:navigate class="font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 hover:underline">
                                    {{ $inv->invoice_number }}
                                </a>
                                <div class="text-[11px] text-slate-400">{{ $inv->invoiceLines->count() }} baris pengiriman</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('purchase-orders.show', $inv->purchaseOrder) }}" wire:navigate class="hover:underline font-semibold text-slate-800 dark:text-zinc-200">
                                    {{ $inv->purchaseOrder->po_number }}
                                </a>
                            </td>
                            @if (! $isVendor)
                                <td class="px-6 py-4 font-semibold text-slate-800 dark:text-zinc-200">{{ $inv->vendor->name }}</td>
                            @endif
                            <td class="px-6 py-4 text-xs whitespace-nowrap">
                                <div class="font-medium text-slate-700 dark:text-zinc-300">{{ $inv->invoice_date->format('d M Y') }}</div>
                                @if ($inv->isOverdue())
                                    <span class="inline-flex items-center gap-1 mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950/80 dark:text-rose-300 border border-rose-200">
                                        ⚠️ Overdue ({{ $inv->days_overdue }} hari)
                                    </span>
                                @elseif (! $inv->isPaid() && ! $inv->isVoid() && ! $inv->isRejected() && $inv->due_date)
                                    <span class="text-[10px] text-slate-400 block mt-0.5">
                                        Jatuh Tempo: {{ $inv->due_date->format('d M Y') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-black text-slate-900 dark:text-white whitespace-nowrap">
                                Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($inv->isThreeWayMatched())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        Match Valid
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/60">
                                        <span class="size-1.5 rounded-full bg-rose-500"></span>
                                        Anomali
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <flux:badge size="sm" :color="$inv->status->badgeColor()">
                                    {{ $inv->status->label() }}
                                </flux:badge>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('invoices.show', $inv) }}" wire:navigate class="px-3 py-1.5 rounded-lg text-xs font-bold {{ ($inv->isSubmitted() && $isSpv) ? 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-2xs' : 'text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300' }} transition">
                                    {{ ($inv->isSubmitted() && $isSpv) ? 'Verifikasi 3-Way' : 'Lihat Detail' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isVendor ? 7 : 8 }}" class="px-6 py-12 text-center text-slate-400 text-xs">
                                Tidak ada dokumen tagihan ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-zinc-800">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
