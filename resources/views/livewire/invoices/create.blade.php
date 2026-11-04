<div class="p-6 max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Buat Tagihan / Invoice Baru</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Pilih PO dan gabungkan surat jalan (DO) yang telah terkonfirmasi SPV untuk diterbitkan invoicenya.</p>
        </div>
        <a href="{{ route('invoices.index') }}" wire:navigate class="px-4 py-2 text-sm font-medium text-zinc-600 bg-zinc-100 rounded-lg hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 transition">
            &larr; Kembali ke Daftar
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <!-- Informasi Dokumen Invoice -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 dark:border-zinc-800 pb-2">
                1. Identitas Tagihan / Faktur
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Pilih Purchase Order (PO) *</label>
                    <select wire:model.live="purchase_order_id" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Pilih Purchase Order dengan Pengiriman Selesai --</option>
                        @foreach ($availablePos as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->po_number }} &bull; {{ $p->vendor->name }} (PO Tgl: {{ $p->po_date->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                    @error('purchase_order_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nomor Invoice / Faktur *</label>
                    <input type="text" wire:model="invoice_number" placeholder="contoh: INV-2026/XI/001" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @error('invoice_number') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tanggal Invoice *</label>
                    <input type="date" wire:model="invoice_date" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @error('invoice_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Unggah Berkas Invoice / Kwitansi (PDF/Foto)</label>
                    <input type="file" wire:model="document_file" class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-zinc-800 dark:file:text-zinc-300">
                    <p class="text-[11px] text-zinc-400 mt-1">Format PDF, JPG, PNG. Maksimal 5 MB.</p>
                    @error('document_file') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Pemilihan Pengiriman Terkonfirmasi yang Belum Ditagih -->
        @if ($purchase_order_id && $eligibleDeliveryItems->isNotEmpty())
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-2">
                    <div>
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">2. Pilih Surat Jalan (DO) yang Ditagihkan</h2>
                        <p class="text-xs text-zinc-400">Centang baris pengiriman yang akan digabungkan ke dalam invoice ini. Harga otomatis flat sesuai PO.</p>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-zinc-400 uppercase font-semibold">Total Tagihan Invoice</div>
                        <div class="text-xl font-bold text-blue-600 dark:text-blue-400">
                            Rp {{ number_format($this->total_amount, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                @error('selected_item_ids') <div class="text-xs text-red-600">{{ $message }}</div> @enderror

                <div class="space-y-4">
                    @foreach ($eligibleDeliveryItems as $deliveryId => $groupItems)
                        @php
                            $deliv = $groupItems->first()->delivery;
                        @endphp
                        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden bg-zinc-50/40 dark:bg-zinc-800/20">
                            <div class="px-4 py-2.5 bg-zinc-100/80 dark:bg-zinc-800/60 flex items-center justify-between border-b border-zinc-200/60 dark:border-zinc-800">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-blue-700 dark:text-blue-400 font-mono">{{ $deliv->code }}</span>
                                    @if ($deliv->vendor_do_number)
                                        <span class="text-xs text-zinc-500">(DO Vendor: {{ $deliv->vendor_do_number }})</span>
                                    @endif
                                    <span class="text-xs text-zinc-400">&bull; {{ $deliv->delivery_date->format('d M Y') }}</span>
                                </div>
                                <span class="text-xs font-semibold text-green-700 bg-green-100 px-2 py-0.5 rounded-full">
                                    ✓ Terkonfirmasi SPV
                                </span>
                            </div>

                            <div class="divide-y divide-zinc-200/60 dark:divide-zinc-800">
                                @foreach ($groupItems as $item)
                                    @php
                                        $qty = $item->qty_received ?? $item->qty_sent;
                                        $lineAmount = (int) round($qty * $item->poItem->unit_price);
                                    @endphp
                                    <label class="px-4 py-3 flex items-center justify-between hover:bg-white dark:hover:bg-zinc-800/40 cursor-pointer transition">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" value="{{ $item->id }}" wire:model.live="selected_item_ids" class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                                            <div>
                                                <div class="font-semibold text-sm text-zinc-900 dark:text-zinc-100">
                                                    {{ $item->poItem->item_name }}
                                                </div>
                                                <div class="text-xs text-zinc-400 flex items-center gap-2">
                                                    @if ($item->training_name)
                                                        <span>🎯 {{ $item->training_name }}</span>
                                                    @endif
                                                    @if ($item->variant_note)
                                                        <span class="italic">"{{ $item->variant_note }}"</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                                                {{ number_format($qty, 0, ',', '.') }} {{ $item->poItem->unit }} &times; Rp {{ number_format($item->poItem->unit_price, 0, ',', '.') }}
                                            </div>
                                            <div class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                                Rp {{ number_format($lineAmount, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif ($purchase_order_id)
            <div class="p-6 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 text-center text-zinc-400 text-sm">
                Tidak ada pengiriman terkonfirmasi yang belum ditagihkan untuk PO ini.
            </div>
        @endif

        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('invoices.index') }}" wire:navigate class="px-5 py-2.5 text-sm font-medium text-zinc-600 bg-zinc-100 rounded-lg hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition">
                Terbitkan & Ajukan Invoice
            </button>
        </div>
    </form>
</div>

