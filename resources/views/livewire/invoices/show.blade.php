<div class="p-6 max-w-6xl mx-auto space-y-6">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" wire:navigate class="p-2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                &larr;
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                        {{ $invoice->invoice_number }}
                    </h1>
                    @if ($invoice->isApproved())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                            ✓ Disetujui SPV
                        </span>
                    @elseif ($invoice->isPaid())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                            ★ Lunas
                        </span>
                    @elseif ($invoice->isInPayment())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                            Dalam Proses Pembayaran
                        </span>
                    @elseif ($invoice->isRejected())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                            ✕ Ditolak
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            Menunggu Verifikasi SPV
                        </span>
                    @endif
                </div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    PO Acuan: <a href="{{ route('purchase-orders.show', $invoice->purchaseOrder) }}" wire:navigate class="font-semibold text-blue-600 hover:underline">{{ $invoice->purchaseOrder->po_number }}</a> &bull;
                    Vendor: <strong class="text-zinc-700 dark:text-zinc-200">{{ $invoice->vendor->name }}</strong> &bull;
                    Tanggal Invoice: {{ $invoice->invoice_date->format('d F Y') }}
                </p>
            </div>
        </div>

        <div class="text-right">
            <div class="text-xs text-zinc-400 uppercase font-semibold">Total Tagihan</div>
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm dark:bg-green-900/30 dark:border-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if ($invoice->isRejected() && $invoice->reject_reason)
        <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm dark:bg-red-900/30 dark:border-red-800 dark:text-red-300">
            <strong>Alasan Penolakan Tagihan:</strong> {{ $invoice->reject_reason }}
        </div>
    @endif

    <!-- Panel Verifikasi 3-Way Match (Section 6) -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-zinc-100 dark:border-zinc-800 pb-3">
            <div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    Hasil Verifikasi 3-Way Match (PO &bull; DO &bull; Invoice)
                </h2>
                <p class="text-xs text-zinc-500">Pemeriksaan otomatis kesesuaian harga PO, kuantitas fisik yang diterima di DO, dan keunikan nomor faktur.</p>
            </div>
            <div>
                @if ($invoice->isThreeWayMatched())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                        ✓ 3-Way Match Sempurna (Valid)
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                        ⚠ Terdapat Anomali Ketidaksesuaian
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Cek 1: Nomor Invoice Unik -->
            <div class="p-4 rounded-lg border {{ $invoice->hasDuplicateNumber() ? 'border-red-200 bg-red-50/50 dark:bg-red-950/20' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">1. Keunikan Nomor Invoice</span>
                    @if ($invoice->hasDuplicateNumber())
                        <span class="text-xs font-bold text-red-600">✕ Ganda / Pernah Dipakai</span>
                    @else
                        <span class="text-xs font-bold text-green-600">✓ Unik</span>
                    @endif
                </div>
                <p class="text-[11px] text-zinc-400 mt-1">Mencegah duplikasi penagihan atas nomor faktur yang sama dari vendor.</p>
            </div>

            <!-- Cek 2: Harga PO -->
            <div class="p-4 rounded-lg border {{ ! $invoice->checkPriceMatch() ? 'border-red-200 bg-red-50/50 dark:bg-red-950/20' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">2. Harga Terkunci PO</span>
                    @if ($invoice->checkPriceMatch())
                        <span class="text-xs font-bold text-green-600">✓ Sesuai PO</span>
                    @else
                        <span class="text-xs font-bold text-red-600">✕ Beda Harga</span>
                    @endif
                </div>
                <p class="text-[11px] text-zinc-400 mt-1">Harga satuan pada invoice terkunci flat mengikuti kontrak PO resmi.</p>
            </div>

            <!-- Cek 3: Kuantitas Fisik DO -->
            <div class="p-4 rounded-lg border {{ ! $invoice->checkQuantityMatch() ? 'border-red-200 bg-red-50/50 dark:bg-red-950/20' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">3. Kuantitas Fisik DO</span>
                    @if ($invoice->checkQuantityMatch())
                        <span class="text-xs font-bold text-green-600">✓ Sesuai Diterima</span>
                    @else
                        <span class="text-xs font-bold text-red-600">✕ Selisih Kuantitas</span>
                    @endif
                </div>
                <p class="text-[11px] text-zinc-400 mt-1">Hanya barang yang telah dikonfirmasi diterima secara fisik yang ditagihkan.</p>
            </div>
        </div>

        <!-- Tombol Aksi Persetujuan / Penolakan SPV -->
        @if ($canVerify)
            <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-end gap-3">
                <button type="button" wire:click="$set('showRejectModal', true)" class="px-4 py-2 text-xs font-bold text-red-700 bg-red-100 hover:bg-red-200 rounded-lg transition">
                    Tolak Tagihan (Kembalikan ke Vendor)
                </button>
                <button type="button" wire:click="approveInvoice" class="px-5 py-2 text-xs font-bold text-white bg-green-600 hover:bg-green-700 rounded-lg shadow-sm transition">
                    ✓ Setujui Tagihan (Approve)
                </button>
            </div>
        @endif
    </div>

    <!-- Rincian Komparasi Baris Tagihan (PO vs DO vs Invoice) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 font-semibold text-sm text-zinc-900 dark:text-zinc-100">
                    Baris Pengiriman yang Ditagihkan
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-zinc-700 dark:text-zinc-300 divide-y divide-zinc-200 dark:divide-zinc-800">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60 font-semibold text-zinc-600 dark:text-zinc-300 text-xs">
                            <tr>
                                <th class="px-4 py-3">Surat Jalan (DO)</th>
                                <th class="px-4 py-3">Item Barang / Jasa</th>
                                <th class="px-3 py-3 text-center">Qty Fisik DO</th>
                                <th class="px-3 py-3 text-center">Qty Tagihan</th>
                                <th class="px-3 py-3 text-right">Harga PO</th>
                                <th class="px-4 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($invoice->invoiceLines as $line)
                                @php
                                    $dItem = $line->deliveryItem;
                                    $deliv = $dItem->delivery;
                                @endphp
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('deliveries.show', $deliv) }}" wire:navigate class="font-mono text-xs font-semibold text-blue-600 hover:underline">
                                            {{ $deliv->code }}
                                        </a>
                                        <div class="text-[10px] text-zinc-400">{{ $deliv->delivery_date->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ $dItem->poItem->item_name }}
                                        @if ($dItem->training_name)
                                            <div class="text-[11px] text-zinc-400">🎯 {{ $dItem->training_name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-center font-mono text-xs text-green-600 font-bold">
                                        {{ number_format($dItem->qty_received, 0, ',', '.') }} {{ $dItem->poItem->unit }}
                                    </td>
                                    <td class="px-3 py-3 text-center font-mono text-xs font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ number_format($line->qty, 0, ',', '.') }} {{ $dItem->poItem->unit }}
                                    </td>
                                    <td class="px-3 py-3 text-right text-xs">
                                        Rp {{ number_format($line->unit_price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-sm">
                                        Rp {{ number_format($line->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-zinc-50 dark:bg-zinc-800/60 font-bold text-xs">
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-right">TOTAL TAGIHAN:</td>
                                <td class="px-4 py-3 text-right text-base text-blue-600 dark:text-blue-400">
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
            <!-- Lampiran Berkas Faktur / Kwitansi Fisik -->
            <div class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 space-y-3">
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 dark:border-zinc-800 pb-2">
                    Berkas Faktur / Invoice Fisik
                </h3>

                @forelse ($invoice->documents as $doc)
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
                                Buka Berkas
                            </a>
                            <a href="{{ route('documents.show', ['document' => $doc, 'download' => 1]) }}" class="px-2.5 py-1 text-xs font-medium text-zinc-700 bg-zinc-100 hover:bg-zinc-200 rounded dark:bg-zinc-800 dark:text-zinc-300 transition">
                                Unduh
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-zinc-400 text-center py-4">
                        Tidak ada berkas fisik dilampirkan.
                    </div>
                @endforelse
            </div>

            <!-- Status Pembayaran / Payment Request -->
            <div class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 space-y-3">
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 dark:border-zinc-800 pb-2">
                    Payment Request Terkait
                </h3>

                @forelse ($invoice->paymentRequests as $pr)
                    <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-800 space-y-1">
                        <div class="font-bold text-xs text-blue-600">{{ $pr->number }}</div>
                        <div class="text-[11px] text-zinc-500">Tahap: <span class="font-medium text-purple-600">{{ $pr->currentStage->name }}</span></div>
                    </div>
                @empty
                    <div class="text-xs text-zinc-400 text-center py-4">
                        Belum dimasukkan ke dalam Payment Request.
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
                    Tolak Tagihan {{ $invoice->invoice_number }}
                </h3>
                <p class="text-xs text-zinc-500">
                    Pengiriman yang terhubung akan dilepaskan status penagihannya agar vendor dapat melakukan revisi invoice. Alasan penolakan wajib dicatat.
                </p>

                <form wire:submit="rejectInvoice" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Alasan Penolakan Tagihan *</label>
                        <textarea wire:model="reject_reason" rows="3" placeholder="contoh: Salah nominal perhitungan, berkas kwitansi tidak terbaca..." class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-red-500 focus:outline-none"></textarea>
                        @error('reject_reason') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showRejectModal', false)" class="px-4 py-2 text-sm font-medium text-zinc-600 bg-zinc-100 rounded-lg hover:bg-zinc-200 transition dark:bg-zinc-800 dark:text-zinc-300">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                            Tolak Tagihan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

