<div class="p-6 max-w-6xl mx-auto space-y-6">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('deliveries.index') }}" wire:navigate class="p-2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                &larr;
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                        {{ $delivery->code }}
                    </h1>
                    @if ($delivery->isConfirmed())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                            ✓ Terkonfirmasi SPV
                        </span>
                    @elseif ($delivery->isRejected())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                            ✕ Ditolak
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            Menunggu Verifikasi SPV
                        </span>
                    @endif

                    @if ($delivery->hasDiscrepancy())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                            Ada Selisih Qty
                        </span>
                    @endif
                </div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    PO Acuan: @if($delivery->purchaseOrder) <a href="{{ route('purchase-orders.show', $delivery->purchaseOrder) }}" wire:navigate class="font-semibold text-blue-600 hover:underline">{{ $delivery->purchaseOrder->po_number }}</a> @else <span class="font-semibold text-zinc-600">-</span> @endif &bull;
                    Vendor: <strong class="text-zinc-700 dark:text-zinc-200">{{ $delivery->vendor->name }}</strong> &bull;
                    Tanggal Kirim: {{ $delivery->delivery_date->format('d F Y') }}
                </p>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm dark:bg-green-900/30 dark:border-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if ($delivery->isRejected() && $delivery->reject_reason)
        <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm dark:bg-red-900/30 dark:border-red-800 dark:text-red-300">
            <strong>Alasan Penolakan:</strong> {{ $delivery->reject_reason }}
        </div>
    @endif

    <!-- SPV Action Panel: Konfirmasi Penerimaan Barang -->
    @if ($canConfirm)
        <div class="bg-amber-50/60 dark:bg-amber-950/20 p-6 rounded-xl border border-amber-200 dark:border-amber-800 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-amber-200 dark:border-amber-800 pb-3">
                <div>
                    <h2 class="text-base font-bold text-amber-900 dark:text-amber-200">Verifikasi & Konfirmasi Penerimaan Fisik (SPV)</h2>
                    <p class="text-xs text-amber-700 dark:text-amber-300">
                        Periksa kesesuaian fisik makanan. Default kuantitas diterima sama dengan yang dikirim vendor. Sesuaikan jika terdapat barang kurang atau rusak.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="$set('showRejectModal', true)" class="px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-100 hover:bg-red-200 rounded-lg transition">
                        Tolak Pengiriman
                    </button>
                    <button type="button" wire:click="confirmDelivery" class="px-4 py-2 text-xs font-bold text-white bg-green-600 hover:bg-green-700 rounded-lg shadow-sm transition">
                        ✓ Konfirmasi Diterima
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Informasi Detail Pengiriman -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
            <!-- Table Rincian Item -->
            <div class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 font-semibold text-sm text-zinc-900 dark:text-zinc-100 flex items-center justify-between">
                    <span>Rincian Item yang Dikirim</span>
                    <span class="text-xs text-zinc-400 font-normal">Harga dihitung flat sesuai kontrak PO</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-zinc-700 dark:text-zinc-300 divide-y divide-zinc-200 dark:divide-zinc-800">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60 font-semibold text-zinc-600 dark:text-zinc-300 text-xs">
                            <tr>
                                <th class="px-4 py-3">Item Makanan / Jasa</th>
                                <th class="px-3 py-3 text-center">Satuan</th>
                                <th class="px-3 py-3 text-center">Qty Kirim</th>
                                <th class="px-3 py-3 text-center">Qty Diterima</th>
                                <th class="px-3 py-3 text-center">Selisih</th>
                                <th class="px-4 py-3">Nama Training / Varian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($delivery->deliveryItems as $item)
                                @php
                                    $receivedVal = $canConfirm
                                        ? ($receivedQuantities[$item->id] ?? $item->qty_sent)
                                        : ($item->qty_received !== null ? $item->qty_received : $item->qty_sent);
                                    $diff = is_numeric($receivedVal) ? ((float) $receivedVal - (float) $item->qty_sent) : 0.0;
                                @endphp
                                <tr class="align-top hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40">
                                    <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ $item->poItem->item_name }}
                                    </td>
                                    <td class="px-3 py-3 text-center font-mono text-xs">{{ $item->poItem->unit }}</td>
                                    <td class="px-3 py-3 text-center font-semibold">{{ number_format($item->qty_sent, 2, ',', '.') }}</td>
                                    <td class="px-3 py-3 text-center">
                                        @if ($canConfirm)
                                            <input type="number" step="0.01" min="0" wire:model.live="receivedQuantities.{{ $item->id }}" class="w-20 px-2 py-1 text-center text-xs font-bold border border-zinc-300 rounded dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        @else
                                            <span class="font-bold {{ $item->hasDifference() ? 'text-rose-600' : 'text-green-600' }}">
                                                {{ $item->qty_received !== null ? number_format($item->qty_received, 2, ',', '.') : '-' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-center font-mono text-xs">
                                        @if ($diff == 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                0,00 (Sesuai)
                                            </span>
                                        @elseif ($diff < 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                Kurang {{ number_format(abs($diff), 2, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                +{{ number_format($diff, 2, ',', '.') }} (Lebih)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs space-y-1">
                                        @if ($item->training_name)
                                            <div class="font-medium text-zinc-800 dark:text-zinc-200">
                                                🎯 {{ $item->training_name }}
                                            </div>
                                        @endif
                                        @if ($item->needed_date)
                                            <div class="text-[11px] text-zinc-400">
                                                Tgl Perlu: {{ $item->needed_date->format('d M Y') }}
                                            </div>
                                        @endif
                                        @if ($item->variant_note)
                                            <div class="text-[11px] text-zinc-600 dark:text-zinc-400 italic">
                                                "{{ $item->variant_note }}"
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-zinc-50 dark:bg-zinc-800/60 font-semibold text-xs">
                            @php
                                $totalSent = (float) $delivery->total_qty_sent;
                                $totalReceivedCalc = 0;
                                foreach ($delivery->deliveryItems as $i) {
                                    $val = $canConfirm
                                        ? ($receivedQuantities[$i->id] ?? $i->qty_sent)
                                        : ($i->qty_received !== null ? $i->qty_received : $i->qty_sent);
                                    $totalReceivedCalc += is_numeric($val) ? (float) $val : 0.0;
                                }
                                $totalDiff = $totalReceivedCalc - $totalSent;
                            @endphp
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-right">TOTAL:</td>
                                <td class="px-3 py-3 text-center font-bold">{{ number_format($totalSent, 2, ',', '.') }}</td>
                                <td class="px-3 py-3 text-center font-bold text-green-600">
                                    {{ number_format($totalReceivedCalc, 2, ',', '.') }}
                                </td>
                                <td class="px-3 py-3 text-center font-mono">
                                    @if ($totalDiff == 0)
                                        <span class="text-emerald-600 font-bold">0,00</span>
                                    @elseif ($totalDiff < 0)
                                        <span class="text-rose-600 font-bold">-{{ number_format(abs($totalDiff), 2, ',', '.') }}</span>
                                    @else
                                        <span class="text-blue-600 font-bold">+{{ number_format($totalDiff, 2, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Kolom Kanan: Meta & Dokumen -->
        <div class="space-y-6">
            <!-- Info Pengiriman -->
            <div class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 space-y-3">
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 dark:border-zinc-800 pb-2">
                    Metadata Pengiriman
                </h3>

                <div class="text-xs space-y-2">
                    <div class="flex justify-between">
                        <span class="text-zinc-400">Kode Pengiriman:</span>
                        <span class="font-mono font-semibold">{{ $delivery->code }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-400">No. DO Vendor:</span>
                        <span class="font-medium">{{ $delivery->vendor_do_number ?: '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-400">Diajukan Oleh:</span>
                        <span class="font-medium">{{ $delivery->submitter->name ?? '-' }}</span>
                    </div>
                    @if ($delivery->isConfirmed())
                        <div class="flex justify-between">
                            <span class="text-zinc-400">Dikonfirmasi Oleh:</span>
                            <span class="font-medium text-green-600">{{ $delivery->confirmer->name ?? 'SPV' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-400">Waktu Konfirmasi:</span>
                            <span class="font-medium">{{ $delivery->confirmed_at?->format('d M Y H:i') }}</span>
                        </div>
                    @endif
                    @if ($delivery->notes)
                        <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-400 block mb-1">Catatan:</span>
                            <p class="text-zinc-600 dark:text-zinc-300 italic">{{ $delivery->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Dokumen Lampiran DO -->
            <div class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 space-y-3">
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 dark:border-zinc-800 pb-2">
                    Surat Jalan Fisik (Scan DO)
                </h3>

                @forelse ($delivery->documents as $doc)
                    <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-800 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase p-1 bg-zinc-200 dark:bg-zinc-700 rounded text-zinc-700 dark:text-zinc-300">
                                {{ pathinfo($doc->original_name, PATHINFO_EXTENSION) }}
                            </span>
                            <span class="text-xs font-medium text-zinc-800 dark:text-zinc-200 truncate flex-1">
                                {{ $doc->original_name }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <a href="{{ route('documents.show', $doc) }}" target="_blank" class="px-2.5 py-1 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded dark:bg-blue-900/30 dark:text-blue-300 transition">
                                Buka
                            </a>
                            <a href="{{ route('documents.show', ['document' => $doc, 'download' => 1]) }}" class="px-2.5 py-1 text-xs font-medium text-zinc-700 bg-zinc-100 hover:bg-zinc-200 rounded dark:bg-zinc-800 dark:text-zinc-300 transition">
                                Unduh
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-zinc-400 text-center py-4">
                        Tidak ada berkas DO fisik dilampirkan.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-800 w-full max-w-md p-6 space-y-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                    Tolak Pengiriman {{ $delivery->code }}
                </h3>
                <p class="text-xs text-zinc-500">
                    Kuantitas sisa PO tidak akan berkurang. Tuliskan alasan penolakan secara jelas agar vendor dapat melakukan penyesuaian.
                </p>

                <form wire:submit="rejectDelivery" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Alasan Penolakan *</label>
                        <textarea wire:model="reject_reason" rows="3" placeholder="contoh: Makanan basi saat sampai, jumlah fisik tidak sesuai..." class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-red-500 focus:outline-none"></textarea>
                        @error('reject_reason') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showRejectModal', false)" class="px-4 py-2 text-sm font-medium text-zinc-600 bg-zinc-100 rounded-lg hover:bg-zinc-200 transition dark:bg-zinc-800 dark:text-zinc-300">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                            Tolak Pengiriman
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
