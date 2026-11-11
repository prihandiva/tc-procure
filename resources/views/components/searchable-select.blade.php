@props([
    'options' => [],
    'wireModel' => null,
    'alpineModel' => null,
    'live' => false,
    'emptyValue' => '',
    'placeholder' => '-- Pilih --',
    'searchPlaceholder' => 'Ketik untuk mencari...',
    'clearable' => true,
    'size' => 'sm',
])

@php
    $triggerTextClass = $size === 'xs' ? 'text-xs py-1.5 sm:py-2' : 'text-sm py-2';
    $emptyValueJson = json_encode($emptyValue);
@endphp

<div
    x-data="{
        open: false,
        search: '',
        highlightedIndex: 0,
        options: @js($options),
        panelStyle: {},
        wireModel: @js($wireModel),
        alpineModel: @js($alpineModel),
        isLive: @js((bool) $live),
        emptyVal: {{ $emptyValueJson }},
        syncOptions() {
            if (this.$refs.optionsMeta && this.$refs.optionsMeta.dataset.options) {
                try {
                    this.options = JSON.parse(this.$refs.optionsMeta.dataset.options);
                } catch (e) {}
            }
        },
        get currentValue() {
            if (this.wireModel && typeof $wire !== 'undefined') {
                return this.wireModel.split('.').reduce((acc, part) => (acc !== null && acc !== undefined ? acc[part] : undefined), $wire);
            }
            if (this.alpineModel) {
                return this.$data[this.alpineModel];
            }
            return this.emptyVal;
        },
        get selectedOption() {
            const val = this.currentValue;
            if (val === null || val === undefined || String(val) === '' || String(val) === String(this.emptyVal)) {
                return null;
            }
            return this.options.find(o => String(o.value) === String(val)) || null;
        },
        get filteredOptions() {
            const q = (this.search || '').trim().toLowerCase();
            if (!q) return this.options;
            return this.options.filter(o => {
                const haystack = [o.label, o.sublabel, o.search].filter(Boolean).join(' ').toLowerCase();
                return haystack.includes(q);
            });
        },
        openDropdown() {
            this.syncOptions();
            this.search = '';
            this.highlightedIndex = 0;
            this.open = true;
            this.$nextTick(() => {
                this.updatePosition();
                this.$refs.searchInput?.focus();
            });
        },
        closeDropdown() {
            this.open = false;
        },
        toggleDropdown() {
            this.open ? this.closeDropdown() : this.openDropdown();
        },
        updatePosition() {
            if (!this.$refs.trigger) return;
            const rect = this.$refs.trigger.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;
            const panelHeight = Math.min(290, window.innerHeight * 0.55);
            const openUp = spaceBelow < panelHeight && spaceAbove > spaceBelow;
            const computedWidth = Math.min(Math.max(rect.width, 240), window.innerWidth - 16);
            let left = rect.left;
            if (left + computedWidth > window.innerWidth - 8) {
                left = Math.max(8, window.innerWidth - computedWidth - 8);
            }
            this.panelStyle = {
                position: 'fixed',
                left: left + 'px',
                width: computedWidth + 'px',
                top: openUp ? 'auto' : (rect.bottom + 4) + 'px',
                bottom: openUp ? (window.innerHeight - rect.top + 4) + 'px' : 'auto',
                zIndex: 99999,
            };
        },
        setValue(val) {
            if (this.wireModel && typeof $wire !== 'undefined') {
                $wire.$set(this.wireModel, val, this.isLive);
            } else if (this.alpineModel) {
                this.$data[this.alpineModel] = val;
            }
            this.$el.dispatchEvent(new CustomEvent('option-selected', {
                bubbles: true,
                detail: { model: this.wireModel || this.alpineModel, value: val }
            }));
        },
        selectOption(opt) {
            this.setValue(opt.value);
            this.closeDropdown();
        },
        clear() {
            this.setValue(this.emptyVal);
            this.closeDropdown();
        },
        moveDown() {
            if (!this.open) { this.openDropdown(); return; }
            const max = this.filteredOptions.length - 1;
            if (max >= 0) {
                this.highlightedIndex = Math.min(max, this.highlightedIndex + 1);
                this.scrollToHighlighted();
            }
        },
        moveUp() {
            if (!this.open) return;
            this.highlightedIndex = Math.max(0, this.highlightedIndex - 1);
            this.scrollToHighlighted();
        },
        selectHighlighted() {
            const list = this.filteredOptions;
            if (this.open && list[this.highlightedIndex]) {
                this.selectOption(list[this.highlightedIndex]);
            }
        },
        scrollToHighlighted() {
            this.$nextTick(() => {
                const container = this.$refs.optionsList;
                const el = container?.querySelector(`[data-idx='${this.highlightedIndex}']`);
                if (el) {
                    el.scrollIntoView({ block: 'nearest' });
                }
            });
        }
    }"
    x-init="syncOptions()"
    @resize.window="if (open) updatePosition()"
    @scroll.window="if (open) updatePosition()"
    class="relative w-full"
