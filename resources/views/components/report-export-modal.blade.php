@props([
    'defaultType' => 'po',
    'triggerClass' => null,
    'wrapperClass' => null,
    'buttonLabel' => 'Ekspor Laporan',
    'showTrigger' => true,
])

@php
    $user = auth()->user();
    $isVendor = $user?->isVendor();
    $vendors = \App\Models\Vendor::active()->orderBy('name')->get();
@endphp

<div
    x-data="{
        open: false,
        reportType: '{{ $defaultType }}',
        dateFrom: '',
        dateTo: '',
        vendorId: '{{ $isVendor ? $user->vendor_id : 'all' }}',
        status: 'all',
        isVendor: {{ $isVendor ? 'true' : 'false' }},

        init() {
            window.addEventListener('open-export-modal', (e) => {
                if (e.detail && e.detail.type) {
                    this.reportType = e.detail.type;
                }
                this.open = true;
            });
        },

        setDateShortcut(period) {
            const now = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const formatDate = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

            if (period === 'today') {
                this.dateFrom = formatDate(now);
                this.dateTo = formatDate(now);
            } else if (period === 'this_month') {
                const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                this.dateFrom = formatDate(firstDay);
                this.dateTo = formatDate(lastDay);
            } else if (period === 'this_year') {
                const firstDay = new Date(now.getFullYear(), 0, 1);
                const lastDay = new Date(now.getFullYear(), 11, 31);
                this.dateFrom = formatDate(firstDay);
                this.dateTo = formatDate(lastDay);
            } else if (period === 'all') {
                this.dateFrom = '';
                this.dateTo = '';
            }
        },

        resetAllFilters() {
            this.dateFrom = '';
            this.dateTo = '';
            if (!this.isVendor) {
                this.vendorId = 'all';
            }
            this.status = 'all';
        },

        submitExport(format, inline = false) {
            this.$refs.exportFormat.value = format;
            this.$refs.exportInline.value = inline ? '1' : '0';
            this.$refs.exportForm.target = inline ? '_blank' : '_self';
            this.$refs.exportForm.submit();
            if (!inline) {
                setTimeout(() => { this.open = false; }, 800);
            }
        }
    }"
    class="{{ $wrapperClass ?? 'inline-block' }}"
