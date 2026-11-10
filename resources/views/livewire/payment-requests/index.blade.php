<div class="space-y-6 max-w-7xl mx-auto w-full min-w-0">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white">Payment Request (Pengajuan Bayar)</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60 whitespace-nowrap shrink-0">
                    {{ $paymentRequests->total() }} Total
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                {{ $isVendor ? 'Pantau proses approval bertingkat dan status pencairan atas tagihan Anda.' : 'Pengelompokan faktur yang disetujui ke dalam nomor Payment Request dan pelacakan tahapan ACC/Finance.' }}
            </p>
        </div>
        @can('create', App\Models\PaymentRequest::class)
            <a href="{{ route('payment-requests.create') }}" wire:navigate class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/30 transition transform active:scale-95 w-full sm:w-auto">
                <flux:icon icon="plus" class="size-4" />
                <span>Buat PR Baru</span>
            </a>
        @endcan
    </div>

    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm dark:bg-emerald-950/40 dark:border-emerald-800/60 dark:text-emerald-300 shadow-2xs">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600" />
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Card Daftar Invoice Approved Siap Dibuatkan PR -->
    @if ($isSpv && $readyForPrInvoices->isNotEmpty())
        <div x-data="{ showAll: false }" class="rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/25 border border-indigo-200/80 dark:border-indigo-900/50 p-4 shadow-xs space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex items-start sm:items-center gap-2.5">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-xs">
                        <flux:icon icon="banknotes" class="size-4.5" />
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-sm font-bold text-indigo-950 dark:text-indigo-100">Invoice Approved Siap Dibuatkan Payment Request (PR)</h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-600 text-white">
                                {{ $readyForPrInvoices->count() }} Invoice
                            </span>
                            <span class="text-xs font-black text-indigo-700 dark:text-indigo-300">
                                Total: Rp {{ number_format($readyForPrInvoices->sum('total_amount'), 0, ',', '.') }}
                            </span>
                        </div>
                        <p class="text-xs text-indigo-800/80 dark:text-indigo-300/80 mt-0.5">Invoice berikut telah lolos verifikasi 3-Way Match dan belum masuk ke PR. Klik <strong>Buat PR</strong> untuk mengajukan pembayaran.</p>
                    </div>
                </div>
                @if ($readyForPrInvoices->count() > 3)
                    <button type="button" @click="showAll = !showAll" class="text-xs font-bold text-indigo-700 dark:text-indigo-300 hover:underline shrink-0 self-start sm:self-auto cursor-pointer">
                        <span x-text="showAll ? 'Tampilkan Lebih Sedikit' : 'Lihat Semua ({{ $readyForPrInvoices->count() }})'"></span>
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($readyForPrInvoices as $idx => $inv)
                    <div
                        wire:key="ready-pr-inv-{{ $inv->id }}"
                        @if ($idx >= 3) x-show="showAll" x-cloak @endif
                        class="flex items-center justify-between gap-3 p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-indigo-200/70 dark:border-zinc-800 shadow-2xs hover:border-indigo-400 dark:hover:border-indigo-700 transition"
                    >
                        <div class="min-w-0 space-y-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <a href="{{ route('invoices.show', $inv) }}" wire:navigate class="font-mono font-bold text-xs text-slate-900 dark:text-white hover:text-indigo-600 hover:underline truncate">
                                    {{ $inv->invoice_number }}
                                </a>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                                    Approved
                                </span>
                            </div>
                            <div class="text-[11px] font-medium text-slate-600 dark:text-zinc-300 truncate">{{ $inv->vendor->name }}</div>
                            <div class="text-[11px] text-slate-400 truncate">
                                PO: <span class="font-mono text-slate-600 dark:text-zinc-300">{{ $inv->purchaseOrder->po_number ?? '-' }}</span>
                                <span class="mx-1">&bull;</span>
                                <span>{{ $inv->invoice_date->format('d M Y') }}</span>
                            </div>
                            <div class="text-xs font-black text-indigo-600 dark:text-indigo-400">
                                Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                            </div>
                        </div>

                        <a href="{{ route('payment-requests.create', ['invoice_id' => $inv->id]) }}" wire:navigate class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-xs transition shrink-0 whitespace-nowrap">
                            <flux:icon icon="plus" class="size-3.5" />
                            <span>Buat PR</span>
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
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nomor PR, catatan, invoice..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                    <flux:icon icon="magnifying-glass" class="size-4 text-slate-400 absolute left-3 top-2.5" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Tahap Status</label>
                <select wire:model.live="stage_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                    <option value="all">Semua Tahap</option>
                    @foreach ($stages as $stg)
                        <option value="{{ $stg->id }}">{{ $stg->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status Posting SAP</label>
                <select wire:model.live="posting_status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                    <option value="all">Semua Status</option>
                    <option value="unposted">Belum Diposting</option>
                    <option value="posted">Sudah Diposting (Selesai)</option>
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
            <table class="min-w-[740px] w-full text-left text-sm text-slate-700 dark:text-zinc-300 divide-y divide-slate-100 dark:divide-zinc-800 whitespace-nowrap">
                <thead class="bg-slate-50/80 dark:bg-zinc-800/60 font-bold text-slate-500 dark:text-zinc-400 text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Nomor Payment Request</th>
                        <th class="px-6 py-3.5">Tahap Saat Ini</th>
                        <th class="px-6 py-3.5 text-center">Invoice Terkait</th>
                        <th class="px-6 py-3.5 text-right">Total Nilai</th>
                        <th class="px-6 py-3.5">Tanggal Pengajuan</th>
                        <th class="px-6 py-3.5 text-center">Status Posting</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse ($paymentRequests as $pr)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('payment-requests.show', $pr) }}" wire:navigate class="font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 hover:underline">
                                    {{ $pr->number }}
                                </a>
                                @if ($pr->notes)
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5">{{ $pr->notes }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60">
                                    <span class="size-1.5 rounded-full bg-indigo-500"></span>
                                    {{ $pr->currentStage->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="font-bold text-xs px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $pr->invoices->count() }} Invoice
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-black text-slate-900 dark:text-white whitespace-nowrap">
                                Rp {{ number_format($pr->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                                {{ $pr->submitted_at ? $pr->submitted_at->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($pr->isPosted())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        Posted ({{ $pr->posting->document_number }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/60">
                                        <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Belum Diposting
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('payment-requests.show', $pr) }}" wire:navigate class="px-3 py-1.5 rounded-lg text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 transition">
                                    Detail PR &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-xs">
                                Tidak ada dokumen Payment Request ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-zinc-800">
            {{ $paymentRequests->links() }}
        </div>
    </div>
</div>
