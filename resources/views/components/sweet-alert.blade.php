<div
    x-data="sweetAlertToast()"
    x-on:toast.window="addToast($event.detail)"
    class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 max-w-sm w-full pointer-events-none px-4 sm:px-0"
    role="region"
    aria-label="Notifikasi Sistem"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 translate-x-8 scale-95"
            class="pointer-events-auto relative overflow-hidden rounded-2xl bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xl border p-4 shadow-2xl transition-all"
            :class="{
                'border-emerald-300 dark:border-emerald-800/80 shadow-emerald-500/10': toast.type === 'success',
                'border-rose-300 dark:border-rose-800/80 shadow-rose-500/10': toast.type === 'error',
                'border-amber-300 dark:border-amber-800/80 shadow-amber-500/10': toast.type === 'warning',
                'border-indigo-300 dark:border-indigo-800/80 shadow-indigo-500/10': toast.type === 'info'
            }"
        >
            <div class="flex items-start gap-3.5">
                <!-- SweetAlert Style Icon -->
                <div class="shrink-0 mt-0.5">
                    <template x-if="toast.type === 'success'">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60 shadow-xs">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/60 shadow-xs">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </div>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/60 shadow-xs">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/60 shadow-xs">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                        </div>
                    </template>
                </div>

                <!-- Text Content -->
                <div class="flex-1 min-w-0 pr-2">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate" x-text="toast.title"></h4>
                    <p class="text-xs text-slate-600 dark:text-zinc-300 mt-0.5 leading-relaxed break-words" x-text="toast.message"></p>
                </div>

                <!-- Close Button -->
                <button
                    type="button"
                    @click="removeToast(toast.id)"
                    class="shrink-0 p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition"
                    aria-label="Tutup notifikasi"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Auto-dismiss Progress Bar -->
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-slate-100 dark:bg-zinc-800 overflow-hidden">
                <div
                    class="h-full transition-all ease-linear"
                    :class="{
                        'bg-emerald-500': toast.type === 'success',
                        'bg-rose-500': toast.type === 'error',
                        'bg-amber-500': toast.type === 'warning',
                        'bg-indigo-500': toast.type === 'info'
                    }"
                    :style="`width: ${toast.progress}%; transition-duration: 50ms;`"
                ></div>
            </div>
        </div>
    </template>
</div>

<script>
function sweetAlertToast() {
    return {
        toasts: [],
        init() {
            @if (session()->has('toast'))
                @php $t = session('toast'); @endphp
                this.addToast({
                    type: @json($t['type'] ?? 'info'),
                    title: @json($t['title'] ?? 'Pemberitahuan'),
                    message: @json($t['message'] ?? '')
                });
            @endif

            @if (session()->has('success'))
                this.addToast({
                    type: 'success',
                    title: 'Berhasil!',
                    message: @json(session('success'))
                });
            @endif

            @if (session()->has('error'))
                this.addToast({
                    type: 'error',
                    title: 'Terjadi Kesalahan!',
                    message: @json(session('error'))
                });
            @endif

            @if (session()->has('status'))
                this.addToast({
                    type: 'info',
                    title: 'Status',
                    message: @json(session('status'))
                });
            @endif

            @if ($errors->any())
                this.addToast({
                    type: 'error',
                    title: 'Perhatian!',
                    message: @json($errors->first())
                });
            @endif

            window.sweetToast = (toast) => this.addToast(toast);
        },
        addToast(toast) {
            if (!toast || !toast.message) return;
            const id = Date.now() + Math.random().toString(36).substring(2, 7);
            const duration = toast.duration || 4500;
            const newToast = {
                id: id,
                type: toast.type || 'info',
                title: toast.title || (toast.type === 'success' ? 'Berhasil' : (toast.type === 'error' ? 'Kesalahan' : 'Pemberitahuan')),
                message: toast.message,
                visible: true,
                progress: 100
            };
            this.toasts.push(newToast);

            const startTime = Date.now();
            const interval = setInterval(() => {
                const elapsed = Date.now() - startTime;
                const remainingPct = Math.max(0, 100 - (elapsed / duration) * 100);
                const current = this.toasts.find(t => t.id === id);
                if (current) {
                    current.progress = remainingPct;
                }
                if (elapsed >= duration) {
                    clearInterval(interval);
                    this.removeToast(id);
                }
            }, 50);
        },
        removeToast(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index > -1) {
                this.toasts[index].visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 250);
            }
        }
    };
}
</script>