>
    <!-- Optional Trigger Button -->
    @if ($showTrigger)
        <button
            type="button"
            @click="open = true"
            class="{{ $triggerClass ?? 'w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 text-xs font-bold hover:bg-slate-50 dark:hover:bg-zinc-700/60 shadow-2xs transition transform active:scale-95 cursor-pointer whitespace-nowrap' }}"
            title="Buka Menu Ekspor Laporan Excel / PDF"
        >
            <flux:icon icon="arrow-down-tray" class="size-4 text-indigo-600 dark:text-indigo-400" />
            <span>{{ $buttonLabel }}</span>
        </button>
    @endif

    <!-- Modal Dialog Backdrop -->
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
        >
            <!-- Modal Container -->
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                @click.outside="if (!$event.target.closest('[data-searchable-panel]')) open = false"
                class="relative w-full max-w-xl rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-2xl overflow-hidden"
            >
                <!-- Form Native Submission to ReportExportController -->
                <form x-ref="exportForm" action="{{ route('reports.export') }}" method="POST">
                    @csrf
                    <input type="hidden" name="format" x-ref="exportFormat" value="excel">
                    <input type="hidden" name="inline" x-ref="exportInline" value="0">
                    <input type="hidden" name="report_type" x-model="reportType">
                    <input type="hidden" name="date_from" x-model="dateFrom">
                    <input type="hidden" name="date_to" x-model="dateTo">
                    <input type="hidden" name="vendor_id" x-model="vendorId">
                    <input type="hidden" name="status" x-model="status">

                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-zinc-800/60 border-b border-slate-200 dark:border-zinc-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-xs">
                                <flux:icon icon="document-chart-bar" class="size-5" />
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Ekspor Laporan Transaksi</h3>
                                <p class="text-xs text-slate-500 dark:text-zinc-400">Pilih jenis data, kriteria filter, dan format unduhan (Excel / PDF).</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="open = false"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 hover:bg-slate-200/60 dark:hover:bg-zinc-700/60 transition cursor-pointer"
                        >
                            <flux:icon icon="x-mark" class="size-5" />
                        </button>
                    </div>

                    <!-- Modal Body / Filter Controls -->
                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                        <!-- 1. Pilihan Jenis Laporan -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
                                1. Jenis Laporan
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <button
                                    type="button"
                                    @click="reportType = 'po'; status = 'all'"
                                    :class="reportType === 'po' ? 'bg-indigo-600 text-white font-bold border-indigo-600 shadow-2xs' : 'bg-slate-50 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border-slate-200 dark:border-zinc-700 hover:bg-slate-100'"
                                    class="p-2.5 rounded-xl border text-xs text-left transition cursor-pointer flex flex-col gap-0.5"
                                >
                                    <span class="font-extrabold">📋 Purchase Order</span>
                                    <span class="text-[10px] opacity-80">Kontrak & Plafon</span>
                                </button>

                                <button
                                    type="button"
                                    @click="reportType = 'deliveries'; status = 'all'"
                                    :class="reportType === 'deliveries' ? 'bg-indigo-600 text-white font-bold border-indigo-600 shadow-2xs' : 'bg-slate-50 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border-slate-200 dark:border-zinc-700 hover:bg-slate-100'"
                                    class="p-2.5 rounded-xl border text-xs text-left transition cursor-pointer flex flex-col gap-0.5"
                                >
                                    <span class="font-extrabold">🚚 Pengiriman DO</span>
                                    <span class="text-[10px] opacity-80">Surat Jalan Fisik</span>
                                </button>

                                <button
                                    type="button"
                                    @click="reportType = 'invoices'; status = 'all'"
                                    :class="reportType === 'invoices' ? 'bg-indigo-600 text-white font-bold border-indigo-600 shadow-2xs' : 'bg-slate-50 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border-slate-200 dark:border-zinc-700 hover:bg-slate-100'"
                                    class="p-2.5 rounded-xl border text-xs text-left transition cursor-pointer flex flex-col gap-0.5"
                                >
                                    <span class="font-extrabold">🧾 Tagihan Invoice</span>
                                    <span class="text-[10px] opacity-80">3-Way Match & TOP</span>
                                </button>

                                <button
                                    type="button"
                                    @click="reportType = 'postings'; status = 'all'"
                                    :class="reportType === 'postings' ? 'bg-indigo-600 text-white font-bold border-indigo-600 shadow-2xs' : 'bg-slate-50 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border-slate-200 dark:border-zinc-700 hover:bg-slate-100'"
                                    class="p-2.5 rounded-xl border text-xs text-left transition cursor-pointer flex flex-col gap-0.5"
                                >
                                    <span class="font-extrabold">💳 Dokumen SAP</span>
                                    <span class="text-[10px] opacity-80">Realisasi Pelunasan</span>
                                </button>

                                @if (! $isVendor)
                                    <button
                                        type="button"
                                        @click="reportType = 'vendor_balances'; status = 'all'"
                                        :class="reportType === 'vendor_balances' ? 'bg-indigo-600 text-white font-bold border-indigo-600 shadow-2xs' : 'bg-slate-50 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border-slate-200 dark:border-zinc-700 hover:bg-slate-100'"
                                        class="p-2.5 rounded-xl border text-xs text-left transition cursor-pointer flex flex-col gap-0.5 col-span-2 sm:col-span-1"
                                    >
                                        <span class="font-extrabold">🏢 Rekap Saldo</span>
                                        <span class="text-[10px] opacity-80">Monitoring Rekanan</span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- 2. Rentang Tanggal (Date Range) -->
                        <div class="space-y-2 pt-1 border-t border-slate-100 dark:border-zinc-800">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">
                                    2. Rentang Tanggal
                                </label>
                                <!-- Shortcut Filter Pills -->
                                <div class="flex items-center gap-1 text-[11px]">
                                    <button
                                        type="button"
                                        @click="setDateShortcut('today')"
                                        class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 font-semibold text-slate-600 dark:text-zinc-300 transition cursor-pointer"
                                    >Hari Ini</button>
                                    <button
                                        type="button"
                                        @click="setDateShortcut('this_month')"
                                        class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 font-semibold text-slate-600 dark:text-zinc-300 transition cursor-pointer"
                                    >Bulan Ini</button>
                                    <button
                                        type="button"
                                        @click="setDateShortcut('this_year')"
                                        class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 font-semibold text-slate-600 dark:text-zinc-300 transition cursor-pointer"
                                    >Tahun Ini</button>
                                    <button
                                        type="button"
                                        @click="setDateShortcut('all')"
                                        class="px-2 py-0.5 rounded-md bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 font-semibold text-indigo-700 dark:text-indigo-300 transition cursor-pointer"
                                    >Semua</button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <span class="text-[11px] font-medium text-slate-500 dark:text-zinc-400 block mb-1">Dari Tanggal:</span>
                                    <input
                                        type="date"
                                        x-model="dateFrom"
                                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                                    />
                                </div>
                                <div>
                                    <span class="text-[11px] font-medium text-slate-500 dark:text-zinc-400 block mb-1">Sampai Tanggal:</span>
                                    <input
                                        type="date"
                                        x-model="dateTo"
                                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- 3. Filter Vendor Rekanan -->
                        @if (! $isVendor)
                            <div class="pt-1 border-t border-slate-100 dark:border-zinc-800">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
                                    3. Filter Rekanan Vendor
                                </label>
                                <x-searchable-select
                                    alpine-model="vendorId"
                                    empty-value="all"
                                    placeholder="Semua Rekanan Vendor (Tanpa Filter Vendor)"
                                    search-placeholder="Ketik nama vendor..."
                                    :options="$vendors->map(fn ($v) => [
                                        'value' => $v->id,
                                        'label' => $v->name,
                                    ])->values()->all()"
                                />
                            </div>
                        @else
                            <div class="pt-1 border-t border-slate-100 dark:border-zinc-800 text-xs text-slate-500 dark:text-zinc-400">
                                <span class="font-bold text-slate-700 dark:text-zinc-300">Rekanan:</span> {{ $user->vendor?->name ?? 'Vendor Anda' }} (Otomatis Terfilter)
                            </div>
                        @endif

                        <!-- 4. Filter Status Transaksi -->
                        <div x-show="reportType !== 'vendor_balances' && reportType !== 'postings'" class="pt-1 border-t border-slate-100 dark:border-zinc-800">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
                                4. Filter Status
                            </label>

                            <!-- PO Status Options -->
                            <div x-show="reportType === 'po'">
                                <select x-model="status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                                    <option value="all">Semua Status (Tanpa Filter Status)</option>
                                    <option value="active">Aktif (Active)</option>
                                    <option value="completed">Selesai (Completed)</option>
                                    <option value="cancelled">Dibatalkan (Cancelled)</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>

                            <!-- Deliveries DO Status Options -->
                            <div x-show="reportType === 'deliveries'">
                                <select x-model="status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                                    <option value="all">Semua Status (Tanpa Filter Status)</option>
                                    <option value="submitted">Menunggu Verifikasi Tim TC</option>
                                    <option value="confirmed">Terkonfirmasi Tim TC</option>
                                    <option value="rejected">Ditolak</option>
                                </select>
                            </div>

                            <!-- Invoices Status Options -->
                            <div x-show="reportType === 'invoices'">
                                <select x-model="status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                                    <option value="all">Semua Status (Tanpa Filter Status)</option>
                                    <option value="submitted">Diajukan (Menunggu Tim TC)</option>
                                    <option value="approved">Disetujui Tim TC</option>
                                    <option value="in_payment">Dalam Proses Pembayaran</option>
                                    <option value="paid">Lunas (Paid)</option>
                                    <option value="rejected">Ditolak</option>
                                </select>
                            </div>
                        </div>

                        <!-- Reset All Filters Action Button -->
                        <div class="pt-2 flex items-center justify-between text-xs border-t border-slate-100 dark:border-zinc-800">
                            <span class="text-slate-500 dark:text-zinc-400">Ingin mencetak seluruh data tanpa batasan?</span>
                            <button
                                type="button"
                                @click="resetAllFilters()"
                                class="inline-flex items-center gap-1 font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 hover:underline cursor-pointer"
                            >
                                <flux:icon icon="arrow-path" class="size-3.5" />
                                <span>Reset (Tanpa Filter)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Footer Actions -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-zinc-800/60 border-t border-slate-200 dark:border-zinc-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                        <button
                            type="button"
                            @click="submitExport('pdf', true)"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-zinc-300 hover:bg-slate-200/60 dark:hover:bg-zinc-700 transition cursor-pointer"
                            title="Buka pratinjau PDF di tab browser baru"
                        >
                            <flux:icon icon="eye" class="size-4 text-slate-500" />
                            <span>Pratinjau PDF</span>
                        </button>

                        <div class="flex items-center gap-2">
                            <!-- Download Excel Button -->
                            <button
                                type="button"
                                @click="submitExport('excel')"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/25 transition transform active:scale-95 cursor-pointer"
                            >
                                <flux:icon icon="table-cells" class="size-4" />
                                <span>Unduh Excel (.xlsx)</span>
                            </button>

                            <!-- Download PDF Button -->
                            <button
                                type="button"
                                @click="submitExport('pdf', false)"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-md shadow-rose-600/25 transition transform active:scale-95 cursor-pointer"
                            >
                                <flux:icon icon="document-text" class="size-4" />
                                <span>Unduh PDF (.pdf)</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

