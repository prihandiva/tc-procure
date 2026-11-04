<div class="space-y-6">
    {{-- Header & Quick Actions --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl" class="font-bold tracking-tight">Portal Rekanan: {{ $vendor->name ?? 'Vendor' }}</flux:heading>
            </div>
            <flux:text class="text-xs text-zinc-500 mt-0.5">
                Monitoring alur pengiriman harian, sisa saldo PO, dan status pembayaran tagihan catering
            </flux:text>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <flux:button variant="primary" icon="plus" href="{{ route('deliveries.create') }}" wire:navigate>
                Input Surat Jalan (DO)
            </flux:button>
            <flux:button variant="subtle" icon="document-plus" href="{{ route('invoices.create') }}" wire:navigate>
                Buat Tagihan (Invoice)
            </flux:button>
        </div>
    </div>

    {{-- Alert Siap Tagih (Uninvoiced Confirmed DOs) --}}
    @if ($uninvoicedCount > 0)
        <flux:card class="border-emerald-500/40 bg-emerald-50/50 dark:bg-emerald-950/30">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <flux:icon.check-circle class="size-5 text-emerald-600 dark:text-emerald-400" />
                        <flux:heading size="base" class="font-bold text-emerald-900 dark:text-emerald-200">
                            Pengiriman Anda Telah Diterima SPV dan Siap Ditagihkan!
                        </flux:heading>
                    </div>
                    <flux:text class="text-xs text-emerald-700 dark:text-emerald-300">
                        Terdapat <strong>{{ $uninvoicedCount }} item</strong> pengiriman yang telah diverifikasi fisik oleh Training Center dengan total estimasi tagihan <strong>Rp {{ number_format($uninvoicedAmount, 0, ',', '.') }}</strong>.
                    </flux:text>
                </div>
                <div>
                    <flux:button variant="primary" size="sm" href="{{ route('invoices.create') }}" wire:navigate>
                        Buat Tagihan Sekarang
                    </flux:button>
                </div>
            </div>
        </flux:card>
    @endif

    {{-- Financial KPI Cards for Vendor --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card class="space-y-1">
            <flux:text class="text-xs font-medium uppercase text-zinc-500">Sisa Kuota Kirim PO</flux:text>
            <flux:heading size="xl" class="font-bold text-amber-600 dark:text-amber-400">
                Rp {{ number_format($totalRemainingValue, 0, ',', '.') }}
            </flux:heading>
            <flux:text class="text-xs text-zinc-500">Dari total kontrak PO Rp {{ number_format($totalPoValue, 0, ',', '.') }}</flux:text>
        </flux:card>

        <flux:card class="space-y-1">
            <flux:text class="text-xs font-medium uppercase text-zinc-500">Siap Ditagihkan (DO Approved)</flux:text>
            <flux:heading size="xl" class="font-bold text-emerald-600 dark:text-emerald-400">
                Rp {{ number_format($uninvoicedAmount, 0, ',', '.') }}
            </flux:heading>
            <flux:text class="text-xs text-zinc-500">{{ $uninvoicedCount }} Item pengiriman belum ditagih</flux:text>
        </flux:card>

        <flux:card class="space-y-1">
            <flux:text class="text-xs font-medium uppercase text-zinc-500">Tagihan Dalam Proses</flux:text>
            <flux:heading size="xl" class="font-bold text-indigo-600 dark:text-indigo-400">
                Rp {{ number_format($pendingApprovalAmount + $inProcessAmount, 0, ',', '.') }}
            </flux:heading>
            <flux:text class="text-xs text-zinc-500">Verifikasi SPV, ACC & Finance</flux:text>
        </flux:card>

        <flux:card class="space-y-1">
            <flux:text class="text-xs font-medium uppercase text-zinc-500">Total Pembayaran Lunas</flux:text>
            <flux:heading size="xl" class="font-bold text-zinc-900 dark:text-white">
                Rp {{ number_format($paidAmount, 0, ',', '.') }}
            </flux:heading>
            <flux:text class="text-xs text-zinc-500">Telah dibayar kasir / diposting SAP</flux:text>
        </flux:card>
    </div>

    {{-- Active POs for Vendor Table --}}
    <flux:card class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg" class="font-bold">Purchase Order (PO) Aktif Anda</flux:heading>
                <flux:text class="text-xs text-zinc-500">Daftar PO yang menjadi acuan pengiriman dan penagihan saat ini</flux:text>
            </div>
            <flux:badge color="zinc">{{ $activePos->count() }} PO Aktif</flux:badge>
        </div>

        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 text-left text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs uppercase font-medium text-zinc-500">
                    <tr>
                        <th class="px-4 py-3">No. PO</th>
                        <th class="px-4 py-3">Tgl PO</th>
                        <th class="px-4 py-3 text-right">Plafon PO</th>
                        <th class="px-4 py-3 text-right">Terkirim</th>
                        <th class="px-4 py-3 text-right">Sisa Saldo</th>
                        <th class="px-4 py-3 text-center">Pemenuhan</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($activePos as $po)
                        @php
                            $delivered = $po->delivered_amount;
                            $pct = $po->total_amount > 0 ? round(($delivered / $po->total_amount) * 100) : 0;
                            $remaining = max(0, $po->total_amount - $delivered);
                        @endphp
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40">
                            <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-white">
                                {{ $po->po_number }}
                                @if ($po->isNonPo())
                                    <flux:badge color="amber" size="xs">Non-PO</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-500">
                                {{ $po->po_date?->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-zinc-800 dark:text-zinc-200">
                                Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($delivered, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-amber-600 dark:text-amber-400">
                                Rp {{ number_format($remaining, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-16 bg-zinc-200 rounded-full h-2 dark:bg-zinc-700 overflow-hidden">
                                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min(100, $pct) }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">{{ $pct }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center space-x-1">
                                <flux:button variant="primary" size="xs" href="{{ route('deliveries.create', ['po_id' => $po->id]) }}" wire:navigate>
                                    Kirim DO
                                </flux:button>
                                <flux:button variant="subtle" size="xs" href="{{ route('purchase-orders.show', $po) }}" wire:navigate>
                                    Detail
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-zinc-500">
                                Belum ada Purchase Order (PO) aktif yang diterbitkan untuk akun Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>

    {{-- Feeds 2 Kolom: DO Terbaru & Invoice Terbaru --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Riwayat Pengiriman Terbaru --}}
        <flux:card class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="base" class="font-bold">Pengiriman Terbaru (Surat Jalan)</flux:heading>
                <flux:button variant="subtle" size="xs" href="{{ route('deliveries.index') }}" wire:navigate>
                    Semua DO
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
                                PO: {{ $deliv->purchaseOrder?->po_number ?? '-' }} • Tgl Kirim: {{ $deliv->delivery_date?->format('d/m/Y') }}
                            </div>
                        </div>
                        <flux:button variant="subtle" size="xs" href="{{ route('deliveries.show', $deliv) }}" wire:navigate>
                            Detail
                        </flux:button>
                    </div>
                @empty
                    <div class="py-4 text-center text-xs text-zinc-500">
                        Belum ada riwayat pengiriman.
                    </div>
                @endforelse
            </div>
        </flux:card>

        {{-- Riwayat Tagihan Terbaru --}}
        <flux:card class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="base" class="font-bold">Tagihan Terbaru (Invoice)</flux:heading>
                <flux:button variant="subtle" size="xs" href="{{ route('invoices.index') }}" wire:navigate>
                    Semua Invoice
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
                                Rp {{ number_format($inv->total_amount, 0, ',', '.') }} • {{ $inv->invoice_date?->format('d/m/Y') }}
                            </div>
                        </div>
                        <flux:button variant="subtle" size="xs" href="{{ route('invoices.show', $inv) }}" wire:navigate>
                            Detail
                        </flux:button>
                    </div>
                @empty
                    <div class="py-4 text-center text-xs text-zinc-500">
                        Belum ada riwayat tagihan.
                    </div>
                @endforelse
            </div>
        </flux:card>
    </div>
</div>
