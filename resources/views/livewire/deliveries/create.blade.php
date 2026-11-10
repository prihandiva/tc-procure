<div class="space-y-6 max-w-5xl mx-auto w-full min-w-0">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Input Pengiriman Makanan / DO</h1>
            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Pilih PO aktif, isi rincian kuantitas pengiriman, nama training, varian makanan, dan lampirkan scan surat jalan.</p>
        </div>
        <a href="{{ route('deliveries.index') }}" wire:navigate class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-bold text-slate-600 bg-white hover:bg-slate-100 rounded-xl border border-slate-200/80 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700 transition shadow-2xs w-full sm:w-auto">
            <flux:icon icon="arrow-left" class="size-3.5" />
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <!-- Section 1: PO & Header Pengiriman -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 dark:border-zinc-800 pb-2">
                1. Dokumen Acuan & Surat Jalan
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Pilih Purchase Order (PO) *</label>
                    <select wire:model.live="purchase_order_id" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Pilih Purchase Order Aktif --</option>
                        @foreach ($availablePos as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->po_number }} &bull; {{ $p->vendor->name }} &bull; Sisa Kuota: {{ number_format($p->total_qty_remaining, 0, ',', '.') }} {{ $p->type->value === 'non_po' ? '(Non-PO)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('purchase_order_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tanggal Pengiriman *</label>
                    <input type="date" wire:model="delivery_date" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @error('delivery_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nomor Surat Jalan Fisik Vendor (Opsional)</label>
                    <input type="text" wire:model="vendor_do_number" placeholder="contoh: DO-10294 atau nomor kwitansi vendor" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[11px] text-zinc-400 mt-1">Sistem akan membuat kode pengiriman unik otomatis (format DLV-2026-xxxxx).</p>
                    @error('vendor_do_number') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-2">
                    <x-file-upload-camera
                        model="document_file"
                        :file="$document_file"
                        label="Unggah Foto / Scan Surat Jalan (DO Fisik)"
                        help="Ambil foto langsung kertas Surat Jalan fisik saat serah terima barang atau pilih file PDF/JPG dari galeri."
                    />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Catatan Tambahan (Opsional)</label>
                <textarea wire:model="notes" rows="2" placeholder="Catatan jam pengantaran, nama driver, dll..." class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                @error('notes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Section 2: Rincian Item yang Dikirim -->
        @if ($purchase_order_id && count($items) > 0)
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-2">
                    <div>
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">2. Rincian Item yang Dikirim</h2>
                        <p class="text-xs text-zinc-400">Isi kuantitas kirim (tidak boleh melebihi sisa PO), nama training, dan varian makanan jika ada.</p>
                    </div>
                    <div class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                        Total Kirim: <span class="text-blue-600">{{ number_format($this->total_qty_sent, 0, ',', '.') }}</span>
                    </div>
                </div>

                @error('items') <div class="text-xs text-red-600">{{ $message }}</div> @enderror

                <div class="space-y-4">
                    @foreach ($items as $index => $item)
                        <div class="p-4 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-zinc-200/60 dark:border-zinc-800 pb-2">
                                <div>
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $item['item_name'] }}</span>
                                    <span class="text-xs text-zinc-500 font-mono ml-1">({{ $item['unit'] }})</span>
                                </div>
                                <div class="text-xs">
                                    Sisa Kuota PO: <strong class="text-amber-600">{{ number_format($item['qty_remaining'], 2, ',', '.') }} {{ $item['unit'] }}</strong>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">
                                        Qty Dikirim (Maks: {{ $item['qty_remaining'] }}) *
                                    </label>
                                    <input type="number" step="0.01" min="0" max="{{ $item['qty_remaining'] }}" wire:model.live="items.{{ $index }}.qty_sent" class="w-full px-3 py-1.5 text-sm border border-zinc-300 rounded dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    @error("items.{$index}.qty_sent") <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">
                                        Nama Training (Opsional)
                                    </label>
                                    <input type="text" wire:model="items.{{ $index }}.training_name" placeholder="contoh: Safety Induction, Basic Ops..." class="w-full px-3 py-1.5 text-sm border border-zinc-300 rounded dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">
                                        Tanggal Diperlukan (Opsional)
                                    </label>
                                    <input type="date" wire:model="items.{{ $index }}.needed_date" class="w-full px-3 py-1.5 text-sm border border-zinc-300 rounded dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">
                                        Rincian Varian / Rasa (Opsional)
                                    </label>
                                    <input type="text" wire:model="items.{{ $index }}.variant_note" placeholder="contoh: 12 lontong, 12 pudding, 12 bolen..." class="w-full px-3 py-1.5 text-sm border border-zinc-300 rounded dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif ($purchase_order_id)
            <div class="p-6 bg-amber-50 rounded-xl border border-amber-200 text-amber-800 text-sm">
                Purchase Order ini tidak memiliki item dengan kuota sisa yang dapat dikirim.
            </div>
        @endif

        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4">
            <a href="{{ route('deliveries.index') }}" wire:navigate class="inline-flex items-center justify-center px-5 py-2.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl dark:bg-zinc-800 dark:text-zinc-300 transition w-full sm:w-auto">
                Batal
            </a>
            <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-md shadow-indigo-600/30 transition transform active:scale-95 w-full sm:w-auto">
                Kirimkan Pengajuan Surat Jalan
            </button>
        </div>
    </form>
</div>

