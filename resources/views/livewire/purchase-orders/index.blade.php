<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Daftar Purchase Order (PO)</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ $isVendor ? 'Pantau PO, kuota barang, sisa pengiriman, dan status penagihan vendor Anda.' : 'Kelola penerbitan PO makanan/snack, alokasi vendor, dan status kuota pelatihan.' }}
            </p>
        </div>
        @can('create', App\Models\PurchaseOrder::class)
            <a href="{{ route('purchase-orders.create') }}" wire:navigate class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                + Terbitkan PO Baru
            </a>
        @endcan
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm dark:bg-green-900/30 dark:border-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    <!-- Search & Filters -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Pencarian</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nomor PO atau catatan..." class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            @if (! $isVendor)
                <div>
                    <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Vendor</label>
                    <select wire:model.live="vendor_id" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="all">Semua Vendor</option>
                        @foreach ($vendors as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Jenis Dokumen</label>
                <select wire:model.live="type" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="all">Semua Jenis</option>
                    <option value="po">PO (Purchase Order)</option>
                    <option value="non_po">Non-PO (Langsung)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Status PO</label>
                <select wire:model.live="status" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
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
    <div class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-700 dark:text-zinc-300 divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60 font-semibold text-zinc-600 dark:text-zinc-300">
                    <tr>
                        <th class="px-6 py-3">Nomor Dokumen</th>
                        @if (! $isVendor)
                            <th class="px-6 py-3">Vendor</th>
                        @endif
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3 text-center">Status Barang (Qty)</th>
                        <th class="px-6 py-3 text-right">Nilai Total (Rp)</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($purchaseOrders as $po)
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                    <a href="{{ route('purchase-orders.show', $po) }}" wire:navigate class="hover:text-blue-600 hover:underline">
                                        {{ $po->po_number }}
                                    </a>
                                    @if ($po->type->value === 'non_po')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                            Non-PO
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-zinc-400">{{ $po->po_items_count ?? $po->poItems->count() }} jenis item</div>
                            </td>
                            @if (! $isVendor)
                                <td class="px-6 py-4 font-medium">{{ $po->vendor->name }}</td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap">{{ $po->po_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="text-xs font-medium">
                                    <span class="text-green-600 font-bold">{{ number_format($po->total_qty_delivered, 0, ',', '.') }}</span>
                                    / {{ number_format($po->total_qty_ordered, 0, ',', '.') }}
                                </div>
                                <div class="text-[11px] text-zinc-400">
                                    Sisa: <span class="font-semibold text-amber-600">{{ number_format($po->total_qty_remaining, 0, ',', '.') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right font-medium whitespace-nowrap">
                                Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($po->status->value === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">Aktif</span>
                                @elseif ($po->status->value === 'completed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Selesai</span>
                                @elseif ($po->status->value === 'cancelled')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">Dibatalkan</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300">Draft</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('purchase-orders.show', $po) }}" wire:navigate class="text-blue-600 hover:text-blue-800 font-medium text-xs">
                                    Detail
                                </a>
                                @can('update', $po)
                                    <a href="{{ route('purchase-orders.edit', $po) }}" wire:navigate class="text-amber-600 hover:text-amber-800 font-medium text-xs">
                                        Ubah
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isVendor ? 6 : 7 }}" class="px-6 py-8 text-center text-zinc-400">
                                Tidak ada dokumen Purchase Order ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
            {{ $purchaseOrders->links() }}
        </div>
    </div>
</div>

