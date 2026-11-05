<div class="p-6 max-w-7xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Master Tahapan Pembayaran</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60">
                    {{ $stages->count() }} Tahap
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                Konfigurasi tahapan proses berkas pengajuan pembayaran (misal: ACC Finance, Kasir Pembayaran, Posting SAP).
            </p>
        </div>

        @if ($canManage)
            <div class="flex items-center gap-2">
                <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-md shadow-indigo-600/30 transition transform active:scale-95">
                    <flux:icon icon="plus" class="size-4" />
                    <span>Tambah Tahapan</span>
                </button>
            </div>
        @endif
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm dark:bg-emerald-950/40 dark:border-emerald-800/60 dark:text-emerald-300 shadow-2xs">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600" />
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Stages Table --}}
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm dark:divide-zinc-800">
                <thead class="bg-slate-50/80 dark:bg-zinc-800/60 text-[11px] uppercase font-bold text-slate-500 dark:text-zinc-400 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 text-center w-20">Urutan</th>
                        <th class="px-6 py-3.5">Nama Tahapan</th>
                        <th class="px-6 py-3.5 text-center">Tahapan Akhir (Final Posting)?</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        @if ($canManage)
                            <th class="px-6 py-3.5 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse ($stages as $stage)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 font-bold text-xs border border-indigo-200/60">
                                    {{ $stage->sort_order }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                {{ $stage->name }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($stage->is_final)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        Ya (Final SAP)
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">Bukan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($stage->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/60">
                                        <span class="size-1.5 rounded-full bg-rose-500"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            @if ($canManage)
                                <td class="px-6 py-4 text-right space-x-2">
                                    <button wire:click="edit({{ $stage->id }})" class="px-2.5 py-1 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg dark:bg-indigo-950/60 dark:text-indigo-300 transition">
                                        Ubah
                                    </button>
                                    <button wire:click="toggleActive({{ $stage->id }})" class="px-2.5 py-1 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300 rounded-lg transition">
                                        {{ $stage->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManage ? 5 : 4 }}" class="px-6 py-12 text-center text-slate-400 text-xs">
                                Belum ada tahapan yang dikonfigurasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Form --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">
                        {{ $editingStageId ? 'Ubah Tahapan' : 'Tambah Tahapan Baru' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Nama Tahapan *</label>
                        <input type="text" wire:model="name" placeholder="Contoh: Proses ACC Finance" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                        @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Urutan Eksekusi *</label>
                        <input type="number" wire:model="sort_order" placeholder="1" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                        @error('sort_order') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1 pt-1">
                        <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-zinc-300 font-medium">
                            <input type="checkbox" wire:model="is_final" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                            <span>Tandai sebagai Tahapan Akhir (Final Posting)</span>
                        </label>
                        <p class="text-[11px] text-slate-400 pl-6">
                            Tahapan ini otomatis ditetapkan saat PR berhasil diposting ke SAP.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-zinc-300 font-medium">
                            <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                            <span>Status Aktif</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition dark:bg-zinc-800 dark:text-zinc-300">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-xs transition">
                            Simpan Tahapan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
