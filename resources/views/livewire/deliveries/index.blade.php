<div class="space-y-6 max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Pengiriman & Surat Jalan (DO)</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60">
                    {{ $deliveries->total() }} Total
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                {{ $isVendor ? 'Input pengiriman harian makanan/snack pelatihan berdasarkan alokasi PO Anda.' : 'Pencatatan pengiriman vendor catering dan verifikasi penerimaan fisik Surat Jalan (DO).' }}
            </p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <x-report-export-modal default-type="deliveries" button-label="Ekspor Laporan" wrapper-class="flex-1 sm:flex-initial" trigger-class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2.5 rounded-xl border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 text-xs font-bold hover:bg-slate-50 dark:hover:bg-zinc-700/60 shadow-2xs transition transform active:scale-95 cursor-pointer whitespace-nowrap" />
            @can('create', App\Models\Delivery::class)
                <a href="{{ route('deliveries.create') }}" wire:navigate class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs shadow-md shadow-emerald-600/30 transition transform active:scale-95 whitespace-nowrap">
                    <flux:icon icon="plus" class="size-4 shrink-0" />
                    <span>Input Pengiriman Baru</span>
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

    <!-- Filter & Search -->
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-4 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Pencarian</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Kode DLV, No DO, PO, training..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
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
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status Pengiriman</label>
                <select wire:model.live="status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                    <option value="all">Semua Status</option>
                    <option value="submitted">Menunggu Konfirmasi SPV</option>
                    <option value="confirmed">Terkonfirmasi (Diterima)</option>
                    <option value="rejected">Ditolak</option>
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
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto -mx-3 sm:mx-0">
            <table class="min-w-[720px] w-full text-left text-sm text-slate-700 dark:text-zinc-300 divide-y divide-slate-100 dark:divide-zinc-800 whitespace-nowrap">
                <thead class="bg-slate-50/80 dark:bg-zinc-800/60 font-bold text-slate-500 dark:text-zinc-400 text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Kode Pengiriman</th>
                        <th class="px-6 py-3.5">PO Terkait</th>
                        @if (! $isVendor)
                            <th class="px-6 py-3.5">Vendor</th>
                        @endif
                        <th class="px-6 py-3.5">Tanggal Kirim</th>
                        <th class="px-6 py-3.5">No. DO Vendor</th>
                        <th class="px-6 py-3.5 text-center">Total Kuantitas</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse ($deliveries as $deliv)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('deliveries.show', $deliv) }}" wire:navigate class="font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 hover:underline">
                                        {{ $deliv->code }}
                                    </a>
                                    @if ($deliv->hasDiscrepancy())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                                            Selisih
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('purchase-orders.show', $deliv->purchaseOrder) }}" wire:navigate class="hover:underline font-semibold text-slate-800 dark:text-zinc-200">
                                    {{ $deliv->purchaseOrder->po_number }}
                                </a>
                            </td>
                            @if (! $isVendor)
                                <td class="px-6 py-4 font-semibold text-slate-800 dark:text-zinc-200">{{ $deliv->vendor->name }}</td>
                            @endif
                            <td class="px-6 py-4 text-xs text-slate-500 whitespace-nowrap">{{ $deliv->delivery_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-slate-600 dark:text-zinc-400">{{ $deliv->vendor_do_number ?: '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="text-xs font-bold">
                                    @if ($deliv->isConfirmed())
                                        <span class="text-emerald-600 dark:text-emerald-400">{{ number_format($deliv->total_qty_received, 0, ',', '.') }}</span>
                                        <span class="text-slate-400 font-normal">/ kirim: {{ number_format($deliv->total_qty_sent, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-slate-900 dark:text-white">{{ number_format($deliv->total_qty_sent, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-slate-400">{{ $deliv->deliveryItems->count() }} baris item</div>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($deliv->status->value === 'confirmed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        Terkonfirmasi
                                    </span>
                                @elseif ($deliv->status->value === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/60">
                                        <span class="size-1.5 rounded-full bg-rose-500"></span>
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/60">
                                        <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Menunggu SPV
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('deliveries.show', $deliv) }}" wire:navigate class="px-3 py-1.5 rounded-lg text-xs font-bold {{ ($deliv->isSubmitted() && $isSpv) ? 'bg-amber-500 hover:bg-amber-600 text-white shadow-2xs' : 'text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300' }} transition">
                                    {{ ($deliv->isSubmitted() && $isSpv) ? 'Verifikasi Fisik' : 'Lihat Detail' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isVendor ? 7 : 8 }}" class="px-6 py-12 text-center text-slate-400 text-xs">
                                Tidak ada data pengiriman ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-zinc-800">
            {{ $deliveries->links() }}
        </div>
    </div>
</div>
