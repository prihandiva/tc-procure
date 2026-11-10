<div class="space-y-6 max-w-6xl mx-auto w-full min-w-0">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Ubah Purchase Order {{ $purchaseOrder->po_number }}</h1>
            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Pembaruan data PO sebelum ada pengiriman pertama yang terkonfirmasi.</p>
        </div>
        <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" wire:navigate class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-bold text-slate-600 bg-white hover:bg-slate-100 rounded-xl border border-slate-200/80 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700 transition shadow-2xs w-full sm:w-auto">
            <flux:icon icon="arrow-left" class="size-3.5" />
            <span>Kembali ke Detail PO</span>
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
                    <input type="text" wire:model="po_number" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
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
                    <textarea wire:model="notes" rows="2" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                    @error('notes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tambah Dokumen Lampiran Baru (Opsional)</label>
                    <input type="file" wire:model="document_file" class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-zinc-800 dark:file:text-zinc-300">
                    <p class="text-[11px] text-zinc-400 mt-1">Format PDF, JPG, PNG. Maksimal 5 MB.</p>
                    @error('document_file') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Rincian Item PO -->
        <x-po-items-editor
            :items="$items"
            :master-items="$masterItems"
            :catalog-map="$catalogMap"
            :total-amount="$this->total_amount"
            subtitle="Ubah item, kuantitas, atau harga satuan kontrak PO sebelum mulai dikirim."
        />

        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4">
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" wire:navigate class="inline-flex items-center justify-center px-5 py-2.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl dark:bg-zinc-800 dark:text-zinc-300 transition w-full sm:w-auto">
                Batal
            </a>
            <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-md shadow-indigo-600/30 transition transform active:scale-95 w-full sm:w-auto">
                Simpan Perubahan PO
            </button>
        </div>
    </form>
</div>

