<div class="space-y-6">
    {{-- Header & Navigation --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <flux:button variant="subtle" icon="arrow-left" href="{{ route('payment-requests.index') }}" wire:navigate>
                Kembali
            </flux:button>
            <div>
                <div class="flex items-center gap-3">
                    <flux:heading size="xl" class="font-bold tracking-tight">
                        {{ $paymentRequest->number }}
                    </flux:heading>
                    @if ($paymentRequest->isPosted())
                        <flux:badge color="green" size="sm">Sudah Diposting SAP</flux:badge>
                    @else
                        <flux:badge color="amber" size="sm">Menunggu Posting SAP</flux:badge>
                    @endif
                    @if ($paymentRequest->currentStage)
                        <flux:badge color="indigo" size="sm">{{ $paymentRequest->currentStage->name }}</flux:badge>
                    @endif
                </div>
                <flux:text class="text-xs text-zinc-500 mt-1">
                    Diajukan pada {{ $paymentRequest->submitted_at?->translatedFormat('d F Y H:i') ?? '-' }}
                </flux:text>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if ($isSpv && ! $paymentRequest->isPosted())
                <flux:button variant="primary" icon="check-badge" href="{{ route('postings.create') }}" wire:navigate>
                    Posting ke SAP
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="rounded-xl border border-emerald-500/20 bg-emerald-50/50 p-4 text-sm text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="rounded-xl border border-rose-500/20 bg-rose-50/50 p-4 text-sm text-rose-800 dark:bg-rose-950/30 dark:text-rose-300">
            {{ session('error') }}
        </div>
    @endif

    {{-- KPI Metric Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card class="space-y-1">
            <flux:text class="text-xs font-medium uppercase text-zinc-500">Total Nilai Tagihan</flux:text>
            <flux:heading size="xl" class="font-bold text-zinc-900 dark:text-white">
                Rp {{ number_format($paymentRequest->total_amount, 0, ',', '.') }}
            </flux:heading>
            <flux:text class="text-xs text-zinc-500">Dari {{ $paymentRequest->invoices->count() }} Invoice</flux:text>
        </flux:card>

        <flux:card class="space-y-1">
            <flux:text class="text-xs font-medium uppercase text-zinc-500">Tahapan Saat Ini</flux:text>
            <flux:heading size="lg" class="font-bold text-indigo-600 dark:text-indigo-400">
                {{ $paymentRequest->currentStage?->name ?? '-' }}
            </flux:heading>
            <flux:text class="text-xs text-zinc-500">Urutan: {{ $paymentRequest->currentStage?->sort_order ?? 0 }}</flux:text>
        </flux:card>

        <flux:card class="space-y-1">
            <flux:text class="text-xs font-medium uppercase text-zinc-500">Status SAP Posting</flux:text>
            @if ($paymentRequest->isPosted())
                <div class="flex items-center gap-2">
                    <flux:badge color="green" size="sm">Tercatat</flux:badge>
                    <flux:text class="text-sm font-semibold text-zinc-900 dark:text-white">
                        {{ $paymentRequest->posting->document_number }}
                    </flux:text>
                </div>
                <flux:text class="text-xs text-zinc-500">Tgl: {{ $paymentRequest->posting->posting_date?->format('d/m/Y') }}</flux:text>
            @else
                <flux:badge color="amber" size="sm">Belum Diposting</flux:badge>
                <flux:text class="text-xs text-zinc-500">Menunggu penyelesaian di SAP</flux:text>
            @endif
        </flux:card>

        <flux:card class="space-y-1">
            <flux:text class="text-xs font-medium uppercase text-zinc-500">Vendor Terkait</flux:text>
            <flux:heading size="sm" class="font-semibold text-zinc-800 dark:text-zinc-200">
                {{ $paymentRequest->vendors->pluck('name')->implode(', ') ?: '-' }}
            </flux:heading>
            <flux:text class="text-xs text-zinc-500">{{ $paymentRequest->vendors->count() }} Rekanan</flux:text>
        </flux:card>
    </div>

    {{-- Posting Information Banner (if posted) --}}
    @if ($paymentRequest->isPosted())
        <flux:card class="border-emerald-500/30 bg-emerald-50/40 dark:bg-emerald-950/20">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <flux:icon.check-circle class="size-5 text-emerald-600 dark:text-emerald-400" />
                        <flux:heading size="base" class="font-bold text-emerald-900 dark:text-emerald-200">
                            Payment Request Telah Diposting ke SAP (Doc: {{ $paymentRequest->posting->document_number }})
                        </flux:heading>
                    </div>
                    <flux:text class="text-xs text-emerald-700 dark:text-emerald-300">
                        Tanggal Posting: <strong>{{ $paymentRequest->posting->posting_date?->translatedFormat('d F Y') }}</strong> | 
                        Total Dibayar: <strong>Rp {{ number_format($paymentRequest->posting->total_paid, 0, ',', '.') }}</strong>
                        @if ($paymentRequest->posting->notes)
                            | Catatan: {{ $paymentRequest->posting->notes }}
                        @endif
                    </flux:text>
                </div>
                <div class="flex items-center gap-2">
                    <flux:button variant="subtle" size="sm" href="{{ route('postings.show', $paymentRequest->posting) }}" wire:navigate>
                        Lihat Dokumen Posting
                    </flux:button>
                </div>
            </div>
        </flux:card>
    @endif

    {{-- Main Content Grid: Invoices & Stage History --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left 2 cols: Invoices included --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Invoices Table Card --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg" class="font-bold">Daftar Tagihan (Invoices)</flux:heading>
                        <flux:text class="text-xs text-zinc-500">Invoice yang digabungkan dalam berkas pengajuan ini</flux:text>
                    </div>
                    <flux:badge color="zinc">{{ $paymentRequest->invoices->count() }} Invoice</flux:badge>
                </div>

                <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <table class="min-w-full divide-y divide-zinc-200 text-left text-sm dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs uppercase font-medium text-zinc-500">
                            <tr>
                                <th class="px-4 py-3">No. Invoice</th>
                                <th class="px-4 py-3">Vendor</th>
                                <th class="px-4 py-3">No. PO</th>
                                <th class="px-4 py-3">Tgl Invoice</th>
                                <th class="px-4 py-3 text-right">Total Nilai</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($paymentRequest->invoices as $inv)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40">
                                    <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-white">
                                        {{ $inv->invoice_number }}
                                    </td>
                                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                        {{ $inv->vendor->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                        {{ $inv->purchaseOrder?->po_number ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-zinc-500">
                                        {{ $inv->invoice_date?->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-zinc-900 dark:text-white">
                                        Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <flux:badge size="sm" :color="$inv->status->badgeColor()">
                                            {{ $inv->status->label() }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <flux:button variant="subtle" size="xs" href="{{ route('invoices.show', $inv) }}" wire:navigate>
                                            Detail
                                        </flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-zinc-50 font-semibold dark:bg-zinc-800/80">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-right text-xs uppercase text-zinc-600 dark:text-zinc-400">Total Keseluruhan:</td>
                                <td class="px-4 py-3 text-right text-base text-zinc-900 dark:text-white">
                                    Rp {{ number_format($paymentRequest->total_amount, 0, ',', '.') }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($paymentRequest->notes)
                    <div class="rounded-lg bg-zinc-50 p-3 text-xs text-zinc-600 dark:bg-zinc-800/60 dark:text-zinc-400">
                        <span class="font-semibold text-zinc-700 dark:text-zinc-300">Catatan Pengajuan:</span> {{ $paymentRequest->notes }}
                    </div>
                @endif
            </flux:card>

            {{-- Lampiran Dokumen Card --}}
            <flux:card class="space-y-4">
                <flux:heading size="lg" class="font-bold">Dokumen Lampiran Berkas</flux:heading>

                @if ($paymentRequest->documents->isNotEmpty())
                    <div class="divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                        @foreach ($paymentRequest->documents as $doc)
                            <div class="flex items-center justify-between p-3">
                                <div class="flex items-center gap-3">
                                    <flux:icon.document-text class="size-5 text-zinc-400" />
                                    <div>
                                        <flux:text class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                                            {{ $doc->original_name }}
                                        </flux:text>
                                        <flux:text class="text-xs text-zinc-500">
                                            {{ $doc->formatted_size }} • Diunggah {{ $doc->created_at->diffForHumans() }}
                                            @if ($doc->notes) • Catatan: {{ $doc->notes }} @endif
                                        </flux:text>
                                    </div>
                                </div>
                                <flux:button variant="subtle" size="sm" icon="arrow-down-tray" href="{{ route('documents.show', $doc) }}" target="_blank">
                                    Unduh
                                </flux:button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <flux:text class="text-sm text-zinc-500">Belum ada dokumen lampiran tambahan.</flux:text>
                @endif

                @if ($isSpv)
                    <div class="rounded-xl border border-dashed border-zinc-300 p-4 dark:border-zinc-700">
                        <flux:heading size="sm" class="font-semibold mb-2">Unggah Lampiran Baru</flux:heading>
                        <form wire:submit="uploadDocument" class="space-y-3">
                            <div>
                                <flux:input type="file" wire:model="document_file" label="Pilih File (PDF/JPG/PNG, Maks. 5MB)" />
                                @error('document_file') <flux:text class="text-xs text-rose-500 mt-1">{{ $message }}</flux:text> @enderror
                            </div>
                            <div>
                                <flux:input wire:model="document_notes" label="Keterangan Dokumen (Opsional)" placeholder="Contoh: Bukti tanda terima berkas dari Finance" />
                            </div>
                            <div class="flex justify-end">
                                <flux:button type="submit" variant="primary" size="sm" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="uploadDocument">Unggah Dokumen</span>
                                    <span wire:loading wire:target="uploadDocument">Mengunggah...</span>
                                </flux:button>
                            </div>
                        </form>
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- Right 1 col: Stage Transition & Timeline --}}
        <div class="space-y-6">
            {{-- Stage Transition Form (SPV only, unposted) --}}
            @if ($canManageStage)
                <flux:card class="space-y-4">
                    <div>
                        <flux:heading size="base" class="font-bold">Pembaruan Tahapan Berkas</flux:heading>
                        <flux:text class="text-xs text-zinc-500">Ubah posisi berkas pengajuan (ACC, Finance, dsb.)</flux:text>
                    </div>

                    <form wire:submit="updateStage" class="space-y-4">
                        <div>
                            <flux:select wire:model="new_stage_id" label="Pilih Tahapan Baru">
                                @foreach ($stages as $stage)
                                    <option value="{{ $stage->id }}">{{ $stage->name }} (Tahap {{ $stage->sort_order }})</option>
                                @endforeach
                            </flux:select>
                            @error('new_stage_id') <flux:text class="text-xs text-rose-500 mt-1">{{ $message }}</flux:text> @enderror
                        </div>

                        <div>
                            <flux:textarea wire:model="stage_notes" label="Catatan Perubahan" placeholder="Contoh: Berkas telah disetujui ACC dan diteruskan ke kasir/finance..." rows="3" />
                            @error('stage_notes') <flux:text class="text-xs text-rose-500 mt-1">{{ $message }}</flux:text> @enderror
                        </div>

                        <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="updateStage">Perbarui Tahapan</span>
                            <span wire:loading wire:target="updateStage">Menyimpan...</span>
                        </flux:button>
                    </form>
                </flux:card>
            @endif

            {{-- Timeline / History Log --}}
            <flux:card class="space-y-4">
                <flux:heading size="base" class="font-bold">Riwayat Tahapan (Audit Trail)</flux:heading>

                @if ($paymentRequest->stageLogs->isNotEmpty())
                    <div class="relative border-l border-zinc-200 dark:border-zinc-700 ml-3 space-y-6">
                        @foreach ($paymentRequest->stageLogs as $log)
                            <div class="relative pl-6">
                                {{-- Bullet --}}
                                <div class="absolute -left-1.5 top-1 size-3 rounded-full border-2 border-white bg-indigo-600 dark:border-zinc-800"></div>

                                <div class="space-y-1">
                                    <div class="flex items-center justify-between">
                                        <flux:text class="text-xs font-semibold text-zinc-900 dark:text-white">
                                            {{ $log->toStage?->name ?? 'Tahap Baru' }}
                                        </flux:text>
                                        <flux:text class="text-[10px] text-zinc-400">
                                            {{ $log->created_at?->diffForHumans() }}
                                        </flux:text>
                                    </div>

                                    @if ($log->fromStage)
                                        <flux:text class="text-xs text-zinc-500">
                                            Dari: <span class="italic">{{ $log->fromStage->name }}</span>
                                        </flux:text>
                                    @else
                                        <flux:text class="text-xs text-zinc-500 italic">Pengajuan Baru Dibuat</flux:text>
                                    @endif

                                    @if ($log->notes)
                                        <div class="rounded bg-zinc-50 p-2 text-xs text-zinc-600 dark:bg-zinc-800/80 dark:text-zinc-300">
                                            {{ $log->notes }}
                                        </div>
                                    @endif

                                    <flux:text class="text-[11px] text-zinc-400">
                                        Oleh: {{ $log->changer?->name ?? 'Sistem' }} • {{ $log->created_at?->translatedFormat('d M Y H:i') }}
                                    </flux:text>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <flux:text class="text-xs text-zinc-500">Belum ada catatan riwayat tahapan.</flux:text>
                @endif
            </flux:card>
        </div>
    </div>
</div>

