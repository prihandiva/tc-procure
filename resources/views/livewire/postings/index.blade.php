<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Dokumen Posting SAP</flux:heading>
            <flux:text class="text-xs text-zinc-500">
                Pencatatan nomor dokumen akuntansi SAP dan konfirmasi pelunasan tagihan catering
            </flux:text>
        </div>

        @if ($isSpv)
            <div class="flex items-center gap-2">
                <flux:button variant="primary" icon="plus" href="{{ route('postings.create') }}" wire:navigate>
                    Catat Posting Baru
                </flux:button>
            </div>
        @endif
    </div>

    {{-- Filter Bar --}}
    <flux:card class="space-y-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari No. Dokumen SAP, No. PR, atau Catatan..."
                    icon="magnifying-glass"
                />
            </div>

            <div>
                <flux:input type="date" wire:model.live="date_from" label="Dari Tanggal" />
            </div>

            <div>
                <flux:input type="date" wire:model.live="date_to" label="Sampai Tanggal" />
            </div>
        </div>
    </flux:card>

    {{-- Table of Postings --}}
    <flux:card class="space-y-4">
        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 text-left text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs uppercase font-medium text-zinc-500">
                    <tr>
                        <th class="px-4 py-3">No. Dokumen SAP</th>
                        <th class="px-4 py-3">Tgl Posting</th>
                        <th class="px-4 py-3 text-right">Total Dibayar</th>
                        <th class="px-4 py-3">Payment Request Terkait</th>
                        <th class="px-4 py-3">Vendor</th>
                        <th class="px-4 py-3 text-center">Bukti Bayar</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($postings as $posting)
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40">
                            <td class="px-4 py-3 font-bold text-zinc-900 dark:text-white">
                                {{ $posting->document_number }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ $posting->posting_date?->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($posting->total_paid, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="space-y-1">
                                    @foreach ($posting->paymentRequests as $pr)
                                        <div class="text-xs font-medium text-zinc-800 dark:text-zinc-200">
                                            {{ $pr->number }}
                                        </div>
                                    @endforeach
                                    <div class="text-[11px] text-zinc-400">
                                        {{ $posting->paymentRequests->count() }} PR
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
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
                            <td class="px-4 py-3 text-center">
                                @if ($posting->documents->isNotEmpty())
                                    <flux:badge color="green" size="sm">Ada ({{ $posting->documents->count() }})</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Tidak Ada</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <flux:button variant="subtle" size="xs" href="{{ route('postings.show', $posting) }}" wire:navigate>
                                    Detail
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-zinc-500">
                                Belum ada dokumen posting SAP yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $postings->links() }}
        </div>
    </flux:card>
</div>