>
    <!-- Live Options Store synced automatically on Livewire morphs -->
    <span class="hidden" x-ref="optionsMeta" data-options="{{ json_encode($options) }}"></span>

    <!-- Trigger Button -->
    <button
        type="button"
        x-ref="trigger"
        @click="toggleDropdown()"
        @keydown.arrow-down.prevent="moveDown()"
        class="w-full flex items-center justify-between gap-2 px-3 {{ $triggerTextClass }} text-left border border-slate-300 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden transition cursor-pointer"
    >
        <span class="min-w-0 flex-1 truncate">
            <template x-if="selectedOption">
                <span class="font-medium text-slate-900 dark:text-zinc-100" x-text="selectedOption.label + (selectedOption.sublabel ? ' • ' + selectedOption.sublabel : '')"></span>
            </template>
            <template x-if="!selectedOption">
                <span class="text-slate-400 dark:text-zinc-400">{{ $placeholder }}</span>
            </template>
        </span>

        <span class="flex items-center gap-1 shrink-0">
            @if ($clearable)
                <span
                    x-show="selectedOption"
                    x-cloak
                    @click.stop="clear()"
                    title="Hapus pilihan"
                    class="inline-flex items-center justify-center size-4 rounded-full text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition"
                >
                    <flux:icon icon="x-mark" class="size-3.5" />
                </span>
            @endif
            <flux:icon icon="chevron-down" class="size-4 text-slate-400 transition-transform duration-150" ::class="open ? 'rotate-180 text-indigo-500' : ''" />
        </span>
    </button>

    <!-- Teleported Dropdown Menu (Never clipped by overflow-hidden / table scroll) -->
    <template x-teleport="body">
        <div
            data-searchable-panel="true"
            x-show="open"
            x-cloak
            :style="panelStyle"
            @click.outside="if (!$refs.trigger.contains($event.target)) closeDropdown()"
            @keydown.escape.window="closeDropdown()"
            class="rounded-xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 shadow-2xl overflow-hidden"
        >
            <!-- Search Input -->
            <div class="p-2 border-b border-slate-100 dark:border-zinc-800 bg-slate-50/70 dark:bg-zinc-800/60">
                <div class="relative">
                    <flux:icon icon="magnifying-glass" class="size-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input
                        type="text"
                        x-ref="searchInput"
                        x-model="search"
                        @input="highlightedIndex = 0"
                        @keydown.arrow-down.prevent="moveDown()"
                        @keydown.arrow-up.prevent="moveUp()"
                        @keydown.enter.prevent="selectHighlighted()"
                        placeholder="{{ $searchPlaceholder }}"
                        class="w-full pl-8 pr-7 py-1.5 text-xs border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    >
                    <button
                        type="button"
                        x-show="search"
                        @click="search = ''; $refs.searchInput?.focus()"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer"
                    >
                        <flux:icon icon="x-mark" class="size-3.5" />
                    </button>
                </div>
            </div>

            <!-- Options List -->
            <div x-ref="optionsList" class="max-h-60 overflow-y-auto divide-y divide-slate-50 dark:divide-zinc-800/60 p-1">
                <template x-if="filteredOptions.length === 0">
                    <div class="px-3 py-5 text-center text-xs text-slate-400">
                        Tidak ada pilihan cocok dengan "<span class="font-semibold text-slate-600 dark:text-zinc-300" x-text="search"></span>".
                    </div>
                </template>

                <template x-for="(opt, idx) in filteredOptions" :key="String(opt.value)">
                    <button
                        type="button"
                        :data-idx="idx"
                        @click="selectOption(opt)"
                        @mouseenter="highlightedIndex = idx"
                        class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-left text-xs transition cursor-pointer"
                        :class="{
                            'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-200': highlightedIndex === idx,
                            'font-semibold bg-indigo-50/40 dark:bg-indigo-950/30': selectedOption && String(selectedOption.value) === String(opt.value) && highlightedIndex !== idx,
                            'text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-800/70': highlightedIndex !== idx
                        }"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="truncate font-semibold" x-text="opt.label"></div>
                            <template x-if="opt.sublabel">
                                <div class="text-[11px] text-slate-400 dark:text-zinc-400 truncate mt-0.5" x-text="opt.sublabel"></div>
                            </template>
                        </div>
                        <template x-if="selectedOption && String(selectedOption.value) === String(opt.value)">
                            <flux:icon icon="check" class="size-4 text-indigo-600 dark:text-indigo-400 shrink-0" />
                        </template>
                    </button>
                </template>
            </div>
        </div>
    </template>
</div>
