<div x-data="{ open: false }" @keydown.escape.window="open = false" class="relative inline-flex items-center shrink-0">
    <!-- Notification Bell Button -->
    <button
        type="button"
        @click="open = !open"
        aria-label="Notifikasi Transaksi"
        title="Notifikasi PO, DO &amp; Invoice"
        class="relative inline-flex items-center justify-center size-8 sm:size-9 rounded-lg border border-slate-200/80 dark:border-zinc-700/80 bg-slate-50/90 hover:bg-slate-100 dark:bg-zinc-800/80 dark:hover:bg-zinc-800 text-slate-600 dark:text-zinc-300 transition cursor-pointer shrink-0"
        data-test="header-notifications-button"
    >
        <flux:icon icon="bell" class="size-4 {{ $totalCount > 0 ? 'text-indigo-600 dark:text-indigo-400' : '' }}" />
        @if ($totalCount > 0)
            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-rose-600 text-white text-[10px] font-black shadow-xs ring-2 ring-white dark:ring-zinc-900">
                {{ $totalCount > 99 ? '99+' : $totalCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Notification Panel -->
    <div
        x-show="open"
        x-cloak
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="fixed inset-x-3 top-14 sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-2 sm:w-96 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-2xl z-50 overflow-hidden"
    >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-slate-100 dark:border-zinc-800 bg-slate-50/80 dark:bg-zinc-800/60 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <flux:icon icon="bell-alert" class="size-4 text-indigo-600 dark:text-indigo-400" />
                <span class="text-xs font-extrabold text-slate-900 dark:text-white">Pusat Notifikasi Transaksi</span>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $totalCount > 0 ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/60' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' }}">
                {{ $totalCount }} Perlu Perhatian
            </span>
        </div>

        <!-- Body -->
        <div class="max-h-[70vh] sm:max-h-96 overflow-y-auto divide-y divide-slate-100 dark:divide-zinc-800 text-xs">
            @if ($totalCount === 0)
                <div class="px-4 py-8 text-center text-slate-400 space-y-1">
                    <flux:icon icon="check-circle" class="size-7 mx-auto text-emerald-500" />
                    <div class="font-semibold text-slate-600 dark:text-zinc-300">Semua antrean transaksi sudah tertangani</div>
                    <div class="text-[11px]">Tidak ada PO baru, DO menunggu verifikasi, atau invoice tertunda.</div>
                </div>
            @else
                <!-- 1. DO Masuk / Menunggu Verifikasi -->
                @if ($pendingDos->isNotEmpty())
                    <div class="p-3 space-y-2 bg-amber-50/30 dark:bg-amber-950/10">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                                <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                {{ $isVendor ? 'DO Menunggu Verifikasi TC' : 'DO Masuk Menunggu Verifikasi' }} ({{ $pendingDos->count() }})
                            </span>
                            <a href="{{ route('deliveries.index') }}" @click="open = false" wire:navigate class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Lihat Daftar</a>
                        </div>
                        <div class="space-y-1.5">
                            @foreach ($pendingDos->take(3) as $deliv)
                                <a href="{{ route('deliveries.show', $deliv) }}" @click="open = false" wire:navigate class="flex items-center justify-between gap-2 p-2 rounded-xl bg-white dark:bg-zinc-800/80 border border-amber-200/70 dark:border-zinc-700 hover:border-amber-400 transition">
                                    <div class="min-w-0">
                                        <div class="font-mono font-bold text-slate-900 dark:text-white truncate">DO {{ $deliv->code }}</div>
                                        <div class="text-[11px] text-slate-500 dark:text-zinc-400 truncate">
                                            PO {{ $deliv->purchaseOrder->po_number ?? '-' }} &bull; {{ $isVendor ? $deliv->delivery_date->format('d M Y') : $deliv->vendor->name }}
                                        </div>
                                    </div>
                                    <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400 shrink-0">
                                        {{ $isVendor ? 'Detail' : 'Verifikasi' }} &rarr;
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- 2. Invoice Masuk / Menunggu Verifikasi / Perlu Revisi -->
                @if ($pendingInvoices->isNotEmpty())
                    <div class="p-3 space-y-2 bg-rose-50/30 dark:bg-rose-950/10">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-700 dark:text-rose-400 flex items-center gap-1.5">
                                <span class="size-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                {{ $isVendor ? 'Invoice Proses / Revisi' : 'Invoice Masuk Menunggu Verifikasi' }} ({{ $pendingInvoices->count() }})
                            </span>
                            <a href="{{ route('invoices.index') }}" @click="open = false" wire:navigate class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Lihat Daftar</a>
                        </div>
                        <div class="space-y-1.5">
                            @foreach ($pendingInvoices->take(3) as $inv)
                                <a href="{{ route('invoices.show', $inv) }}" @click="open = false" wire:navigate class="flex items-center justify-between gap-2 p-2 rounded-xl bg-white dark:bg-zinc-800/80 border border-rose-200/70 dark:border-zinc-700 hover:border-rose-400 transition">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono font-bold text-slate-900 dark:text-white truncate">{{ $inv->invoice_number }}</span>
                                            @if ($inv->isRejected())
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-100 text-rose-700">Ditolak</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-500 dark:text-zinc-400 truncate">
                                            Rp {{ number_format($inv->total_amount, 0, ',', '.') }} &bull; {{ $isVendor ? ($inv->purchaseOrder->po_number ?? '-') : $inv->vendor->name }}
                                        </div>
                                    </div>
                                    <span class="text-[11px] font-bold text-rose-600 dark:text-rose-400 shrink-0">
                                        {{ $isVendor ? 'Lihat' : '3-Way Match' }} &rarr;
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- 3. DO Terkonfirmasi Siap Dibuatkan Invoice (Untuk Vendor & TC) -->
                @if ($readyInvoices->isNotEmpty())
                    <div class="p-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-700 dark:text-indigo-400">
                                Siap Dibuatkan Invoice ({{ $readyInvoices->count() }} PO)
                            </span>
                            <a href="{{ route('invoices.index') }}" @click="open = false" wire:navigate class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Menu Invoice</a>
                        </div>
                        <div class="space-y-1.5">
                            @foreach ($readyInvoices->take(3) as $entry)
                                <a href="{{ route('invoices.create', ['po_id' => $entry['po']->id]) }}" @click="open = false" wire:navigate class="flex items-center justify-between gap-2 p-2 rounded-xl bg-slate-50 dark:bg-zinc-800/80 border border-slate-200/80 dark:border-zinc-700 hover:border-indigo-400 transition">
                                    <div class="min-w-0">
                                        <div class="font-mono font-bold text-slate-900 dark:text-white truncate">PO {{ $entry['po']->po_number }}</div>
                                        <div class="text-[11px] text-slate-500 dark:text-zinc-400 truncate">
                                            {{ $entry['deliveries_count'] }} DO Terkonfirmasi &bull; Rp {{ number_format($entry['estimated_amount'], 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 shrink-0">Buat Invoice &rarr;</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- 4. Invoice Approved Siap Dibuatkan PR (Khusus Tim TC / SPV) -->
                @if (! $isVendor && $readyForPrInvoices->isNotEmpty())
                    <div class="p-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-700 dark:text-purple-400">
                                Siap Dibuatkan Payment Request ({{ $readyForPrInvoices->count() }})
                            </span>
                            <a href="{{ route('payment-requests.index') }}" @click="open = false" wire:navigate class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Menu PR</a>
                        </div>
                        <div class="space-y-1.5">
                            @foreach ($readyForPrInvoices->take(3) as $inv)
                                <a href="{{ route('payment-requests.create', ['invoice_id' => $inv->id]) }}" @click="open = false" wire:navigate class="flex items-center justify-between gap-2 p-2 rounded-xl bg-slate-50 dark:bg-zinc-800/80 border border-slate-200/80 dark:border-zinc-700 hover:border-purple-400 transition">
                                    <div class="min-w-0">
                                        <div class="font-mono font-bold text-slate-900 dark:text-white truncate">{{ $inv->invoice_number }}</div>
                                        <div class="text-[11px] text-slate-500 dark:text-zinc-400 truncate">
                                            {{ $inv->vendor->name }} &bull; Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <span class="text-[11px] font-bold text-purple-600 dark:text-purple-400 shrink-0">Buat PR &rarr;</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- 5. PO Masuk / Aktif (Siap Kirim DO) -->
                @if ($incomingPos->isNotEmpty())
                    <div class="p-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                                {{ $isVendor ? 'PO Masuk / Siap Kirim DO' : 'PO Aktif Sisa Kuota Kirim' }} ({{ $incomingPos->count() }})
                            </span>
                            <a href="{{ route('purchase-orders.index') }}" @click="open = false" wire:navigate class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Daftar PO</a>
                        </div>
                        <div class="space-y-1.5">
                            @foreach ($incomingPos->take(3) as $po)
                                <a href="{{ $isVendor ? route('deliveries.create', ['po_id' => $po->id]) : route('purchase-orders.show', $po) }}" @click="open = false" wire:navigate class="flex items-center justify-between gap-2 p-2 rounded-xl bg-slate-50 dark:bg-zinc-800/80 border border-slate-200/80 dark:border-zinc-700 hover:border-emerald-400 transition">
                                    <div class="min-w-0">
                                        <div class="font-mono font-bold text-slate-900 dark:text-white truncate">PO {{ $po->po_number }}</div>
                                        <div class="text-[11px] text-slate-500 dark:text-zinc-400 truncate">
                                            Sisa: {{ number_format($po->total_qty_remaining, 0, ',', '.') }} {{ $isVendor ? '' : '• ' . $po->vendor->name }}
                                        </div>
                                    </div>
                                    <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 shrink-0">
                                        {{ $isVendor ? 'Buat DO' : 'Detail PO' }} &rarr;
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

