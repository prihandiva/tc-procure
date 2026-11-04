<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Master Tahapan Pembayaran (Payment Stages)</flux:heading>
            <flux:text class="text-xs text-zinc-500">
                Konfigurasi alur tahapan berkas pengajuan pembayaran (misal: Proses ACC, Proses Finance, Posted SAP)
            </flux:text>
        </div>

        @if ($canManage)
            <div class="flex items-center gap-2">
                <flux:button variant="primary" icon="plus" wire:click="create">
                    Tambah Tahapan
                </flux:button>
            </div>
        @endif
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="rounded-xl border border-emerald-500/20 bg-emerald-50/50 p-4 text-sm text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stages Table --}}
    <flux:card class="space-y-4">
        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 text-left text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs uppercase font-medium text-zinc-500">
                    <tr>
                        <th class="px-4 py-3 text-center w-16">Urutan</th>
                        <th class="px-4 py-3">Nama Tahapan</th>
                        <th class="px-4 py-3 text-center">Tahapan Akhir (Final)?</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        @if ($canManage)
                            <th class="px-4 py-3 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($stages as $stage)
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40">
                            <td class="px-4 py-3 text-center font-bold text-zinc-700 dark:text-zinc-300">
                                {{ $stage->sort_order }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-white">
                                {{ $stage->name }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($stage->is_final)
                                    <flux:badge color="green" size="sm">Ya (Final)</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Bukan</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($stage->is_active)
                                    <flux:badge color="green" size="sm">Aktif</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Non-aktif</flux:badge>
                                @endif
                            </td>
                            @if ($canManage)
                                <td class="px-4 py-3 text-center space-x-1">
                                    <flux:button variant="subtle" size="xs" wire:click="edit({{ $stage->id }})">
                                        Edit
                                    </flux:button>
                                    <flux:button variant="subtle" size="xs" wire:click="toggleActive({{ $stage->id }})">
                                        {{ $stage->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </flux:button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManage ? 5 : 4 }}" class="px-4 py-8 text-center text-zinc-500">
                                Belum ada tahapan yang dikonfigurasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>

    {{-- Modal Form --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 space-y-4">
                <flux:heading size="lg" class="font-bold">
                    {{ $editingStageId ? 'Edit Tahapan' : 'Tambah Tahapan Baru' }}
                </flux:heading>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <flux:input wire:model="name" label="Nama Tahapan *" placeholder="Contoh: Proses ACC" />
                        @error('name') <flux:text class="text-xs text-rose-500 mt-1">{{ $message }}</flux:text> @enderror
                    </div>

                    <div>
                        <flux:input type="number" wire:model="sort_order" label="Urutan Eksekusi *" placeholder="1" />
                        @error('sort_order') <flux:text class="text-xs text-rose-500 mt-1">{{ $message }}</flux:text> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                            <input type="checkbox" wire:model="is_final" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900" />
                            <span>Tandai sebagai Tahapan Akhir (Final Posting)</span>
                        </label>
                        <flux:text class="text-[11px] text-zinc-400">
                            Tahapan ini akan otomatis ditetapkan saat PR berhasil diposting ke SAP.
                        </flux:text>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                            <input type="checkbox" wire:model="is_active" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900" />
                            <span>Status Aktif</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <flux:button type="button" variant="subtle" wire:click="$set('showModal', false)">
                            Batal
                        </flux:button>
                        <flux:button type="submit" variant="primary">
                            Simpan
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

