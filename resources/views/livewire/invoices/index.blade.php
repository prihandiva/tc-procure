<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Tagihan & Faktur (Invoice)</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ $isVendor ? 'Buat dan ajukan invoice dari pengiriman yang telah dikonfirmasi SPV, serta pantau status pembayaran.' : 'Verifikasi tagihan vendor melalui pencocokan 3 arah (3-way match PO-DO-Invoice).' }}
            </p>
        </div>
        @can('create', App\Models\Invoice::class)
            <a href="{{ route('invoices.create') }}" wire:navigate class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                + Buat Tagihan / Invoice Baru
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
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nomor invoice, nomor PO, vendor..." class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
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
                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Status Tagihan</label>
                <select wire:model.live="status" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="all">Semua Status</option>
                    <option value="submitted">Diajukan (Menunggu SPV)</option>
                    <option value="approved">Disetujui SPV</option>
                    <option value="in_payment">Dalam Proses Bayar</option>
                    <option value="paid">Lunas (Paid)</option>
                    <option value="rejected">Ditolak</option>
                    <option value="void">Batal (Void)</option>
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
                        <th class="px-6 py-3">Nomor Invoice</th>
                        <th class="px-6 py-3">PO Terkait</th>
                        @if (! $isVendor)
                            <th class="px-6 py-3">Vendor</th>
                        @endif
                        <th class="px-6 py-3">Tanggal Tagihan</th>
                        <th class="px-6 py-3 text-right">Total Tagihan (Rp)</th>
                        <th class="px-6 py-3 text-center">3-Way Match</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($invoices as $inv)
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('invoices.show', $inv) }}" wire:navigate class="font-semibold text-blue-600 hover:text-blue-800">
                                    {{ $inv->invoice_number }}
                                </a>
                                <div class="text-[11px] text-zinc-400">{{ $inv->invoiceLines->count() }} baris pengiriman</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('purchase-orders.show', $inv->purchaseOrder) }}" wire:navigate class="hover:underline font-medium">
                                    {{ $inv->purchaseOrder->po_number }}
                                </a>
                            </td>
                            @if (! $isVendor)
                                <td class="px-6 py-4">{{ $inv->vendor->name }}</td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap">{{ $inv->invoice_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-right font-bold whitespace-nowrap">
                                Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($inv->isThreeWayMatched())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                        ✓ Match Valid
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300">
                                        ⚠ Anomali
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($inv->status->value === 'approved')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                        Disetujui
                                    </span>
                                @elseif ($inv->status->value === 'paid')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                        ★ Lunas
                                    </span>
                                @elseif ($inv->status->value === 'in_payment')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                        Proses Bayar
                                    </span>
                                @elseif ($inv->status->value === 'rejected')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                        Diajukan
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('invoices.show', $inv) }}" wire:navigate class="text-blue-600 hover:text-blue-800 font-medium text-xs">
                                    {{ ($inv->isSubmitted() && $isSpv) ? 'Verifikasi (3-Way)' : 'Lihat Detail' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isVendor ? 7 : 8 }}" class="px-6 py-8 text-center text-zinc-400">
                                Tidak ada dokumen tagihan ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
            {{ $invoices->links() }}
        </div>
    </div>
</div>

