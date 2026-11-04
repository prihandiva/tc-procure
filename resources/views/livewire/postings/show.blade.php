<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <flux:button variant="subtle" icon="arrow-left" href="{{ route('postings.index') }}" wire:navigate>
                Kembali
            </flux:button>
            <div>
                <div class="flex items-center gap-3">
                    <flux:heading size="xl" class="font-bold tracking-tight">
                        Dokumen SAP #{{ $posting->document_number }}
                    </flux:heading>
                    <flux:badge color="green" size="sm">Posted SAP</flux:badge>
                </div>
                <flux:text class="text-xs text-zinc-500 mt-1">
                    Tanggal Posting: {{ $posting->posting_date?->translatedFormat('d F Y') }}
                </flux:text>
            </div>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="rounded-xl border border-emerald-500/20 bg-emerald-50/50 p-4 text-sm text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card class="space-y-1">
            <flux:text class="text-xs font-medium uppercase text-zinc-500">Total Nominal Dibayar</flux:text>
            <flux:heading size="xl" class="font-bold text-emerald-600 dark:text-emerald-400">
                Rp {{ number_format($posting->total_paid, 0, ',', '.') }}
            </flux:heading>
            <flux:text class="text-xs text-zinc-500">Nilai pelunasan di SAP</flux:text>
        </flux:card>

        <flux:card class="space-y-1">
            <flux:text class="text-xs font-medium uppercase text-zinc-500">Payment Request</flux:text>
            <flux:heading size="xl" class="font-bold text-zinc-900 dark:text-white">
                {{ $posting->paymentRequests->count() }}
            </flux:heading>
            <flux:text class="text-xs text-zinc-500">Berkas pengajuan</flux:text>
        </flux:card>

        <flux:card class="space-y-1">
            <flux:text class="text-xs font-medium uppercase text-zinc-500">Total Invoice</flux:text>
            <flux:heading size="xl" class="font-bold text-zinc-900 dark:text-white">
                {{ $invoices->count() }}
            </flux:heading>
            <flux:text class="text-xs text-zinc-500">Semua berstatus LUNAS</flux:text>
        </flux:card>

        <flux:card class="space-y-1">
            <flux:text class="text-xs font-medium uppercase text-zinc-500">Vendor Terkait</flux:text>
            <flux:heading size="sm" class="font-semibold text-zinc-800 dark:text-zinc-200">
                {{ $vendors->pluck('name')->implode(', ') ?: '-' }}
            </flux:heading>
            <flux:text class="text-xs text-zinc-500">{{ $vendors->count() }} Rekanan</flux:text>
        </flux:card>
    </div>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Linked Payment Requests Table --}}
            <flux:card class="space-y-4">
                <flux:heading size="lg" class="font-bold">Payment Request Terkait</flux:heading>
                <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <table class="min-w-full divide-y divide-zinc-200 text-left text-sm dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs uppercase font-medium text-zinc-500">
                            <tr>
                                <th class="px-4 py-3">No. PR</th>
                                <th class="px-4 py-3">Tgl Diajukan</th>
                                <th class="px-4 py-3">Tahapan</th>
                                <th class="px-4 py-3 text-center">Jml Invoice</th>
                                <th class="px-4 py-3 text-right">Total Nilai</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($posting->paymentRequests as $pr)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40">
                                    <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-white">
                                        {{ $pr->number }}
                                    </td>
                                    <td class="px-4 py-3 text-zinc-500">
                                        {{ $pr->submitted_at?->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <flux:badge size="sm" color="indigo">
                                            {{ $pr->currentStage?->name ?? '-' }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-4 py-3 text-center text-zinc-600 dark:text-zinc-300">
                                        {{ $pr->invoices->count() }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-zinc-900 dark:text-white">
                                        Rp {{ number_format($pr->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <flux:button variant="subtle" size="xs" href="{{ route('payment-requests.show', $pr) }}" wire:navigate>
                                            Detail PR
                                        </flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </flux:card>

            {{-- Linked Invoices Table --}}
            <flux:card class="space-y-4">
                <flux:heading size="lg" class="font-bold">Daftar Invoice Lunas</flux:heading>
                <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <table class="min-w-full divide-y divide-zinc-200 text-left text-sm dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs uppercase font-medium text-zinc-500">
                            <tr>
                                <th class="px-4 py-3">No. Invoice</th>
                                <th class="px-4 py-3">Vendor</th>
                                <th class="px-4 py-3">No. PO</th>
                                <th class="px-4 py-3 text-right">Nilai Tagihan</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($invoices as $inv)
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
                                    <td class="px-4 py-3 text-right font-semibold text-zinc-900 dark:text-white">
                                        Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <flux:badge size="sm" color="green">
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
                    </table>
                </div>
            </flux:card>
        </div>

        {{-- Right 1 col: Details & Documents --}}
        <div class="space-y-6">
            <flux:card class="space-y-4">
                <flux:heading size="base" class="font-bold">Informasi Dokumen</flux:heading>

                <div class="space-y-3 text-sm">
                    <div>
                        <flux:text class="text-xs text-zinc-500">Nomor Dokumen SAP</flux:text>
                        <flux:text class="font-bold text-zinc-900 dark:text-white">{{ $posting->document_number }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs text-zinc-500">Tanggal Posting</flux:text>
                        <flux:text class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $posting->posting_date?->format('d/m/Y') }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs text-zinc-500">Total Dibayar</flux:text>
                        <flux:text class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($posting->total_paid, 0, ',', '.') }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs text-zinc-500">Dicatat Oleh</flux:text>
                        <flux:text class="text-zinc-700 dark:text-zinc-300">{{ $posting->creator?->name ?? '-' }}</flux:text>
                    </div>
                    @if ($posting->notes)
                        <div>
                            <flux:text class="text-xs text-zinc-500">Catatan</flux:text>
                            <div class="rounded-lg bg-zinc-50 p-2 text-xs text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $posting->notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </flux:card>

            {{-- Proof of Payment Document --}}
            <flux:card class="space-y-4">
                <flux:heading size="base" class="font-bold">Bukti Bayar / Dokumen SAP</flux:heading>

                @if ($posting->documents->isNotEmpty())
                    <div class="divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                        @foreach ($posting->documents as $doc)
                            <div class="flex items-center justify-between p-3">
                                <div class="flex items-center gap-3">
                                    <flux:icon.document-text class="size-5 text-zinc-400" />
                                    <div>
                                        <flux:text class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                                            {{ $doc->original_name }}
                                        </flux:text>
                                        <flux:text class="text-xs text-zinc-500">
                                            {{ $doc->formatted_size }}
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
                    <flux:text class="text-sm text-zinc-500">Belum ada file bukti bayar yang diunggah.</flux:text>
                @endif

                @if ($isSpv)
                    <div class="rounded-xl border border-dashed border-zinc-300 p-4 dark:border-zinc-700">
                        <flux:heading size="sm" class="font-semibold mb-2">Unggah Bukti Bayar Tambahan</flux:heading>
                        <form wire:submit="uploadDocument" class="space-y-3">
                            <div>
                                <flux:input type="file" wire:model="document_file" label="File Bukti Bayar (PDF/JPG/PNG, Max 5MB)" />
                                @error('document_file') <flux:text class="text-xs text-rose-500 mt-1">{{ $message }}</flux:text> @enderror
                            </div>
                            <div>
                                <flux:input wire:model="document_notes" label="Keterangan (Opsional)" placeholder="Contoh: Bukti transfer kasir / scan SAP" />
                            </div>
                            <div class="flex justify-end">
                                <flux:button type="submit" variant="primary" size="sm" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="uploadDocument">Unggah File</span>
                                    <span wire:loading wire:target="uploadDocument">Mengunggah...</span>
                                </flux:button>
                            </div>
                        </form>
                    </div>
                @endif
            </flux:card>
        </div>
    </div>
</div>

