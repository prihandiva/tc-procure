<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" wire:navigate class="flex size-10 items-center justify-center text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-100 bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 shadow-2xs hover:shadow-xs transition">
                <flux:icon icon="arrow-left" class="size-4" />
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                        {{ $invoice->invoice_number }}
                    </h1>
                    @if ($invoice->isApproved())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60">
                            <span class="size-1.5 rounded-full bg-indigo-500"></span>
                            Disetujui SPV
                        </span>
                    @elseif ($invoice->isPaid())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                            Lunas Penuh
                        </span>
                    @elseif ($invoice->isInPayment())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200/60">
                            <span class="size-1.5 rounded-full bg-sky-500"></span>
                            Dalam Proses Pembayaran
                        </span>
                    @elseif ($invoice->isRejected())
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
                </div>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1 flex flex-wrap items-center gap-2">
                    <span>PO Acuan: <a href="{{ route('purchase-orders.show', $invoice->purchaseOrder) }}" wire:navigate class="font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 hover:underline">{{ $invoice->purchaseOrder->po_number }}</a></span>
                    <span class="text-slate-300 dark:text-zinc-700">&bull;</span>
                    <span>Vendor: <strong class="text-slate-800 dark:text-zinc-200">{{ $invoice->vendor->name }}</strong></span>
                    <span class="text-slate-300 dark:text-zinc-700">&bull;</span>
                    <span>Tgl Invoice: <strong class="text-slate-800 dark:text-zinc-200">{{ $invoice->invoice_date->translatedFormat('d F Y') }}</strong></span>
                </p>
            </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 px-5 py-3 shadow-xs text-right sm:text-right">
            <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Total Nilai Tagihan</div>
            <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 tracking-tight">
                Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm dark:bg-emerald-950/40 dark:border-emerald-800/60 dark:text-emerald-300 shadow-2xs">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600" />
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if ($invoice->isRejected() && $invoice->reject_reason)
        <div class="flex items-start gap-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm dark:bg-rose-950/40 dark:border-rose-800/60 dark:text-rose-300 shadow-2xs">
            <flux:icon icon="exclamation-triangle" class="size-5 shrink-0 text-rose-600" />
            <div>
                <strong class="font-bold block">Alasan Penolakan Tagihan:</strong>
                <p class="text-xs mt-0.5">{{ $invoice->reject_reason }}</p>
            </div>
        </div>
    @endif

    <!-- Panel Verifikasi 3-Way Match (Section 6) -->
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 dark:border-zinc-800 pb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <flux:icon icon="shield-check" class="size-5 text-indigo-600 dark:text-indigo-400" />
                    Hasil Verifikasi Otomatis 3-Way Match (PO &bull; DO &bull; Invoice)
                </h2>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">Validasi sistematis atas kesesuaian harga kontrak PO, kuantitas fisik diterima di DO, dan keunikan nomor faktur.</p>
            </div>
            <div>
                @if ($invoice->isThreeWayMatched())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                        <span class="size-2 rounded-full bg-emerald-500"></span>
                        3-Way Match Sempurna (Valid)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/60">
                        <span class="size-2 rounded-full bg-rose-500"></span>
                        Terdapat Anomali Ketidaksesuaian
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Cek 1: Nomor Invoice Unik -->
            <div class="p-4 rounded-xl border {{ $invoice->hasDuplicateNumber() ? 'border-rose-200 bg-rose-50/50 dark:bg-rose-950/20' : 'border-slate-200/80 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-800/40' }} space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 dark:text-zinc-300">1. Keunikan No. Faktur</span>
                    @if ($invoice->hasDuplicateNumber())
                        <span class="text-xs font-bold text-rose-600">✕ Duplikat</span>
                    @else
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">✓ Unik</span>
                    @endif
                </div>
                <p class="text-[11px] text-slate-500 dark:text-zinc-400 leading-relaxed">Mencegah duplikasi tagihan atas nomor invoice yang sama dari rekanan.</p>
            </div>

            <!-- Cek 2: Harga PO -->
            <div class="p-4 rounded-xl border {{ ! $invoice->checkPriceMatch() ? 'border-rose-200 bg-rose-50/50 dark:bg-rose-950/20' : 'border-slate-200/80 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-800/40' }} space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 dark:text-zinc-300">2. Tarif Sesuai PO</span>
                    @if ($invoice->checkPriceMatch())
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">✓ Terkunci</span>
                    @else
                        <span class="text-xs font-bold text-rose-600">✕ Beda Tarif</span>
                    @endif
                </div>
                <p class="text-[11px] text-slate-500 dark:text-zinc-400 leading-relaxed">Tarif satuan pada invoice mengikuti nilai kontrak flat PO yang disepakati.</p>
            </div>

            <!-- Cek 3: Kuantitas Fisik DO -->
            <div class="p-4 rounded-xl border {{ ! $invoice->checkQuantityMatch() ? 'border-rose-200 bg-rose-50/50 dark:bg-rose-950/20' : 'border-slate-200/80 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-800/40' }} space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 dark:text-zinc-300">3. Kuantitas Fisik DO</span>
                    @if ($invoice->checkQuantityMatch())
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">✓ Sesuai Fisik</span>
                    @else
                        <span class="text-xs font-bold text-rose-600">✕ Selisih Fisik</span>
                    @endif
                </div>
                <p class="text-[11px] text-slate-500 dark:text-zinc-400 leading-relaxed">Hanya barang yang telah dikonfirmasi diterima secara fisik yang ditagihkan.</p>
            </div>
        </div>

        <!-- Tombol Aksi Persetujuan / Penolakan SPV -->
        @if ($canVerify)
            <div class="pt-4 border-t border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 w-full">
                <button type="button" wire:click="$set('showRejectModal', true)" class="w-full sm:w-auto justify-center px-4 py-2.5 text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300 dark:hover:bg-rose-950/60 rounded-xl transition">
                    Tolak Tagihan (Kembalikan ke Vendor)
                </button>
                <button type="button" wire:click="approveInvoice" class="w-full sm:w-auto justify-center inline-flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-md shadow-emerald-600/25 transition transform active:scale-95">
                    <flux:icon icon="check" class="size-4" />
                    <span>Setujui Tagihan (Approve)</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Rincian Komparasi Baris Tagihan (PO vs DO vs Invoice) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between bg-slate-50/50 dark:bg-zinc-900/50">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">Rincian Pengiriman yang Ditagihkan</h3>
                        <span class="text-xs text-slate-400">Diverifikasi terhadap Surat Jalan (DO) fisik yang telah terkonfirmasi</span>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-300">
                        {{ $invoice->invoiceLines->count() }} Baris
                    </span>
                </div>

                <div class="overflow-x-auto -mx-3 sm:mx-0">
                    <table class="min-w-[650px] w-full text-left text-sm text-slate-700 dark:text-zinc-300 divide-y divide-slate-100 dark:divide-zinc-800 whitespace-nowrap">
                        <thead class="bg-slate-50/80 dark:bg-zinc-800/60 font-bold text-slate-500 dark:text-zinc-400 text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5">Surat Jalan (DO)</th>
                                <th class="px-5 py-3.5">Item Barang / Jasa</th>
                                <th class="px-3 py-3.5 text-center">Qty Fisik DO</th>
                                <th class="px-3 py-3.5 text-center">Qty Tagihan</th>
                                <th class="px-4 py-3.5 text-right">Harga PO</th>
                                <th class="px-5 py-3.5 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            @foreach ($invoice->invoiceLines as $line)
                                @php
                                    $dItem = $line->deliveryItem;
                                    $deliv = $dItem->delivery;
                                @endphp
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                                    <td class="px-5 py-4">
                                        <a href="{{ route('deliveries.show', $deliv) }}" wire:navigate class="font-mono text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 hover:underline">
                                            {{ $deliv->code }}
                                        </a>
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $deliv->delivery_date->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-5 py-4 font-bold text-slate-900 dark:text-white">
                                        {{ $dItem->poItem->item_name }}
                                        @if ($dItem->training_name)
                                            <div class="text-[11px] text-slate-400 font-normal mt-0.5">🎯 {{ $dItem->training_name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-center font-mono text-xs text-emerald-600 dark:text-emerald-400 font-bold">
                                        {{ number_format($dItem->qty_received, 0, ',', '.') }} {{ $dItem->poItem->unit }}
                                    </td>
                                    <td class="px-3 py-4 text-center font-mono text-xs font-bold text-slate-900 dark:text-white">
                                        {{ number_format($line->qty, 0, ',', '.') }} {{ $dItem->poItem->unit }}
                                    </td>
                                    <td class="px-4 py-4 text-right text-xs text-slate-600 dark:text-zinc-400">
                                        Rp {{ number_format($line->unit_price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-4 text-right font-bold text-sm text-slate-900 dark:text-white">
                                        Rp {{ number_format($line->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50/80 dark:bg-zinc-800/60 font-bold text-xs">
                            <tr>
                                <td colspan="5" class="px-5 py-4 text-right uppercase tracking-wider text-slate-500">TOTAL TAGIHAN:</td>
                                <td class="px-5 py-4 text-right text-base font-black text-indigo-600 dark:text-indigo-400">
                                    Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Kanan: Dokumen Lampiran & Payment Request -->
        <div class="space-y-6">
            <!-- Lampiran Berkas Faktur Fisik -->
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-zinc-800 pb-3">
                    <flux:icon icon="paper-clip" class="size-4.5 text-indigo-600 dark:text-indigo-400" />
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Berkas Faktur / Invoice Fisik
                    </h3>
                </div>

                <x-document-preview-card :documents="$invoice->documents" />
            </div>

            <!-- Status Pembayaran / Payment Request -->
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-5 shadow-xs space-y-3">
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-zinc-800 pb-3">
                    <flux:icon icon="banknotes" class="size-4.5 text-indigo-600 dark:text-indigo-400" />
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Payment Request Terkait
                    </h3>
                </div>

                @forelse ($invoice->paymentRequests as $pr)
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/50 border border-slate-200/70 dark:border-zinc-700/60 space-y-1.5">
                        <a href="{{ route('payment-requests.show', $pr) }}" wire:navigate class="font-bold text-xs text-indigo-600 hover:underline">
                            {{ $pr->number }}
                        </a>
                        <div class="text-[11px] text-slate-500">Tahap: <span class="font-bold text-slate-800 dark:text-zinc-200">{{ $pr->currentStage->name }}</span></div>
                    </div>
                @empty
                    <div class="text-xs text-slate-400 text-center py-6">
                        Belum dimasukkan ke dalam berkas Payment Request.
                    </div>
                @endforelse
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
                            Tolak Tagihan {{ $invoice->invoice_number }}
                        </h3>
                        <p class="text-xs text-slate-400">Pengiriman terkait akan dilepas agar dapat direvisi</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 dark:text-zinc-400 leading-relaxed">
                    Pengiriman DO yang terhubung akan dilepaskan status penagihannya agar vendor dapat merevisi tagihan.
                </p>

                <form wire:submit="rejectInvoice" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Alasan Penolakan Tagihan *</label>
                        <textarea wire:model="reject_reason" rows="3" placeholder="contoh: Salah nominal perhitungan, berkas kwitansi tidak terbaca..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-hidden"></textarea>
                        @error('reject_reason') <span class="text-xs text-rose-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showRejectModal', false)" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition dark:bg-zinc-800 dark:text-zinc-300">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 rounded-xl shadow-xs transition">
                            Tolak Tagihan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
