<div class="space-y-6 max-w-7xl mx-auto w-full min-w-0">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white">Dokumen Posting SAP</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60 whitespace-nowrap shrink-0">
                    {{ $postings->total() }} Total
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                Pencatatan nomor dokumen akuntansi SAP dan konfirmasi pelunasan riil tagihan catering Training Center.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
            <x-report-export-modal default-type="postings" button-label="Ekspor Laporan" wrapper-class="w-full sm:w-auto" trigger-class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 text-xs font-bold hover:bg-slate-50 dark:hover:bg-zinc-700/60 shadow-2xs transition transform active:scale-95 cursor-pointer whitespace-nowrap" />
            @if ($isSpv)
                <a href="{{ route('postings.create') }}" wire:navigate class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/30 transition transform active:scale-95 whitespace-nowrap">
                    <flux:icon icon="plus" class="size-4 shrink-0" />
                    <span>Catat Posting Baru</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-4 shadow-xs">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Pencarian</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari No. Dokumen SAP, No. PR, atau Catatan..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                    <flux:icon icon="magnifying-glass" class="size-4 text-slate-400 absolute left-3 top-2.5" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Dari Tanggal</label>
                <input type="date" wire:model.live="date_from" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Sampai Tanggal</label>
                <input type="date" wire:model.live="date_to" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white">
            </div>
        </div>
    </div>

    {{-- Table of Postings --}}
    <div class="w-full max-w-full rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
        <div class="w-full max-w-full overflow-x-auto">
            <table class="min-w-[720px] w-full divide-y divide-slate-100 text-left text-sm dark:divide-zinc-800 whitespace-nowrap">
                <thead class="bg-slate-50/80 dark:bg-zinc-800/60 text-[11px] uppercase font-bold text-slate-500 dark:text-zinc-400 tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">No. Dokumen SAP</th>
                        <th class="px-6 py-3.5">Tgl Posting</th>
                        <th class="px-6 py-3.5 text-right">Total Dibayar</th>
                        <th class="px-6 py-3.5">Payment Request Terkait</th>
                        <th class="px-6 py-3.5">Vendor</th>
                        <th class="px-6 py-3.5 text-center">Bukti Bayar</th>
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse ($postings as $posting)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                <a href="{{ route('postings.show', $posting) }}" wire:navigate class="hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline">
                                    {{ $posting->document_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $posting->posting_date?->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-right font-black text-emerald-600 dark:text-emerald-400">
                                @php
                                    $amountToDisplay = $isVendor
                                        ? (int) $posting->paymentRequests->flatMap->invoices->where('vendor_id', auth()->user()->vendor_id)->sum('total_amount')
                                        : (int) $posting->total_paid;
                                @endphp
                                Rp {{ number_format($amountToDisplay, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    @foreach ($posting->paymentRequests as $pr)
                                        <div class="text-xs font-semibold text-slate-800 dark:text-zinc-200">
                                            {{ $pr->number }}
                                        </div>
                                    @endforeach
                                    <div class="text-[10px] text-slate-400">
                                        {{ $posting->paymentRequests->count() }} PR
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-800 dark:text-zinc-200">
                                @php
                                    $vendorNames = $posting->paymentRequests
                                        ->flatMap->invoices
                                        ->pluck('vendor.name')
                                        ->unique()
                                        ->filter()
                                        ->implode(', ');
                                @endphp
                                {{ $vendorNames ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($posting->documents->isNotEmpty())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        Ada ({{ $posting->documents->count() }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs text-slate-400 bg-slate-100 dark:bg-zinc-800">
                                        Tidak Ada
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('postings.show', $posting) }}" wire:navigate class="px-3 py-1.5 rounded-lg text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-xs">
                                Belum ada dokumen posting SAP yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 dark:border-zinc-800">
            {{ $postings->links() }}
        </div>
    </div>
</div>
