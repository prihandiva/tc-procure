<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Master Data Item Snack & Jasa</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Katalog standar item konsumsi pelatihan, condiments, dan ongkos kirim.</p>
        </div>
        @can('create', App\Models\Item::class)
            <button wire:click="create" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                + Tambah Item
            </button>
        @endcan
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm dark:bg-green-900/30 dark:border-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filter & Search -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="sm:col-span-1">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama item atau satuan..." class="w-full px-4 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
            <select wire:model.live="type" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="all">Semua Tipe (Barang & Jasa)</option>
                <option value="goods">Barang (Konsumsi / Perlengkapan)</option>
                <option value="service">Jasa (Ongkos Kirim)</option>
            </select>
        </div>
        <div>
            <select wire:model.live="status" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="all">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-700 dark:text-zinc-300 divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60 font-semibold text-zinc-600 dark:text-zinc-300">
                    <tr>
                        <th class="px-6 py-3">Nama Item</th>
                        <th class="px-6 py-3 text-center">Tipe</th>
                        <th class="px-6 py-3 text-center">Satuan</th>
                        <th class="px-6 py-3 text-right">Harga Default (Katalog)</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($items as $item)
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-6 py-4 font-medium text-zinc-900 dark:text-zinc-100">{{ $item->name }}</td>
                            <td class="px-6 py-4 text-center">
                                @if ($item->isGoods())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">Barang</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">Jasa</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-xs">{{ $item->unit }}</td>
                            <td class="px-6 py-4 text-right font-medium">
                                {{ $item->default_price ? 'Rp ' . number_format($item->default_price, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($item->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                @can('update', $item)
                                    <button wire:click="edit({{ $item->id }})" class="text-blue-600 hover:text-blue-800 font-medium text-xs">Ubah</button>
                                    <button wire:click="toggleActive({{ $item->id }})" class="text-zinc-500 hover:text-zinc-700 font-medium text-xs">
                                        {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-zinc-400">Tidak ada master item ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
            {{ $items->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-800 w-full max-w-lg p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $editingItemId ? 'Ubah Master Item' : 'Tambah Master Item Baru' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-zinc-400 hover:text-zinc-600">&times;</button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nama Item *</label>
                        <input type="text" wire:model="name" placeholder="contoh: Snack Basah, Nasi Box..." class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tipe Item *</label>
                            <select wire:model="itemType" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="goods">Barang</option>
                                <option value="service">Jasa (Ongkir/Lainnya)</option>
                            </select>
                            @error('itemType') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Satuan *</label>
                            <input type="text" wire:model="unit" placeholder="pc, box, pak, kg, au..." class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            @error('unit') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Harga Default (Saran / Katalog)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400 text-sm">Rp</div>
                            <input type="number" wire:model="default_price" min="0" placeholder="contoh: 3500" class="w-full pl-10 pr-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <p class="text-xs text-zinc-400 mt-1">Harga ini hanya sebagai saran saat membuat PO. Harga PO tetap dapat disesuaikan.</p>
                        @error('default_price') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="item_active" wire:model="is_active" class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                        <label for="item_active" class="text-sm text-zinc-700 dark:text-zinc-300">Item Aktif</label>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-medium text-zinc-600 bg-zinc-100 rounded-lg hover:bg-zinc-200 transition dark:bg-zinc-800 dark:text-zinc-300">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

