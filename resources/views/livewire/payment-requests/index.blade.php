<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Payment Request (Pengajuan Bayar)</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ $isVendor ? 'Pantau proses pengajuan pembayaran bertingkat atas invoice Anda.' : 'Pengelompokan invoice yang disetujui ke dalam nomor Payment Request dan pelacakan tahapan ACC/Finance.' }}
            </p>
        </div>
        @can('create', App\Models\PaymentRequest::class)
            <a href="{{ route('payment-requests.create') }}" wire:navigate class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                + Buat Payment Request Baru
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
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nomor PR, catatan, invoice..." class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Tahap Status</label>
                <select wire:model.live="stage_id" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="all">Semua Tahap</option>
                    @foreach ($stages as $stg)
                        <option value="{{ $stg->id }}">{{ $stg->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Status Posting SAP</label>
                <select wire:model.live="posting_status" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="all">Semua</option>
                    <option value="unposted">Belum Diposting</option>
                    <option value="posted">Sudah Diposting (Selesai)</option>
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
                        <th class="px-6 py-3">Nomor Payment Request</th>
                        <th class="px-6 py-3">Tahap Saat Ini</th>
                        <th class="px-6 py-3 text-center">Invoice Terkait</th>
                        <th class="px-6 py-3 text-right">Total Nilai (Rp)</th>
                        <th class="px-6 py-3">Tanggal Pengajuan</th>
                        <th class="px-6 py-3 text-center">Status Posting</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($paymentRequests as $pr)
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('payment-requests.show', $pr) }}" wire:navigate class="font-bold text-blue-600 hover:text-blue-800">
                                    {{ $pr->number }}
                                </a>
                                @if ($pr->notes)
                                    <div class="text-xs text-zinc-400 truncate max-w-xs">{{ $pr->notes }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                                    {{ $pr->currentStage->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="font-semibold text-xs">{{ $pr->invoices->count() }} Invoice</span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold whitespace-nowrap">
                                Rp {{ number_format($pr->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                {{ $pr->submitted_at ? $pr->submitted_at->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($pr->isPosted())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                        ✓ Posted ({{ $pr->posting->document_number }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                        Belum Diposting
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('payment-requests.show', $pr) }}" wire:navigate class="text-blue-600 hover:text-blue-800 font-medium text-xs">
                                    Lihat Detail &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-zinc-400">
                                Tidak ada dokumen Payment Request ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
            {{ $paymentRequests->links() }}
        </div>
    </div>
</div>

