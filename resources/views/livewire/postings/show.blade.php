<div class="space-y-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('postings.index') }}" wire:navigate class="flex size-10 items-center justify-center text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-100 bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 shadow-2xs hover:shadow-xs transition">
                <flux:icon icon="arrow-left" class="size-4" />
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                        Dokumen SAP #{{ $posting->document_number }}
                    </h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                        Posted SAP
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                    Tanggal Posting: <strong class="text-slate-800 dark:text-zinc-200">{{ $posting->posting_date?->translatedFormat('d F Y') }}</strong>
                </p>
            </div>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm dark:bg-emerald-950/40 dark:border-emerald-800/60 dark:text-emerald-300 shadow-2xs">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600" />
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    {{-- KPI Cards (4 Cards) --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Total Nominal Dibayar</span>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2 tracking-tight">
                Rp {{ number_format($totalPaid, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-400 mt-1">Nilai pelunasan di kasir/SAP</p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Payment Request Terkait</span>
            <div class="text-2xl font-black text-slate-900 dark:text-white mt-2 tracking-tight">
                {{ $posting->paymentRequests->count() }} PR
            </div>
            <p class="text-xs text-slate-400 mt-1">Berkas pengajuan kasir</p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400">Total Invoice</span>
            <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-2 tracking-tight">
                {{ $invoices->count() }} Faktur
            </div>
            <p class="text-xs text-slate-400 mt-1">Semua berstatus LUNAS</p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Vendor Terkait</span>
            <div class="text-sm font-bold text-slate-800 dark:text-zinc-200 mt-2 truncate">
                {{ $vendors->pluck('name')->implode(', ') ?: '-' }}
            </div>
            <p class="text-xs text-slate-400 mt-1">{{ $vendors->count() }} Rekanan Catering</p>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Linked Payment Requests Table --}}
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Payment Request Terkait</h3>
                </div>
                <div class="overflow-x-auto -mx-3 sm:mx-0">
                    <table class="min-w-[600px] w-full divide-y divide-slate-100 text-left text-sm dark:divide-zinc-800 whitespace-nowrap">
                        <thead class="bg-slate-50/80 dark:bg-zinc-800/60 text-[11px] uppercase font-bold text-slate-500 dark:text-zinc-400 tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5">No. PR</th>
                                <th class="px-4 py-3.5">Tgl Diajukan</th>
                                <th class="px-4 py-3.5">Tahapan</th>
                                <th class="px-4 py-3.5 text-center">Jml Invoice</th>
                                <th class="px-5 py-3.5 text-right">Total Nilai</th>
                                <th class="px-4 py-3.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            @foreach ($posting->paymentRequests as $pr)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                                    <td class="px-5 py-4 font-bold text-slate-900 dark:text-white">
                                        {{ $pr->number }}
                                    </td>
                                    <td class="px-4 py-4 text-xs text-slate-500">
                                        {{ $pr->submitted_at?->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60">
                                            {{ $pr->currentStage?->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center text-xs font-bold text-slate-700 dark:text-zinc-300">
                                        {{ $pr->invoices->count() }}
                                    </td>
                                    <td class="px-5 py-4 text-right font-black text-slate-900 dark:text-white">
                                        Rp {{ number_format($pr->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <a href="{{ route('payment-requests.show', $pr) }}" wire:navigate class="px-2.5 py-1 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg dark:bg-indigo-950/60 dark:text-indigo-300 transition">
                                            Detail PR
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Linked Invoices Table --}}
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Daftar Invoice Lunas</h3>
                </div>
                <div class="overflow-x-auto -mx-3 sm:mx-0">
                    <table class="min-w-[600px] w-full divide-y divide-slate-100 text-left text-sm dark:divide-zinc-800 whitespace-nowrap">
                        <thead class="bg-slate-50/80 dark:bg-zinc-800/60 text-[11px] uppercase font-bold text-slate-500 dark:text-zinc-400 tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5">No. Invoice</th>
                                <th class="px-4 py-3.5">Vendor</th>
                                <th class="px-4 py-3.5">No. PO</th>
                                <th class="px-5 py-3.5 text-right">Nilai Tagihan</th>
                                <th class="px-4 py-3.5 text-center">Status</th>
                                <th class="px-4 py-3.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            @foreach ($invoices as $inv)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                                    <td class="px-5 py-4 font-bold text-slate-900 dark:text-white">
                                        {{ $inv->invoice_number }}
                                    </td>
                                    <td class="px-4 py-4 text-xs font-semibold text-slate-800 dark:text-zinc-200">
                                        {{ $inv->vendor->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-xs font-mono text-slate-600 dark:text-zinc-400">
                                        {{ $inv->purchaseOrder?->po_number ?? '-' }}
                                    </td>
                                    <td class="px-5 py-4 text-right font-black text-slate-900 dark:text-white">
                                        Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $inv->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <a href="{{ route('invoices.show', $inv) }}" wire:navigate class="px-2.5 py-1 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg dark:bg-indigo-950/60 dark:text-indigo-300 transition">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right 1 col: Details & Documents --}}
        <div class="space-y-6">
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-zinc-800 pb-3">Informasi Dokumen SAP</h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block mb-0.5">Nomor Dokumen SAP:</span>
                        <span class="font-mono font-bold text-slate-900 dark:text-white text-sm">{{ $posting->document_number }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Tanggal Posting:</span>
                        <span class="font-semibold text-slate-800 dark:text-zinc-200">{{ $posting->posting_date?->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Total Nominal Dibayar:</span>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Dicatat Oleh:</span>
                        <span class="font-medium text-slate-800 dark:text-zinc-200">{{ $posting->creator?->name ?? '-' }}</span>
                    </div>
                    @if ($posting->notes)
                        <div class="pt-2 border-t border-slate-100 dark:border-zinc-800">
                            <span class="text-slate-400 block mb-1 font-medium">Catatan:</span>
                            <div class="rounded-xl bg-slate-50 dark:bg-zinc-800/60 p-2.5 text-xs text-slate-700 dark:text-zinc-300 italic border border-slate-100 dark:border-zinc-800">
                                {{ $posting->notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Proof of Payment Document --}}
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-zinc-800 pb-3">Bukti Bayar / Dokumen SAP</h3>

                <x-document-preview-card :documents="$posting->documents" />

                @if ($isSpv)
                    <div class="rounded-2xl border border-dashed border-slate-300 dark:border-zinc-700 p-4 bg-slate-50/50 dark:bg-zinc-900/40 space-y-3">
                        <h4 class="text-xs font-bold text-slate-800 dark:text-zinc-200">Unggah Bukti Bayar Tambahan</h4>
                        <form wire:submit="uploadDocument" class="space-y-3">
                            <div>
                                <x-file-upload-camera
                                    model="document_file"
                                    :file="$document_file"
                                    label="Pilih Foto / Berkas Bukti Transfer"
                                    help="Foto bukti transfer kasir atau pilih file PDF/JPG dari galeri."
                                />
                            </div>
                            <div>
                                <input type="text" wire:model="document_notes" placeholder="Keterangan bukti transfer (opsional)..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white">
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="w-full sm:w-auto px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-xs transition" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="uploadDocument">Unggah File Bukti</span>
                                    <span wire:loading wire:target="uploadDocument">Mengunggah...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
