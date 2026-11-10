<div class="space-y-6 w-full max-w-full min-w-0">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <flux:button variant="subtle" icon="arrow-left" href="{{ route('postings.index') }}" wire:navigate>
            Kembali
        </flux:button>
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Catat Dokumen Posting SAP</flux:heading>
            <flux:text class="text-xs text-zinc-500">
                Hubungkan Payment Request yang telah selesai diproses finance ke nomor dokumen SAP untuk pelunasan tagihan
            </flux:text>
        </div>
    </div>

    {{-- Error / Validation Alerts --}}
    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/20 bg-rose-50/50 p-4 text-sm text-rose-800 dark:bg-rose-950/30 dark:text-rose-300">
            <div class="font-bold">Mohon periksa kembali formulir berikut:</div>
            <ul class="mt-1 list-inside list-disc text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Left 2 cols: PR Selection --}}
            <div class="space-y-6 lg:col-span-2 min-w-0">
                <flux:card class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="lg" class="font-bold">Pilih Payment Request</flux:heading>
                            <flux:text class="text-xs text-zinc-500">
                                Centang Payment Request yang termasuk dalam posting dokumen SAP ini
                            </flux:text>
                        </div>
                        <flux:badge color="indigo">{{ count($selected_pr_ids) }} Dipilih</flux:badge>
                    </div>

                    @error('selected_pr_ids')
                        <flux:text class="text-xs text-rose-500">{{ $message }}</flux:text>
                    @enderror

                    @if ($unpostedPrs->isNotEmpty())
                        <div class="w-full max-w-full overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <table class="min-w-[620px] w-full divide-y divide-zinc-200 text-left text-sm dark:divide-zinc-700 whitespace-nowrap">
                                <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs uppercase font-medium text-zinc-500">
                                    <tr>
                                        <th class="px-4 py-3 text-center w-12">Pilih</th>
                                        <th class="px-4 py-3">No. PR</th>
                                        <th class="px-4 py-3">Tahapan Saat Ini</th>
                                        <th class="px-4 py-3">Vendor</th>
                                        <th class="px-4 py-3 text-center">Jml Invoice</th>
                                        <th class="px-4 py-3 text-right">Total Nilai</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach ($unpostedPrs as $pr)
                                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 {{ in_array($pr->id, $selected_pr_ids) ? 'bg-indigo-50/40 dark:bg-indigo-950/20' : '' }}">
                                            <td class="px-4 py-3 text-center">
                                                <input
                                                    type="checkbox"
                                                    value="{{ $pr->id }}"
                                                    wire:model.live="selected_pr_ids"
                                                    class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900"
                                                />
                                            </td>
                                            <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-white">
                                                {{ $pr->number }}
                                                <div class="text-[11px] text-zinc-400 font-normal">
                                                    {{ $pr->submitted_at?->format('d/m/Y H:i') }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <flux:badge size="sm" color="indigo">
                                                    {{ $pr->currentStage?->name ?? '-' }}
                                                </flux:badge>
                                            </td>
                                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                                {{ $pr->vendors->pluck('name')->implode(', ') ?: '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-center text-zinc-600 dark:text-zinc-300">
                                                {{ $pr->invoices->count() }}
                                            </td>
                                            <td class="px-4 py-3 text-right font-bold text-zinc-900 dark:text-white">
                                                Rp {{ number_format($pr->total_amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-zinc-300 p-8 text-center text-zinc-500 dark:border-zinc-700">
                            Tidak ada Payment Request yang siap diposting. Pastikan sudah membuat Payment Request terlebih dahulu.
                        </div>
                    @endif
                </flux:card>
            </div>

            {{-- Right 1 col: SAP Posting Details --}}
            <div class="space-y-6 w-full max-w-full min-w-0">
                <flux:card class="space-y-4">
                    <flux:heading size="lg" class="font-bold">Detail Dokumen SAP</flux:heading>

                    <div class="space-y-4">
                        <div>
                            <flux:input
                                wire:model="document_number"
                                label="Nomor Dokumen SAP *"
                                placeholder="Contoh: 5000012345 atau 1900012345"
                            />
                            @error('document_number') <flux:text class="text-xs text-rose-500 mt-1">{{ $message }}</flux:text> @enderror
                        </div>

                        <div>
                            <flux:input
                                type="date"
                                wire:model="posting_date"
                                label="Tanggal Posting SAP *"
                            />
                            @error('posting_date') <flux:text class="text-xs text-rose-500 mt-1">{{ $message }}</flux:text> @enderror
                        </div>

                        <div>
                            <flux:input
                                type="number"
                                wire:model="total_paid"
                                label="Total Nominal Dibayar (Rp) *"
                                placeholder="0"
                            />
                            <flux:text class="text-[11px] text-zinc-400 mt-1">
                                Nilai ini otomatis dihitung dari total PR terpilih, dapat disesuaikan jika ada selisih pembulatan/pajak.
                            </flux:text>
                            @error('total_paid') <flux:text class="text-xs text-rose-500 mt-1">{{ $message }}</flux:text> @enderror
                        </div>

                        <div>
                            <x-file-upload-camera
                                model="payment_proof"
                                :file="$payment_proof"
                                label="Upload Bukti Bayar / Slip Transfer SAP (Foto / PDF)"
                                help="Ambil foto slip transfer kasir fisik atau pilih berkas PDF/JPG dari galeri."
                            />
                        </div>

                        <div>
                            <flux:textarea
                                wire:model="notes"
                                label="Catatan Tambahan (Opsional)"
                                placeholder="Catatan no referensi kasir, nomor cek, atau bilyet giro..."
                                rows="3"
                            />
                            @error('notes') <flux:text class="text-xs text-rose-500 mt-1">{{ $message }}</flux:text> @enderror
                        </div>
                    </div>

                    {{-- Summary Box --}}
                    <div class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-800/60 space-y-2">
                        <div class="flex items-center justify-between text-xs text-zinc-500">
                            <span>Jumlah PR Terpilih:</span>
                            <span class="font-bold text-zinc-900 dark:text-white">{{ count($selected_pr_ids) }} PR</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-zinc-500">
                            <span>Kalkulasi Total PR:</span>
                            <span class="font-bold text-zinc-900 dark:text-white">Rp {{ number_format($total_paid, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-2 text-xs text-zinc-500">
                            <span class="text-emerald-600 dark:text-emerald-400 font-medium">
                                * Setelah disimpan, semua invoice dalam PR terpilih akan otomatis berstatus <strong>LUNAS (PAID)</strong>.
                            </span>
                        </div>
                    </div>

                    <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">Simpan Posting SAP</span>
                        <span wire:loading wire:target="save">Menyimpan Posting...</span>
                    </flux:button>
                </flux:card>
            </div>
        </div>
    </form>
</div>

