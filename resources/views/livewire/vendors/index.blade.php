<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Master Data Vendor</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Kelola informasi vendor catering luar dan status keaktifan.</p>
        </div>
        @can('create', App\Models\Vendor::class)
            <button wire:click="create" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                + Tambah Vendor
            </button>
        @endcan
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm dark:bg-green-900/30 dark:border-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filter & Search -->
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama vendor, kontak, atau alamat..." class="w-full px-4 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div class="w-full sm:w-48">
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
                        <th class="px-6 py-3">Nama Vendor</th>
                        <th class="px-6 py-3">Kontak</th>
                        <th class="px-6 py-3">Alamat</th>
                        <th class="px-6 py-3 text-center">Total PO</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($vendors as $vendor)
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-6 py-4 font-medium text-zinc-900 dark:text-zinc-100">{{ $vendor->name }}</td>
                            <td class="px-6 py-4">{{ $vendor->contact ?: '-' }}</td>
                            <td class="px-6 py-4 text-xs max-w-xs truncate">{{ $vendor->address ?: '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                    {{ $vendor->purchase_orders_count }} PO
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($vendor->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                @can('update', $vendor)
                                    <button wire:click="edit({{ $vendor->id }})" class="text-blue-600 hover:text-blue-800 font-medium text-xs">Ubah</button>
                                    <button wire:click="toggleActive({{ $vendor->id }})" class="text-zinc-500 hover:text-zinc-700 font-medium text-xs">
                                        {{ $vendor->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-zinc-400">Tidak ada data vendor ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
            {{ $vendors->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-800 w-full max-w-lg p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $editingVendorId ? 'Ubah Data Vendor' : 'Tambah Vendor Baru' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-zinc-400 hover:text-zinc-600">&times;</button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nama Vendor *</label>
                        <input type="text" wire:model="name" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Kontak / No. Telepon</label>
                        <input type="text" wire:model="contact" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @error('contact') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Alamat Lengkap</label>
                        <textarea wire:model="address" rows="3" class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                        @error('address') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="vendor_active" wire:model="is_active" class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                        <label for="vendor_active" class="text-sm text-zinc-700 dark:text-zinc-300">Vendor Aktif</label>
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

