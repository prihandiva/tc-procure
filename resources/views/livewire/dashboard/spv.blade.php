<div class="space-y-6">
    {{-- Header & Quick Actions --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Dashboard Pengadaan Training Center</flux:heading>
            <flux:text class="text-xs text-zinc-500">
                Ringkasan eksekutif pengadaan snack & catering external • {{ now()->translatedFormat('l, d F Y') }}
            </flux:text>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <flux:button variant="primary" icon="plus" href="{{ route('purchase-orders.create') }}" wire:navigate>
                Terbitkan PO Baru
            </flux:button>
            <flux:button variant="subtle" icon="clipboard-document-check" href="{{ route('invoices.index') }}" wire:navigate>
                Verifikasi Tagihan
            </flux:button>
        </div>
    </div>

    {{-- Pending Action Alert Cards (Prioritas Kerja Hari Ini) --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        {{-- DO Menunggu Konfirmasi --}}
        <flux:card class="border-amber-500/30 bg-amber-50/40 dark:bg-amber-950/20 flex items-center justify-between">
            <div class="space-y-1">
                <flux:text class="text-xs font-semibold uppercase text-amber-700 dark:text-amber-400">DO Menunggu Konfirmasi</flux:text>
                <flux:heading size="xl" class="font-bold text-amber-900 dark:text-amber-200">
                    {{ $pendingDeliveriesCount }}
                </flux:heading>
                <flux:text class="text-xs text-amber-700/80 dark:text-amber-400/80">Pengiriman fisik dari vendor</flux:text>
            </div>
            <flux:button variant="subtle" size="sm" href="{{ route('deliveries.index', ['status' => 'submitted']) }}" wire:navigate>
                Periksa DO
            </flux:button>
        </flux:card>

        {{-- Tagihan Menunggu 3-Way Match --}}
        <flux:card class="border-indigo-500/30 bg-indigo-50/40 dark:bg-indigo-950/20 flex items-center justify-between">
            <div class="space-y-1">
                <flux:text class="text-xs font-semibold uppercase text-indigo-700 dark:text-indigo-400">Invoice Menunggu 3-Way Match</flux:text>
                <flux:heading size="xl" class="font-bold text-indigo-900 dark:text-indigo-200">
                    {{ $pendingInvoicesCount }}
                </flux:heading>
                <flux:text class="text-xs text-indigo-700/80 dark:text-indigo-400/80">Tagihan baru diajukan rekanan</flux:text>
            </div>
            <flux:button variant="subtle" size="sm" href="{{ route('invoices.index', ['status' => 'submitted']) }}" wire:navigate>
                Cocokkan
            </flux:button>
        </flux:card>

        {{-- Payment Request Aktif / Menunggu Posting --}}
        <flux:card class="border-sky-500/30 bg-sky-50/40 dark:bg-sky-950/20 flex items-center justify-between">
            <div class="space-y-1">
                <flux:text class="text-xs font-semibold uppercase text-sky-700 dark:text-sky-400">PR Dalam Proses (Belum Posting)</flux:text>
                <flux:heading size="xl" class="font-bold text-sky-900 dark:text-sky-200">
                    {{ $unpostedPrsCount }}
                </flux:heading>
                <flux:text class="text-xs text-sky-700/80 dark:text-sky-400/80">Proses ACC & Finance</flux:text>
            </div>
            <flux:button variant="subtle" size="sm" href="{{ route('payment-requests.index', ['posting_status' => 'unposted']) }}" wire:navigate>
                Lihat Berkas
            </flux:button>
        </flux:card>
    </div>

    {{-- Global Procurement Financial KPIs --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card class="space-y-1">
            <flux:text class="text-xs font-medium uppercase text-zinc-500">Nilai Kontrak PO Aktif</flux:text>
            <flux:heading size="xl" class="font-bold text-zinc-900 dark:text-white">
                Rp {{ number_format($totalPoValue, 0, ',', '.') }}
            </flux:heading>
            <flux:text class="text-xs text-zinc-500">{{ $activePoCount }} PO Berjalan</flux:text>
        </flux:card>

        <flux:card class="space-y-1">
            <flux:text class="text-xs font-medium uppercase text-zinc-500">Nilai Barang Diterima (Realisasi)</flux:text>
            <flux:heading size="xl" class="font-bold text-emerald-600 dark:text-emerald-400">
                Rp {{ number_format($totalDeliveredValue, 0, ',', '.') }}
            </flux:heading>
            <flux:text class="text-xs text-zinc-500">
                @if ($totalPoValue > 0)
                    {{ number_format(($totalDeliveredValue / $totalPoValue) * 100, 1) }}% dari plafon PO
                @else
                    0%
                @endif
            </flux:text>
        </flux:card>

        <flux:card class="space-y-1">
            <flux:text class="text-xs font-medium uppercase text-zinc-500">Sisa Kuota Belum Dikirim</flux:text>
            <flux:heading size="xl" class="font-bold text-amber-600 dark:text-amber-400">
                Rp {{ number_format($totalRemainingValue, 0, ',', '.') }}
            </flux:heading>
            <flux:text class="text-xs text-zinc-500">Dapat dikirim sampai masa berlaku PO</flux:text>
        </flux:card>

        <flux:card class="space-y-1">
            <flux:text class="text-xs font-medium uppercase text-zinc-500">Total Lunas SAP Posting</flux:text>
            <flux:heading size="xl" class="font-bold text-indigo-600 dark:text-indigo-400">
                Rp {{ number_format($totalPaidValue, 0, ',', '.') }}
            </flux:heading>
            <flux:text class="text-xs text-zinc-500">Pelunasan riil tercatat di SAP</flux:text>
        </flux:card>
    </div>

    {{-- Ringkasan Kinerja & Saldo Per Vendor --}}
    <flux:card class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg" class="font-bold">Status Saldo Pengadaan Per Rekanan (Vendor)</flux:heading>
                <flux:text class="text-xs text-zinc-500">
                    Monitoring batas kuota PO, pengiriman fisik, tagihan, dan sisa kewajiban pembayaran per vendor
                </flux:text>
            </div>
            <flux:button variant="subtle" size="sm" href="{{ route('purchase-orders.index') }}" wire:navigate>
                Semua PO
            </flux:button>
        </div>

        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 text-left text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs uppercase font-medium text-zinc-500">
                    <tr>
                        <th class="px-4 py-3">Nama Vendor</th>
                        <th class="px-4 py-3 text-center">PO Aktif</th>
                        <th class="px-4 py-3 text-right">Plafon PO (Rp)</th>
                        <th class="px-4 py-3 text-right">Diterima Fisik (Rp)</th>
                        <th class="px-4 py-3 text-right">Sisa Kuota (Rp)</th>
                        <th class="px-4 py-3 text-right">Total Ditagih (Rp)</th>
                        <th class="px-4 py-3 text-right">Lunas SAP (Rp)</th>
                        <th class="px-4 py-3 text-right">Sisa Tagihan (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($vendorSummaries as $v)
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40">
                            <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-white">
                                {{ $v['name'] }}
                            </td>
                            <td class="px-4 py-3 text-center text-zinc-600 dark:text-zinc-300">
                                {{ $v['active_po_count'] }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-zinc-800 dark:text-zinc-200">
                                Rp {{ number_format($v['po_value'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($v['delivered_value'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-amber-600 dark:text-amber-400">
                                Rp {{ number_format($v['remaining_value'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-zinc-800 dark:text-zinc-200">
                                Rp {{ number_format($v['invoiced_value'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-indigo-600 dark:text-indigo-400">
                                Rp {{ number_format($v['paid_value'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-400">
                                Rp {{ number_format($v['unpaid_value'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-zinc-500">
                                Belum ada data rekanan vendor aktif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>

    {{-- Recent Transactions Activity (Feed 2 Kolom) --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Recent Deliveries (DO) --}}
        <flux:card class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="base" class="font-bold">Pengiriman Terbaru (Surat Jalan DO)</flux:heading>
                <flux:button variant="subtle" size="xs" href="{{ route('deliveries.index') }}" wire:navigate>
                    Lihat Semua
                </flux:button>
            </div>

            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($recentDeliveries as $deliv)
                    <div class="flex items-center justify-between py-3">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-sm text-zinc-900 dark:text-white">
                                    {{ $deliv->delivery_code }}
                                </span>
                                <flux:badge size="sm" :color="$deliv->status->color()">
                                    {{ $deliv->status->label() }}
                                </flux:badge>
                            </div>
                            <div class="text-xs text-zinc-500">
                                {{ $deliv->vendor->name ?? '-' }} • PO: {{ $deliv->purchaseOrder?->po_number ?? '-' }} • {{ $deliv->delivery_date?->format('d/m/Y') }}
                            </div>
                        </div>
                        <flux:button variant="subtle" size="xs" href="{{ route('deliveries.show', $deliv) }}" wire:navigate>
                            Detail
                        </flux:button>
                    </div>
                @empty
                    <div class="py-4 text-center text-xs text-zinc-500">
                        Belum ada aktivitas pengiriman.
                    </div>
                @endforelse
            </div>
        </flux:card>

        {{-- Recent Invoices (Tagihan) --}}
        <flux:card class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="base" class="font-bold">Tagihan Terbaru (Invoice)</flux:heading>
                <flux:button variant="subtle" size="xs" href="{{ route('invoices.index') }}" wire:navigate>
                    Lihat Semua
                </flux:button>
            </div>

            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($recentInvoices as $inv)
                    <div class="flex items-center justify-between py-3">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-sm text-zinc-900 dark:text-white">
                                    {{ $inv->invoice_number }}
                                </span>
                                <flux:badge size="sm" :color="$inv->status->badgeColor()">
                                    {{ $inv->status->label() }}
                                </flux:badge>
                            </div>
                            <div class="text-xs text-zinc-500">
                                {{ $inv->vendor->name ?? '-' }} • Rp {{ number_format($inv->total_amount, 0, ',', '.') }} • {{ $inv->invoice_date?->format('d/m/Y') }}
                            </div>
                        </div>
                        <flux:button variant="subtle" size="xs" href="{{ route('invoices.show', $inv) }}" wire:navigate>
                            Detail
                        </flux:button>
                    </div>
                @empty
                    <div class="py-4 text-center text-xs text-zinc-500">
                        Belum ada invoice diajukan.
                    </div>
                @endforelse
            </div>
        </flux:card>
    </div>
</div>
