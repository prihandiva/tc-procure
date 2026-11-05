<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Daftar Purchase Order (PO)</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60">
                    {{ $purchaseOrders->total() }} Total
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                {{ $isVendor ? 'Pantau alokasi kuota PO, sisa pengiriman makanan, dan status penagihan vendor Anda.' : 'Kelola penerbitan kontrak PO snack & catering, batas plafon anggaran, dan realisasi kuota.' }}
            </p>
        </div>
        @can('create', App\Models\PurchaseOrder::class)
            <a href="{{ route('purchase-orders.create') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/30 transition transform active:scale-95">
                <flux:icon icon="plus" class="size-4" />
                <span>Terbitkan PO Baru</span>
            </a>
        @endcan
    </div>

    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm dark:bg-emerald-950/40 dark:border-emerald-800/60 dark:text-emerald-300 shadow-2xs">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600" />
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Search & Filters -->
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-4 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Pencarian</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nomor PO atau catatan..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
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
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Jenis Dokumen</label>
                <select wire:model.live="type" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                    <option value="all">Semua Jenis</option>
                    <option value="po">PO Resmi (Purchase Order)</option>
                    <option value="non_po">Non-PO (Langsung)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status PO</label>
                <select wire:model.live="status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                    <option value="all">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="active">Aktif</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-zinc-300 divide-y divide-slate-100 dark:divide-zinc-800">
                <thead class="bg-slate-50/80 dark:bg-zinc-800/60 font-bold text-slate-500 dark:text-zinc-400 text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Nomor PO</th>
                        @if (! $isVendor)
                            <th class="px-6 py-3.5">Vendor</th>
                        @endif
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5 text-center">Realisasi Fisik (Qty)</th>
                        <th class="px-6 py-3.5 text-right">Nilai Total Plafon</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse ($purchaseOrders as $po)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <a href="{{ route('purchase-orders.show', $po) }}" wire:navigate class="hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline">
                                        {{ $po->po_number }}
                                    </a>
                                    @if ($po->type->value === 'non_po')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/50">
                                            Non-PO
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-400">{{ $po->po_items_count ?? $po->poItems->count() }} jenis item barang</div>
                            </td>
                            @if (! $isVendor)
                                <td class="px-6 py-4 font-semibold text-slate-800 dark:text-zinc-200">{{ $po->vendor->name }}</td>
                            @endif
                            <td class="px-6 py-4 text-xs text-slate-500 whitespace-nowrap">{{ $po->po_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="text-xs font-semibold">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ number_format($po->total_qty_delivered, 0, ',', '.') }}</span>
                                    <span class="text-slate-400">/ {{ number_format($po->total_qty_ordered, 0, ',', '.') }}</span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    Sisa: <span class="font-bold text-amber-600 dark:text-amber-400">{{ number_format($po->total_qty_remaining, 0, ',', '.') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right font-black text-slate-900 dark:text-white whitespace-nowrap">
                                Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($po->status->value === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60">
                                        <span class="size-1.5 rounded-full bg-indigo-500"></span>
                                        Aktif
                                    </span>
                                @elseif ($po->status->value === 'completed')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        Selesai
                                    </span>
                                @elseif ($po->status->value === 'cancelled')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/60">
                                        <span class="size-1.5 rounded-full bg-rose-500"></span>
                                        Dibatalkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300 border border-slate-200/60">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('purchase-orders.show', $po) }}" wire:navigate class="px-2.5 py-1 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 rounded-lg transition">
                                    Detail
                                </a>
                                @can('update', $po)
                                    <a href="{{ route('purchase-orders.edit', $po) }}" wire:navigate class="px-2.5 py-1 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300 rounded-lg transition">
                                        Ubah
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isVendor ? 6 : 7 }}" class="px-6 py-12 text-center text-slate-400 text-xs">
                                Tidak ada dokumen Purchase Order ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-zinc-800">
            {{ $purchaseOrders->links() }}
        </div>
    </div>
</div>
