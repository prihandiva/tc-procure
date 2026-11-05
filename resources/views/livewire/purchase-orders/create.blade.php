<div class="space-y-6 max-w-6xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Terbitkan Purchase Order (PO) Baru</h1>
            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Pencatatan nomor PO resmi/Non-PO, alokasi vendor, kuota snack/catering, dan lampiran dokumen.</p>
        </div>
        <a href="{{ route('purchase-orders.index') }}" wire:navigate class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-bold text-slate-600 bg-white hover:bg-slate-100 rounded-xl border border-slate-200/80 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700 transition shadow-2xs w-full sm:w-auto">
            <flux:icon icon="arrow-left" class="size-3.5" />
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <!-- Informasi Utama PO -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 dark:border-zinc-800 pb-2">
                1. Informasi Dokumen & Vendor
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nomor PO *</label>
                    <input type="text" wire:model="po_number" placeholder="contoh: 47157028" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[11px] text-zinc-400 mt-1">Nomor PO dari sistem perusahaan.</p>
                    @error('po_number') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Jenis Dokumen *</label>
                    <select wire:model="type" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="po">PO (Purchase Order)</option>
                        <option value="non_po">Non-PO (Pembelian Langsung)</option>
                    </select>
                    @error('type') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Vendor Catering *</label>
                    <select wire:model="vendor_id" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Pilih Vendor --</option>
                        @foreach ($vendors as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                    @error('vendor_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tanggal PO *</label>
                    <input type="date" wire:model="po_date" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @error('po_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea wire:model="notes" rows="2" placeholder="Keterangan alokasi, budget training, atau instruksi khusus..." class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                    @error('notes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Unggah Berkas Scan / PDF PO (Opsional)</label>
                    <input type="file" wire:model="document_file" class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-zinc-800 dark:file:text-zinc-300">
                    <p class="text-[11px] text-zinc-400 mt-1">Format PDF, JPG, PNG. Maksimal 5 MB.</p>
                    @error('document_file') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Rincian Item PO -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-2">
                <div>
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">2. Rincian Item Barang & Jasa</h2>
                    <p class="text-xs text-zinc-400">Pilih dari katalog standar atau sesuaikan nama, satuan, kuantitas, dan harga satuan terkunci.</p>
                </div>
                <button type="button" wire:click="addItem" class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 transition">
                    + Tambah Baris Item
                </button>
            </div>

            @error('items') <div class="text-xs text-red-600">{{ $message }}</div> @enderror

            <div class="overflow-x-auto -mx-3 sm:mx-0">
                <table class="min-w-[700px] w-full text-left text-sm text-zinc-700 dark:text-zinc-300 whitespace-nowrap">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs font-semibold text-zinc-600 dark:text-zinc-300">
                        <tr>
                            <th class="px-3 py-2 w-1/4">Pilih Katalog</th>
                            <th class="px-3 py-2 w-1/4">Nama Item (Snapshot) *</th>
                            <th class="px-3 py-2 w-20">Satuan *</th>
                            <th class="px-3 py-2 w-24">Qty *</th>
                            <th class="px-3 py-2 w-32">Harga Satuan (Rp) *</th>
                            <th class="px-3 py-2 w-32 text-right">Subtotal (Rp)</th>
                            <th class="px-2 py-2 w-10 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($items as $index => $item)
                            <tr class="align-top">
                                <td class="p-2">
                                    <select wire:model.live="items.{{ $index }}.item_id" class="w-full px-2 py-1.5 text-xs border border-zinc-300 rounded dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:outline-none">
                                        <option value="">-- Pilih Katalog --</option>
                                        @foreach ($masterItems as $mItem)
                                            <option value="{{ $mItem->id }}">{{ $mItem->name }} ({{ $mItem->unit }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="p-2">
                                    <input type="text" wire:model.live="items.{{ $index }}.item_name" placeholder="Nama item" class="w-full px-2 py-1.5 text-xs border border-zinc-300 rounded dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:outline-none">
                                    @error("items.{$index}.item_name") <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                </td>
                                <td class="p-2">
                                    <input type="text" wire:model.live="items.{{ $index }}.unit" placeholder="pc, kg..." class="w-full px-2 py-1.5 text-xs border border-zinc-300 rounded dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:outline-none">
                                    @error("items.{$index}.unit") <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                </td>
                                <td class="p-2">
                                    <input type="number" step="0.01" min="0.01" wire:model.live="items.{{ $index }}.qty_ordered" class="w-full px-2 py-1.5 text-xs border border-zinc-300 rounded dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:outline-none">
                                    @error("items.{$index}.qty_ordered") <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                </td>
                                <td class="p-2">
                                    <input type="number" min="0" wire:model.live="items.{{ $index }}.unit_price" class="w-full px-2 py-1.5 text-xs border border-zinc-300 rounded dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:outline-none">
                                    @error("items.{$index}.unit_price") <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                </td>
                                <td class="p-2 text-right font-medium text-xs pt-3 whitespace-nowrap">
                                    Rp {{ number_format($item['subtotal'] ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="p-2 text-center pt-2">
                                    @if (count($items) > 1)
                                        <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 text-sm font-bold">&times;</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-zinc-50 dark:bg-zinc-800/60 font-semibold">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-right text-sm">TOTAL NILAI PO:</td>
                            <td class="px-3 py-3 text-right text-base text-blue-600 dark:text-blue-400 font-bold whitespace-nowrap">
                                Rp {{ number_format($this->total_amount, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4">
            <a href="{{ route('purchase-orders.index') }}" wire:navigate class="inline-flex items-center justify-center px-5 py-2.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl dark:bg-zinc-800 dark:text-zinc-300 transition w-full sm:w-auto">
                Batal
            </a>
            <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-md shadow-indigo-600/30 transition transform active:scale-95 w-full sm:w-auto">
                Terbitkan Dokumen PO
            </button>
        </div>
    </form>
</div>

