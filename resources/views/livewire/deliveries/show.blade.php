<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('deliveries.index') }}" wire:navigate class="flex size-10 items-center justify-center text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-100 bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 shadow-2xs hover:shadow-xs transition">
                <flux:icon icon="arrow-left" class="size-4" />
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                        {{ $delivery->code }}
                    </h1>
                    @if ($delivery->isConfirmed())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                            Terkonfirmasi SPV
                        </span>
                    @elseif ($delivery->isRejected())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/60">
                            <span class="size-1.5 rounded-full bg-rose-500"></span>
                            Ditolak
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/60">
                            <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Menunggu Verifikasi SPV
                        </span>
                    @endif

                    @if ($delivery->hasDiscrepancy())
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/60">
                            Ada Selisih Qty
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1 flex flex-wrap items-center gap-2">
                    <span>PO Acuan: 
                        @if($delivery->purchaseOrder) 
                            <a href="{{ route('purchase-orders.show', $delivery->purchaseOrder) }}" wire:navigate class="font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 hover:underline">{{ $delivery->purchaseOrder->po_number }}</a> 
                        @else 
                            <span class="font-semibold text-slate-600">-</span> 
                        @endif
                    </span>
                    <span class="text-slate-300 dark:text-zinc-700">&bull;</span>
                    <span>Vendor: <strong class="text-slate-800 dark:text-zinc-200">{{ $delivery->vendor->name }}</strong></span>
                    <span class="text-slate-300 dark:text-zinc-700">&bull;</span>
                    <span>Tgl Kirim: <strong class="text-slate-800 dark:text-zinc-200">{{ $delivery->delivery_date->translatedFormat('d F Y') }}</strong></span>
                </p>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm dark:bg-emerald-950/40 dark:border-emerald-800/60 dark:text-emerald-300 shadow-2xs">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600" />
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if ($delivery->isRejected() && $delivery->reject_reason)
        <div class="flex items-start gap-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm dark:bg-rose-950/40 dark:border-rose-800/60 dark:text-rose-300 shadow-2xs">
            <flux:icon icon="exclamation-triangle" class="size-5 shrink-0 text-rose-600" />
            <div>
                <strong class="font-bold block">Alasan Penolakan:</strong>
                <p class="text-xs mt-0.5">{{ $delivery->reject_reason }}</p>
            </div>
        </div>
    @endif

    <!-- SPV Action Panel: Konfirmasi Penerimaan Barang -->
    @if ($canConfirm)
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500/10 via-amber-50 to-orange-50/50 dark:from-amber-950/30 dark:via-zinc-900 dark:to-zinc-900 p-5 sm:p-6 border border-amber-300/80 dark:border-amber-800/60 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="flex size-7 items-center justify-center rounded-lg bg-amber-500 text-white font-bold text-xs">
                            SPV
                        </div>
                        <h2 class="text-base font-bold text-amber-950 dark:text-amber-200">Verifikasi & Konfirmasi Penerimaan Fisik (SPV)</h2>
                    </div>
                    <p class="text-xs text-amber-800/90 dark:text-amber-300/90 max-w-2xl leading-relaxed">
                        Periksa kesesuaian fisik makanan dan jumlah porsi aktual saat tiba di Training Center. Default kuantitas otomatis disamakan dengan DO vendor. Ubah angka pada kolom "Qty Diterima" jika ada kekurangan atau kerusakan fisik.
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full sm:w-auto shrink-0">
                    <button type="button" wire:click="$set('showRejectModal', true)" class="w-full sm:w-auto justify-center px-4 py-2.5 text-xs font-bold text-rose-700 bg-white hover:bg-rose-50 rounded-xl border border-rose-200 dark:bg-zinc-900 dark:text-rose-400 dark:border-rose-900/60 dark:hover:bg-rose-950/40 shadow-2xs transition">
                        Tolak Pengiriman
                    </button>
                    <button type="button" wire:click="confirmDelivery" class="w-full sm:w-auto justify-center inline-flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-md shadow-emerald-600/25 transition transform active:scale-95">
                        <flux:icon icon="check" class="size-4" />
                        <span>Konfirmasi Diterima</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Informasi Detail Pengiriman (Grid 2 Kolom) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Table Rincian Item -->
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between bg-slate-50/50 dark:bg-zinc-900/50">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">Rincian Item yang Dikirim</h3>
                        <span class="text-xs text-slate-400">Harga dan plafon dihitung flat sesuai kontrak PO acuan</span>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-300">
                        {{ $delivery->deliveryItems->count() }} Macam Item
                    </span>
                </div>

                <div class="overflow-x-auto -mx-3 sm:mx-0">
                    <table class="min-w-[640px] w-full text-left text-sm text-slate-700 dark:text-zinc-300 divide-y divide-slate-100 dark:divide-zinc-800 whitespace-nowrap">
                        <thead class="bg-slate-50/80 dark:bg-zinc-800/60 font-bold text-slate-500 dark:text-zinc-400 text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5">Item Makanan / Jasa</th>
                                <th class="px-3 py-3.5 text-center">Satuan</th>
                                <th class="px-3 py-3.5 text-center">Qty Kirim</th>
                                <th class="px-3 py-3.5 text-center">Qty Diterima</th>
                                <th class="px-3 py-3.5 text-center">Selisih</th>
                                <th class="px-5 py-3.5">Nama Training / Varian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            @foreach ($delivery->deliveryItems as $item)
                                @php
                                    $receivedVal = $canConfirm
                                        ? ($receivedQuantities[$item->id] ?? $item->qty_sent)
                                        : ($item->qty_received !== null ? $item->qty_received : $item->qty_sent);
                                    $diff = is_numeric($receivedVal) ? ((float) $receivedVal - (float) $item->qty_sent) : 0.0;
                                @endphp
                                <tr class="align-top hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                                    <td class="px-5 py-4 font-bold text-slate-900 dark:text-white">
                                        {{ $item->poItem->item_name }}
                                    </td>
                                    <td class="px-3 py-4 text-center font-mono text-xs text-slate-500">
                                        {{ $item->poItem->unit }}
                                    </td>
                                    <td class="px-3 py-4 text-center font-semibold text-slate-800 dark:text-zinc-200">
                                        {{ number_format($item->qty_sent, 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        @if ($canConfirm)
                                            <input type="number" step="0.01" min="0" wire:model.live="receivedQuantities.{{ $item->id }}" class="w-20 px-2.5 py-1.5 text-center text-xs font-bold border border-slate-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 shadow-2xs">
                                        @else
                                            <span class="font-bold text-sm {{ $item->hasDifference() ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                                {{ $item->qty_received !== null ? number_format($item->qty_received, 2, ',', '.') : '-' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-center font-mono text-xs">
                                        @if ($diff == 0)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold text-emerald-700 bg-emerald-50 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                                                0,00 (Sesuai)
                                            </span>
                                        @elseif ($diff < 0)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/60">
                                                Kurang {{ number_format(abs($diff), 2, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200/60">
                                                +{{ number_format($diff, 2, ',', '.') }} (Lebih)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-xs space-y-1">
                                        @if ($item->training_name)
                                            <div class="font-semibold text-slate-800 dark:text-zinc-200 flex items-center gap-1.5">
                                                <span class="size-1.5 rounded-full bg-indigo-500"></span>
                                                {{ $item->training_name }}
                                            </div>
                                        @endif
                                        @if ($item->needed_date)
                                            <div class="text-[11px] text-slate-400">
                                                Tgl Perlu: {{ $item->needed_date->format('d M Y') }}
                                            </div>
                                        @endif
                                        @if ($item->variant_note)
                                            <div class="text-[11px] text-slate-600 dark:text-zinc-400 italic bg-slate-50 dark:bg-zinc-800/80 px-2 py-0.5 rounded border border-slate-100 dark:border-zinc-700/60 inline-block">
                                                "{{ $item->variant_note }}"
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50/80 dark:bg-zinc-800/60 font-bold text-xs">
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
                                <td colspan="2" class="px-5 py-4 text-right uppercase tracking-wider text-slate-500 text-[11px]">TOTAL:</td>
                                <td class="px-3 py-4 text-center font-bold text-slate-900 dark:text-white">{{ number_format($totalSent, 2, ',', '.') }}</td>
                                <td class="px-3 py-4 text-center font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($totalReceivedCalc, 2, ',', '.') }}
                                </td>
                                <td class="px-3 py-4 text-center font-mono">
                                    @if ($totalDiff == 0)
                                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">0,00</span>
                                    @elseif ($totalDiff < 0)
                                        <span class="text-rose-600 dark:text-rose-400 font-bold">-{{ number_format(abs($totalDiff), 2, ',', '.') }}</span>
                                    @else
                                        <span class="text-sky-600 dark:text-sky-400 font-bold">+{{ number_format($totalDiff, 2, ',', '.') }}</span>
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
            <!-- Metadata Pengiriman -->
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-zinc-800 pb-3">
                    <flux:icon icon="information-circle" class="size-4.5 text-indigo-600 dark:text-indigo-400" />
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Metadata Pengiriman
                    </h3>
                </div>

                <div class="text-xs space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Kode Pengiriman:</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-zinc-200">{{ $delivery->code }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">No. DO Vendor:</span>
                        <span class="font-semibold text-slate-800 dark:text-zinc-200">{{ $delivery->vendor_do_number ?: '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Diajukan Oleh:</span>
                        <span class="font-medium text-slate-800 dark:text-zinc-200">{{ $delivery->submitter->name ?? '-' }}</span>
                    </div>
                    @if ($delivery->isConfirmed())
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Dikonfirmasi Oleh:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $delivery->confirmer->name ?? 'SPV' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Waktu Konfirmasi:</span>
                            <span class="font-medium text-slate-800 dark:text-zinc-200">{{ $delivery->confirmed_at?->format('d M Y H:i') }}</span>
                        </div>
                    @endif
                    @if ($delivery->notes)
                        <div class="pt-2 border-t border-slate-100 dark:border-zinc-800">
                            <span class="text-slate-400 block mb-1 font-medium">Catatan:</span>
                            <p class="text-slate-600 dark:text-zinc-300 italic bg-slate-50 dark:bg-zinc-800/60 p-2 rounded-lg border border-slate-100 dark:border-zinc-800">{{ $delivery->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Dokumen Lampiran DO -->
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-zinc-800 pb-3">
                    <flux:icon icon="paper-clip" class="size-4.5 text-indigo-600 dark:text-indigo-400" />
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Surat Jalan Fisik (Scan DO)
                    </h3>
                </div>

                <x-document-preview-card :documents="$delivery->documents" />
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-zinc-800 w-full max-w-md p-6 space-y-4">
                <div class="flex items-center gap-3 text-rose-600">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-rose-50 dark:bg-rose-950/60">
                        <flux:icon icon="exclamation-triangle" class="size-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">
                            Tolak Pengiriman {{ $delivery->code }}
                        </h3>
                        <p class="text-xs text-slate-400">Plafon PO tidak akan terpotong</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 dark:text-zinc-400 leading-relaxed">
                    Tuliskan alasan penolakan secara jelas agar vendor catering dapat memperbaiki atau mengirimkan ulang makanan yang sesuai.
                </p>

                <form wire:submit="rejectDelivery" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Alasan Penolakan *</label>
                        <textarea wire:model="reject_reason" rows="3" placeholder="contoh: Makanan rusak saat tiba di lokasi, jumlah porsi fisik kurang..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-hidden"></textarea>
                        @error('reject_reason') <span class="text-xs text-rose-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showRejectModal', false)" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition dark:bg-zinc-800 dark:text-zinc-300">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 rounded-xl shadow-xs transition">
                            Tolak Pengiriman
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
