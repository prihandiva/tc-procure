<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Master Data Item Snack & Jasa</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60">
                    {{ $items->total() }} Item
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Katalog standar item makanan/snack pelatihan, perlengkapan training, dan ongkos kirim catering.</p>
        </div>
        @can('create', App\Models\Item::class)
            <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-md shadow-indigo-600/30 transition transform active:scale-95">
                <flux:icon icon="plus" class="size-4" />
                <span>Tambah Item Baru</span>
            </button>
        @endcan
    </div>

    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm dark:bg-emerald-950/40 dark:border-emerald-800/60 dark:text-emerald-300 shadow-2xs">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600" />
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Filter & Search -->
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-4 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama item atau satuan..." class="w-full pl-9 pr-4 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                <flux:icon icon="magnifying-glass" class="size-4 text-slate-400 absolute left-3 top-2.5" />
            </div>
            <div>
                <select wire:model.live="type" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                    <option value="all">Semua Tipe (Barang & Jasa)</option>
                    <option value="goods">Barang (Konsumsi / Perlengkapan)</option>
                    <option value="service">Jasa (Ongkos Kirim)</option>
                </select>
            </div>
            <div>
                <select wire:model.live="status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                    <option value="all">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-zinc-300 divide-y divide-slate-100 dark:divide-zinc-800">
                <thead class="bg-slate-50/80 dark:bg-zinc-800/60 font-bold text-slate-500 dark:text-zinc-400 text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Nama Item</th>
                        <th class="px-6 py-3.5 text-center">Tipe</th>
                        <th class="px-6 py-3.5 text-center">Satuan</th>
                        <th class="px-6 py-3.5 text-right">Harga Default (Katalog)</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse ($items as $item)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $item->name }}</td>
                            <td class="px-6 py-4 text-center">
                                @if ($item->isGoods())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60">
                                        Barang
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200/60">
                                        Jasa
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-xs text-slate-500">{{ $item->unit }}</td>
                            <td class="px-6 py-4 text-right font-black text-slate-900 dark:text-white">
                                {{ $item->default_price ? 'Rp ' . number_format($item->default_price, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($item->is_active)
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
                            <td class="px-6 py-4 text-right space-x-2">
                                @can('update', $item)
                                    <button wire:click="edit({{ $item->id }})" class="px-2.5 py-1 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg dark:bg-indigo-950/60 dark:text-indigo-300 transition">
                                        Ubah
                                    </button>
                                    <button wire:click="toggleActive({{ $item->id }})" class="px-2.5 py-1 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300 rounded-lg transition">
                                        {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-xs">Tidak ada master item ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-zinc-800">
            {{ $items->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-zinc-800 w-full max-w-lg p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">
                        {{ $editingItemId ? 'Ubah Master Item' : 'Tambah Master Item Baru' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Nama Item *</label>
                        <input type="text" wire:model="name" placeholder="contoh: Snack Basah, Nasi Box..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                        @error('name') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Tipe Item *</label>
                            <select wire:model="itemType" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                                <option value="goods">Barang</option>
                                <option value="service">Jasa (Ongkir/Lainnya)</option>
                            </select>
                            @error('itemType') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Satuan *</label>
                            <input type="text" wire:model="unit" placeholder="pc, box, pak, kg, au..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                            @error('unit') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Harga Default (Katalog Acuan)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs font-bold">Rp</div>
                            <input type="number" wire:model="default_price" min="0" placeholder="contoh: 3500" class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Harga ini hanya sebagai saran otomatis saat input item PO.</p>
                        @error('default_price') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="item_active" wire:model="is_active" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="item_active" class="text-xs font-medium text-slate-700 dark:text-zinc-300">Status Item Aktif</label>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition dark:bg-zinc-800 dark:text-zinc-300">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-xs transition">
                            Simpan Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
