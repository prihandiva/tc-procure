<div class="space-y-6 max-w-6xl mx-auto w-full min-w-0">
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
                    <x-searchable-select
                        wire-model="vendor_id"
                        :live="false"
                        placeholder="-- Pilih Vendor --"
                        search-placeholder="Ketik nama vendor..."
                        :options="$vendors->map(fn ($v) => [
                            'value' => $v->id,
                            'label' => $v->name,
                        ])->values()->all()"
                    />
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

                <div class="sm:col-span-2">
                    <x-file-upload-camera
                        model="document_file"
                        :file="$document_file"
                        label="Unggah Berkas Scan / PDF PO (Opsional)"
                        help="Ambil foto berkas kontrak PO fisik atau pilih dokumen PDF/JPG dari galeri."
                    />
                </div>
            </div>
        </div>

        <!-- Rincian Item PO -->
        <x-po-items-editor
            :items="$items"
            :master-items="$masterItems"
            :catalog-map="$catalogMap"
            :total-amount="$this->total_amount"
            subtitle="Pilih dari katalog untuk mengisi nama, satuan, dan harga otomatis dari database, atau ubah sesuai kesepakatan PO ini."
        />

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

