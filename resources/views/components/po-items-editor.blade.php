@props([
    'items' => [],
    'masterItems' => collect(),
    'catalogMap' => [],
    'totalAmount' => 0,
    'subtitle' => 'Pilih dari katalog untuk mengisi nama, satuan, dan harga otomatis dari database, atau ubah sesuai kesepakatan PO ini.',
])

<div
    x-data="{
        catalogMap: @js($catalogMap),
        applyCatalog(index, itemId) {
            if (!itemId || !this.catalogMap[itemId]) return;
            const cat = this.catalogMap[itemId];
            if ($wire.items && $wire.items[index]) {
                $wire.items[index].item_name = cat.name;
                $wire.items[index].unit = cat.unit;
                $wire.items[index].unit_price = Number(cat.default_price || 0);
            }
        },
        rowSubtotal(index) {
            const row = $wire.items?.[index];
            if (!row) return 0;
            const qty = parseFloat(row.qty_ordered) || 0;
            const price = parseInt(row.unit_price, 10) || 0;
            return Math.round(qty * price);
        },
        grandTotal() {
            if (!Array.isArray($wire.items)) return 0;
            return $wire.items.reduce((sum, row) => {
                const qty = parseFloat(row?.qty_ordered) || 0;
                const price = parseInt(row?.unit_price, 10) || 0;
                return sum + Math.round(qty * price);
            }, 0);
        },
        catalogDefaultPrice(index) {
            const row = $wire.items?.[index];
            if (!row || !row.item_id || !this.catalogMap[row.item_id]) return null;
            return parseInt(this.catalogMap[row.item_id].default_price, 10) || 0;
        },
        isCustomPrice(index) {
            const defPrice = this.catalogDefaultPrice(index);
            if (defPrice === null) return false;
            const row = $wire.items?.[index];
            const price = parseInt(row?.unit_price, 10);
            return !isNaN(price) && price !== defPrice;
        },
        resetToCatalogPrice(index) {
            const defPrice = this.catalogDefaultPrice(index);
            if (defPrice === null || !$wire.items?.[index]) return;
            $wire.items[index].unit_price = defPrice;
        },
        formatRp(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
        }
    }"
    class="bg-white dark:bg-zinc-900 p-4 sm:p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4"
>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-3">
        <div>
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">2. Rincian Item Barang &amp; Jasa</h2>
            <p class="text-xs text-zinc-400">{{ $subtitle }}</p>
        </div>
        <button type="button" wire:click="addItem" class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 transition shrink-0 self-start sm:self-auto cursor-pointer">
            + Tambah Baris Item
        </button>
    </div>

    @error('items') <div class="text-xs text-red-600">{{ $message }}</div> @enderror

    <div class="w-full max-w-full overflow-x-auto">
        <table class="min-w-[780px] w-full text-left text-sm text-zinc-700 dark:text-zinc-300 whitespace-nowrap">
            <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs font-semibold text-zinc-600 dark:text-zinc-300">
                <tr>
                    <th class="px-3 py-2 min-w-[180px]">Pilih Katalog</th>
                    <th class="px-3 py-2 min-w-[170px]">Nama Item (Snapshot) *</th>
                    <th class="px-3 py-2 min-w-[85px]">Satuan *</th>
                    <th class="px-3 py-2 min-w-[90px]">Qty *</th>
                    <th class="px-3 py-2 min-w-[140px]">Harga Satuan (Rp) *</th>
                    <th class="px-3 py-2 min-w-[120px] text-right">Subtotal (Rp)</th>
                    <th class="px-2 py-2 w-10 text-center"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($items as $index => $item)
                    <tr wire:key="po-item-{{ $item['_key'] ?? $index }}" class="align-top">
                        <td class="p-2">
                            <select
                                wire:model="items.{{ $index }}.item_id"
                                @change="applyCatalog({{ $index }}, $event.target.value)"
                                class="w-full px-2 py-1.5 text-xs border border-zinc-300 rounded dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                                <option value="">-- Pilih Katalog --</option>
                                @foreach ($masterItems as $mItem)
                                    <option value="{{ $mItem->id }}">{{ $mItem->name }} ({{ $mItem->unit }}) - Rp {{ number_format($mItem->default_price, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="p-2">
                            <input
                                type="text"
                                wire:model="items.{{ $index }}.item_name"
                                placeholder="Nama item"
                                class="w-full px-2 py-1.5 text-xs border border-zinc-300 rounded dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                            @error("items.{$index}.item_name") <span class="block text-[10px] text-red-600 mt-0.5">{{ $message }}</span> @enderror
                        </td>
                        <td class="p-2">
                            <input
                                type="text"
                                wire:model="items.{{ $index }}.unit"
                                placeholder="pc, kg..."
                                class="w-full px-2 py-1.5 text-xs border border-zinc-300 rounded dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                            @error("items.{$index}.unit") <span class="block text-[10px] text-red-600 mt-0.5">{{ $message }}</span> @enderror
                        </td>
                        <td class="p-2">
                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                inputmode="decimal"
                                wire:model="items.{{ $index }}.qty_ordered"
                                @focus="$el.select()"
                                class="w-full px-2 py-1.5 text-xs border border-zinc-300 rounded dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                            @error("items.{$index}.qty_ordered") <span class="block text-[10px] text-red-600 mt-0.5">{{ $message }}</span> @enderror
                        </td>
                        <td class="p-2">
                            <input
                                type="number"
                                min="0"
                                step="1"
                                inputmode="numeric"
                                wire:model="items.{{ $index }}.unit_price"
                                @focus="if (String($el.value) === '0') { $el.value = ''; $wire.items[{{ $index }}].unit_price = ''; } else { $el.select(); }"
                                @blur="if ($el.value === '' || $el.value === null) { $el.value = '0'; $wire.items[{{ $index }}].unit_price = 0; }"
                                class="w-full px-2 py-1.5 text-xs font-medium border border-zinc-300 rounded dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                            <template x-if="isCustomPrice({{ $index }})">
                                <div class="mt-1 flex items-center justify-between gap-1 text-[10px] text-amber-600 dark:text-amber-400 font-medium">
                                    <span x-text="'DB: ' + formatRp(catalogDefaultPrice({{ $index }}))"></span>
                                    <button type="button" @click="resetToCatalogPrice({{ $index }})" class="underline hover:text-amber-700 dark:hover:text-amber-300 cursor-pointer">
                                        Reset
                                    </button>
                                </div>
                            </template>
                            @error("items.{$index}.unit_price") <span class="block text-[10px] text-red-600 mt-0.5">{{ $message }}</span> @enderror
                        </td>
                        <td
                            class="p-2 text-right font-medium text-xs pt-3 whitespace-nowrap"
                            x-text="formatRp(rowSubtotal({{ $index }}))"
                        >
                            Rp {{ number_format($item['subtotal'] ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="p-2 text-center pt-2">
                            @if (count($items) > 1)
                                <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 text-sm font-bold cursor-pointer" title="Hapus Baris">&times;</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-zinc-50 dark:bg-zinc-800/60 font-semibold">
                <tr>
                    <td colspan="5" class="px-4 py-3 text-right text-sm">TOTAL NILAI PO:</td>
                    <td
                        class="px-3 py-3 text-right text-base text-blue-600 dark:text-blue-400 font-bold whitespace-nowrap"
                        x-text="formatRp(grandTotal())"
                    >
                        Rp {{ number_format($totalAmount, 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

