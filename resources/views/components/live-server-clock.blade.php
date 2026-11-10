<div
    x-data="liveServerClock({ initialTimeMs: {{ now()->getTimestampMs() }} })"
    class="inline-flex items-center gap-1.5 sm:gap-2 px-2 sm:px-2.5 py-1 sm:py-1.5 rounded-xl bg-slate-100/90 dark:bg-zinc-800/90 border border-slate-200/80 dark:border-zinc-700/70 text-slate-700 dark:text-zinc-200 shadow-2xs whitespace-nowrap shrink-0"
    title="Jam Server Sinkron (Asia/Jakarta • WIB)"
>
    <span class="relative flex size-2 shrink-0">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full size-2 bg-emerald-500"></span>
    </span>
    <flux:icon icon="clock" class="size-3.5 text-indigo-500 dark:text-indigo-400 shrink-0" />
    <div class="flex items-center gap-1 font-mono text-[11px] sm:text-xs whitespace-nowrap">
        <!-- Desktop: Full Indonesian Day and Date (e.g. Selasa, 10 November 2026) -->
        <span class="hidden lg:inline font-semibold text-slate-800 dark:text-zinc-100" x-text="dateFullString"></span>
        <!-- Tablet: Short Date (e.g. 10 Nov 2026) -->
        <span class="hidden sm:inline lg:hidden font-semibold text-slate-800 dark:text-zinc-100" x-text="dateShortString"></span>
        <!-- Separator on tablet & desktop -->
        <span class="hidden sm:inline text-slate-300 dark:text-zinc-600 font-sans">•</span>
        <!-- Live Ticking Time (Always visible on mobile, tablet, and desktop) -->
        <span class="font-bold text-indigo-600 dark:text-indigo-400 tabular-nums" x-text="timeString"></span>
        <span class="text-[10px] font-bold text-slate-400 dark:text-zinc-500">WIB</span>
    </div>
</div>

@once
<script>
    (function() {
        function registerLiveServerClock() {
            if (window.Alpine && !window.Alpine._liveServerClockRegistered) {
                window.Alpine._liveServerClockRegistered = true;
                window.Alpine.data('liveServerClock', (config) => ({
                    offset: (config && config.initialTimeMs ? config.initialTimeMs : Date.now()) - Date.now(),
                    dateFullString: '',
                    dateShortString: '',
                    timeString: '',
                    timer: null,

                    init() {
                        this.update();
                        this.timer = setInterval(() => this.update(), 1000);
                    },

                    destroy() {
                        if (this.timer) clearInterval(this.timer);
                    },

                    update() {
                        const now = new Date(Date.now() + this.offset);

                        // Formatter Indonesia (WIB - Asia/Jakarta)
                        const fullDateFmt = new Intl.DateTimeFormat('id-ID', {
                            timeZone: 'Asia/Jakarta',
                            weekday: 'long',
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric'
                        });

                        const shortDateFmt = new Intl.DateTimeFormat('id-ID', {
                            timeZone: 'Asia/Jakarta',
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric'
                        });

                        const timeFmt = new Intl.DateTimeFormat('id-ID', {
                            timeZone: 'Asia/Jakarta',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: false
                        });

                        this.dateFullString = fullDateFmt.format(now);
                        this.dateShortString = shortDateFmt.format(now);
                        this.timeString = timeFmt.format(now).replace(/\./g, ':');
                    }
                }));
            }
        }

        if (window.Alpine) {
            registerLiveServerClock();
        } else {
            document.addEventListener('alpine:init', registerLiveServerClock);
        }
    })();
</script>
@endonce

