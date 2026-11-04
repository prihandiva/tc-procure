<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Pengiriman & Surat Jalan (DO)</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ $isVendor ? 'Input pengiriman harian makanan/snack pelatihan berdasarkan kuota PO Anda.' : 'Pencatatan pengiriman vendor catering dan verifikasi penerimaan barang fisik.' }}
            </p>
        </div>
        @can('create', App\Models\Delivery::class)
            <a href="{{ route('deliveries.create') }}" wire:navigate class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                + Input Pengiriman Baru
            </a>
        @endcan
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm dark:bg-green-900/30 dark:border-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filter & Search -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Pencarian</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Kode DLV, No DO, PO, training..." class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
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
                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Status Pengiriman</label>
                <select wire:model.live="status" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="all">Semua Status</option>
                    <option value="submitted">Menunggu Konfirmasi SPV</option>
                    <option value="confirmed">Terkonfirmasi (Diterima)</option>
                    <option value="rejected">Ditolak</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Dari Tanggal</label>
                    <input type="date" wire:model.live="date_from" class="w-full px-2 py-2 text-xs border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Sampai Tanggal</label>
                    <input type="date" wire:model.live="date_to" class="w-full px-2 py-2 text-xs border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100">
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-700 dark:text-zinc-300 divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60 font-semibold text-zinc-600 dark:text-zinc-300">
                    <tr>
                        <th class="px-6 py-3">Kode Pengiriman</th>
                        <th class="px-6 py-3">PO Terkait</th>
                        @if (! $isVendor)
                            <th class="px-6 py-3">Vendor</th>
                        @endif
                        <th class="px-6 py-3">Tanggal Kirim</th>
                        <th class="px-6 py-3">No. DO Vendor</th>
                        <th class="px-6 py-3 text-center">Total Kuantitas</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($deliveries as $deliv)
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('deliveries.show', $deliv) }}" wire:navigate class="font-semibold text-blue-600 hover:text-blue-800">
                                    {{ $deliv->code }}
                                </a>
                                @if ($deliv->hasDiscrepancy())
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-amber-100 text-amber-800 ml-1">
                                        Selisih
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('purchase-orders.show', $deliv->purchaseOrder) }}" wire:navigate class="hover:underline font-medium">
                                    {{ $deliv->purchaseOrder->po_number }}
                                </a>
                            </td>
                            @if (! $isVendor)
                                <td class="px-6 py-4">{{ $deliv->vendor->name }}</td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap">{{ $deliv->delivery_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs">{{ $deliv->vendor_do_number ?: '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="text-xs font-semibold">
                                    @if ($deliv->isConfirmed())
                                        <span class="text-green-600">{{ number_format($deliv->total_qty_received, 0, ',', '.') }}</span>
                                        <span class="text-zinc-400">/ kirim: {{ number_format($deliv->total_qty_sent, 0, ',', '.') }}</span>
                                    @else
                                        <span>{{ number_format($deliv->total_qty_sent, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-zinc-400">{{ $deliv->deliveryItems->count() }} baris item</div>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($deliv->status->value === 'confirmed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                        ✓ Terkonfirmasi
                                    </span>
                                @elseif ($deliv->status->value === 'rejected')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                        ✕ Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                        Menunggu SPV
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('deliveries.show', $deliv) }}" wire:navigate class="text-blue-600 hover:text-blue-800 font-medium text-xs">
                                    {{ ($deliv->isSubmitted() && $isSpv) ? 'Konfirmasi Penerimaan' : 'Lihat Detail' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isVendor ? 7 : 8 }}" class="px-6 py-8 text-center text-zinc-400">
                                Tidak ada data pengiriman ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
            {{ $deliveries->links() }}
        </div>
    </div>
</div>

