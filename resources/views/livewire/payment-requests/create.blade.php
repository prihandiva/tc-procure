<div class="space-y-6 max-w-5xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Buat Payment Request Baru</h1>
            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Kelompokkan invoice yang telah diverifikasi dan disetujui SPV untuk pengajuan pembayaran ke bagian Accounting / Finance.</p>
        </div>
        <a href="{{ route('payment-requests.index') }}" wire:navigate class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-bold text-slate-600 bg-white hover:bg-slate-100 rounded-xl border border-slate-200/80 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700 transition shadow-2xs w-full sm:w-auto">
            <flux:icon icon="arrow-left" class="size-3.5" />
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <!-- Informasi Header Payment Request -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 dark:border-zinc-800 pb-2">
                1. Data Dokumen Pengajuan (Payment Request)
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nomor Payment Request *</label>
                    <input type="text" wire:model="number" placeholder="contoh: TC/PC/10-X/2025/509" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none font-mono">
                    <p class="text-[11px] text-zinc-400 mt-1">Format internal penomoran pengajuan departemen Training Center.</p>
                    @error('number') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tahap Status Awal *</label>
                    <select wire:model="current_stage_id" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach ($stages as $stg)
                            <option value="{{ $stg->id }}">{{ $stg->name }} (Urutan: {{ $stg->sort_order }})</option>
                        @endforeach
                    </select>
                    @error('current_stage_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Catatan Tambahan (Opsional)</label>
                <textarea wire:model="notes" rows="2" placeholder="Keterangan pengajuan ke ACC, rincian periode invoice..." class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                @error('notes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Pemilihan Invoice yang Disetujui (Approved) -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-2">
                <div>
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">2. Pilih Invoice yang Dikelompokkan</h2>
                    <p class="text-xs text-zinc-400">Centang invoice berstatus Approved untuk diajukan dalam nomor PR ini.</p>
                </div>
                <div class="text-right">
                    <div class="text-xs text-zinc-400 uppercase font-semibold">Total Nilai Pengajuan</div>
                    <div class="text-xl font-bold text-blue-600 dark:text-blue-400">
                        Rp {{ number_format($this->total_amount, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            @error('selected_invoice_ids') <div class="text-xs text-red-600">{{ $message }}</div> @enderror

            @if ($approvedInvoices->isNotEmpty())
                <div class="divide-y divide-zinc-200 dark:divide-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                    @foreach ($approvedInvoices as $inv)
                        <label class="p-4 flex items-center justify-between hover:bg-zinc-50 dark:hover:bg-zinc-800/40 cursor-pointer transition">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" value="{{ $inv->id }}" wire:model.live="selected_invoice_ids" class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <div class="font-bold text-sm text-zinc-900 dark:text-zinc-100 font-mono">
                                        {{ $inv->invoice_number }}
                                    </div>
                                    <div class="text-xs text-zinc-400">
                                        Vendor: <strong class="text-zinc-700 dark:text-zinc-300">{{ $inv->vendor->name }}</strong> &bull;
                                        PO: {{ $inv->purchaseOrder->po_number }} &bull;
                                        Tanggal: {{ $inv->invoice_date->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right font-bold text-base text-zinc-900 dark:text-zinc-100">
                                Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                            </div>
                        </label>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center text-zinc-400 text-sm">
                    Tidak ada invoice berstatus Approved yang tersedia untuk diajukan. Pastikan SPV telah memverifikasi dan menyetujui invoice terlebih dahulu.
                </div>
            @endif
        </div>

        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4">
            <a href="{{ route('payment-requests.index') }}" wire:navigate class="inline-flex items-center justify-center px-5 py-2.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl dark:bg-zinc-800 dark:text-zinc-300 transition w-full sm:w-auto">
                Batal
            </a>
            <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-md shadow-indigo-600/30 transition transform active:scale-95 w-full sm:w-auto">
                Buat Payment Request
            </button>
        </div>
    </form>
</div>

