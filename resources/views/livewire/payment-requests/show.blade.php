<div class="space-y-6 max-w-7xl mx-auto">
    {{-- Header & Navigation --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('payment-requests.index') }}" wire:navigate class="flex size-10 items-center justify-center text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-100 bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 shadow-2xs hover:shadow-xs transition shrink-0">
                <flux:icon icon="arrow-left" class="size-4" />
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                        {{ $paymentRequest->number }}
                    </h1>
                    @if ($paymentRequest->isPosted())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                            Sudah Diposting SAP
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/60">
                            <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Menunggu Posting SAP
                        </span>
                    @endif
                    @if ($paymentRequest->currentStage)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60">
                            {{ $paymentRequest->currentStage->name }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                    Diajukan pada {{ $paymentRequest->submitted_at?->translatedFormat('d F Y H:i') ?? '-' }}
                </p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
            @if ($isSpv && ! $paymentRequest->isPosted())
                <a href="{{ route('postings.create') }}" wire:navigate class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/30 transition transform active:scale-95">
                    <flux:icon icon="check-badge" class="size-4" />
                    <span>Posting ke SAP</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm dark:bg-emerald-950/40 dark:border-emerald-800/60 dark:text-emerald-300 shadow-2xs">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600" />
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm dark:bg-rose-950/40 dark:border-rose-800/60 dark:text-rose-300 shadow-2xs">
            <flux:icon icon="exclamation-circle" class="size-5 shrink-0 text-rose-600" />
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- KPI Metric Cards (4 Cards) --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Nilai Tagihan</span>
            <div class="text-2xl font-black text-slate-900 dark:text-white mt-2 tracking-tight">
                Rp {{ number_format($paymentRequest->total_amount, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-400 mt-1">Gabungan {{ $paymentRequest->invoices->count() }} Invoice</p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400">Tahapan Saat Ini</span>
            <div class="text-lg font-black text-indigo-600 dark:text-indigo-400 mt-2 truncate">
                {{ $paymentRequest->currentStage?->name ?? '-' }}
            </div>
            <p class="text-xs text-slate-400 mt-1">Urutan Alur: Ke-{{ $paymentRequest->currentStage?->sort_order ?? 0 }}</p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Status SAP Posting</span>
            @if ($paymentRequest->isPosted())
                <div class="flex items-center gap-2 mt-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">Tercatat</span>
                    <span class="text-xs font-mono font-bold text-slate-900 dark:text-white">
                        {{ $paymentRequest->posting->document_number }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-1">Tgl: {{ $paymentRequest->posting->posting_date?->format('d/m/Y') }}</p>
            @else
                <div class="mt-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700">Belum Posting</span>
                </div>
                <p class="text-xs text-slate-400 mt-1">Menunggu penyelesaian di SAP</p>
            @endif
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Vendor Terkait</span>
            <div class="text-sm font-bold text-slate-800 dark:text-zinc-200 mt-2 truncate">
                {{ $paymentRequest->vendors->pluck('name')->implode(', ') ?: '-' }}
            </div>
            <p class="text-xs text-slate-400 mt-1">{{ $paymentRequest->vendors->count() }} Rekanan Catering</p>
        </div>
    </div>

    {{-- Posting Information Banner (if posted) --}}
    @if ($paymentRequest->isPosted())
        <div class="rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60 p-5 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <flux:icon.check-circle class="size-5 text-emerald-600 dark:text-emerald-400" />
                        <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-100">
                            Payment Request Telah Diposting ke SAP (Doc: {{ $paymentRequest->posting->document_number }})
                        </h3>
                    </div>
                    <p class="text-xs text-emerald-700 dark:text-emerald-300">
                        Tanggal Posting: <strong>{{ $paymentRequest->posting->posting_date?->translatedFormat('d F Y') }}</strong> &bull;
                        Total Dibayar: <strong>Rp {{ number_format($paymentRequest->posting->total_paid, 0, ',', '.') }}</strong>
                        @if ($paymentRequest->posting->notes)
                            &bull; Catatan: {{ $paymentRequest->posting->notes }}
                        @endif
                    </p>
                </div>
                <div>
                    <a href="{{ route('postings.show', $paymentRequest->posting) }}" wire:navigate class="px-4 py-2 rounded-xl text-xs font-bold text-emerald-700 bg-white hover:bg-emerald-50 border border-emerald-200 dark:bg-zinc-900 dark:text-emerald-300 dark:border-emerald-800 transition">
                        Lihat Dokumen Posting
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Main Content Grid: Invoices & Stage History --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left 2 cols: Invoices included --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Invoices Table Card --}}
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between bg-slate-50/50 dark:bg-zinc-900/50">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">Daftar Tagihan (Invoices)</h3>
                        <span class="text-xs text-slate-400">Invoice yang digabungkan dalam berkas pengajuan ini</span>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-300">
                        {{ $paymentRequest->invoices->count() }} Invoice
                    </span>
                </div>

                <div class="overflow-x-auto -mx-3 sm:mx-0">
                    <table class="min-w-[650px] w-full divide-y divide-slate-100 text-left text-sm dark:divide-zinc-800 whitespace-nowrap">
                        <thead class="bg-slate-50/80 dark:bg-zinc-800/60 text-[11px] uppercase font-bold text-slate-500 dark:text-zinc-400 tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5">No. Invoice</th>
                                <th class="px-5 py-3.5">Vendor</th>
                                <th class="px-4 py-3.5">No. PO</th>
                                <th class="px-4 py-3.5">Tgl Invoice</th>
                                <th class="px-5 py-3.5 text-right">Total Nilai</th>
                                <th class="px-4 py-3.5 text-center">Status</th>
                                <th class="px-4 py-3.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            @foreach ($paymentRequest->invoices as $inv)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                                    <td class="px-5 py-4 font-bold text-slate-900 dark:text-white">
                                        {{ $inv->invoice_number }}
                                    </td>
                                    <td class="px-5 py-4 text-xs font-semibold text-slate-800 dark:text-zinc-200">
                                        {{ $inv->vendor->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-xs font-mono text-slate-600 dark:text-zinc-400">
                                        {{ $inv->purchaseOrder?->po_number ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-xs text-slate-500">
                                        {{ $inv->invoice_date?->format('d/m/Y') }}
                                    </td>
                                    <td class="px-5 py-4 text-right font-black text-slate-900 dark:text-white">
                                        Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <flux:badge size="sm" :color="$inv->status->badgeColor()">
                                            {{ $inv->status->label() }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <a href="{{ route('invoices.show', $inv) }}" wire:navigate class="px-2.5 py-1 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg dark:bg-indigo-950/60 dark:text-indigo-300 transition">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50/80 dark:bg-zinc-800/60 font-bold text-xs">
                            <tr>
                                <td colspan="4" class="px-5 py-4 text-right uppercase tracking-wider text-slate-500">Total Keseluruhan:</td>
                                <td class="px-5 py-4 text-right text-base font-black text-indigo-600 dark:text-indigo-400">
                                    Rp {{ number_format($paymentRequest->total_amount, 0, ',', '.') }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($paymentRequest->notes)
                    <div class="p-4 bg-slate-50 dark:bg-zinc-800/40 border-t border-slate-100 dark:border-zinc-800 text-xs text-slate-600 dark:text-zinc-300">
                        <strong class="font-bold text-slate-800 dark:text-zinc-200">Catatan Pengajuan:</strong> {{ $paymentRequest->notes }}
                    </div>
                @endif
            </div>

            {{-- Lampiran Dokumen Card --}}
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs space-y-4">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white border-b border-slate-100 dark:border-zinc-800 pb-3">Dokumen Lampiran Berkas</h3>

                <x-document-preview-card :documents="$paymentRequest->documents" />

                @if ($isSpv)
                    <div class="rounded-2xl border border-dashed border-slate-300 dark:border-zinc-700 p-4 bg-slate-50/50 dark:bg-zinc-900/40 space-y-3">
                        <h4 class="text-xs font-bold text-slate-800 dark:text-zinc-200">Unggah Lampiran Baru</h4>
                        <form wire:submit="uploadDocument" class="space-y-3">
                            <div>
                                <x-file-upload-camera
                                    model="document_file"
                                    :file="$document_file"
                                    label="Pilih Foto / Berkas Lampiran"
                                    help="Ambil foto dokumen fisik atau pilih file PDF/JPG dari galeri."
                                />
                            </div>
                            <div>
                                <input type="text" wire:model="document_notes" placeholder="Keterangan dokumen (opsional)..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white">
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="w-full sm:w-auto px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-xs transition" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="uploadDocument">Unggah Dokumen</span>
                                    <span wire:loading wire:target="uploadDocument">Mengunggah...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right 1 col: Stage Transition & Timeline --}}
        <div class="space-y-6">
            {{-- Stage Transition Form (SPV only, unposted) --}}
            @if ($canManageStage)
                <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs space-y-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Pembaruan Tahapan Berkas</h3>
                        <p class="text-xs text-slate-400">Ubah posisi berkas pengajuan (ACC, Finance, dsb.)</p>
                    </div>

                    <form wire:submit="updateStage" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Pilih Tahapan Baru</label>
                            <select wire:model="new_stage_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white">
                                @foreach ($stages as $stage)
                                    <option value="{{ $stage->id }}">{{ $stage->name }} (Tahap {{ $stage->sort_order }})</option>
                                @endforeach
                            </select>
                            @error('new_stage_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Catatan Perubahan</label>
                            <textarea wire:model="stage_notes" rows="3" placeholder="Contoh: Berkas telah disetujui ACC dan diteruskan ke kasir..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white"></textarea>
                            @error('stage_notes') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-xs transition" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="updateStage">Perbarui Tahapan</span>
                            <span wire:loading wire:target="updateStage">Menyimpan...</span>
                        </button>
                    </form>
                </div>
            @endif

            {{-- Timeline / History Log --}}
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-zinc-800 pb-3">Riwayat Tahapan (Audit Trail)</h3>

                @if ($paymentRequest->stageLogs->isNotEmpty())
                    <div class="relative border-l-2 border-indigo-100 dark:border-zinc-800 ml-3 space-y-6">
                        @foreach ($paymentRequest->stageLogs as $log)
                            <div class="relative pl-6">
                                {{-- Bullet --}}
                                <div class="absolute -left-1.5 top-1 size-3 rounded-full border-2 border-white bg-indigo-600 dark:border-zinc-900"></div>

                                <div class="space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-900 dark:text-white">
                                            {{ $log->toStage?->name ?? 'Tahap Baru' }}
                                        </span>
                                        <span class="text-[10px] text-slate-400">
                                            {{ $log->created_at?->diffForHumans() }}
                                        </span>
                                    </div>

                                    @if ($log->fromStage)
                                        <p class="text-xs text-slate-500">
                                            Dari: <span class="italic font-medium">{{ $log->fromStage->name }}</span>
                                        </p>
                                    @else
                                        <p class="text-xs text-slate-500 italic">Pengajuan Baru Dibuat</p>
                                    @endif

                                    @if ($log->notes)
                                        <div class="rounded-lg bg-slate-50 p-2 text-xs text-slate-700 dark:bg-zinc-800/80 dark:text-zinc-300 border border-slate-100 dark:border-zinc-800">
                                            {{ $log->notes }}
                                        </div>
                                    @endif

                                    <p class="text-[10px] text-slate-400">
                                        Oleh: {{ $log->changer?->name ?? 'Sistem' }} • {{ $log->created_at?->translatedFormat('d M Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-xs text-slate-400 text-center py-4">Belum ada catatan riwayat tahapan.</div>
                @endif
            </div>
        </div>
    </div>
</div>
