<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 dark:bg-zinc-950 dark:text-zinc-100 antialiased font-sans selection:bg-indigo-500 selection:text-white">
        <div class="relative min-h-screen flex flex-col lg:grid lg:grid-cols-12">
            <!-- Left Pane: Executive Hero Showcase (Desktop) -->
            <div class="relative hidden lg:flex lg:col-span-6 xl:col-span-7 flex-col justify-between overflow-hidden bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900 p-10 xl:p-14 text-white border-e border-slate-800">
                <!-- Ambient Glow Mesh -->
                <div class="absolute -right-20 -top-20 size-96 rounded-full bg-indigo-500/15 blur-3xl pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 size-96 rounded-full bg-blue-500/15 blur-3xl pointer-events-none"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_var(--tw-gradient-stops))] from-indigo-500/5 via-transparent to-transparent pointer-events-none"></div>

                <!-- Top Brand -->
                <div class="relative z-10 flex items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group" wire:navigate>
                        <div class="flex size-10 items-center justify-center rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white shadow-lg shadow-indigo-600/30 group-hover:scale-105 transition transform">
                            <x-app-logo-icon class="size-5.5 text-white" />
                        </div>
                        <div>
                            <span class="font-extrabold text-xl tracking-tight text-white block">TC-Procure</span>
                            <span class="text-[10px] uppercase tracking-wider text-indigo-300 font-semibold block">Training Center Procurement</span>
                        </div>
                    </a>

                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1 text-xs font-semibold backdrop-blur-md border border-white/10 text-indigo-200">
                        <span class="size-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Enterprise v1.0</span>
                    </div>
                </div>

                <!-- Center Content: Value Propositions -->
                <div class="relative z-10 my-auto py-10 space-y-8 max-w-xl">
                    <div class="space-y-3">
                        <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-indigo-400">
                            <span>Platform Manajemen Pengadaan Terpadu</span>
                        </div>
                        <h1 class="text-3xl xl:text-4xl font-black tracking-tight text-white leading-tight">
                            Kontrol Pengadaan Snack & Catering External Secara Transparan.
                        </h1>
                        <p class="text-sm xl:text-base text-slate-300 leading-relaxed">
                            Otomasi pemantauan kuota Purchase Order, penerimaan fisik Surat Jalan (DO), verifikasi 3-Way Match tagihan, hingga pelunasan posting dokumen SAP.
                        </p>
                    </div>

                    <!-- 4 Pillars Feature Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-2">
                        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md space-y-1 hover:bg-white/10 transition">
                            <div class="flex items-center gap-2 text-indigo-300 font-bold text-xs">
                                <flux:icon icon="clipboard-document-list" class="size-4 text-indigo-400" />
                                <span>Batas Plafon PO</span>
                            </div>
                            <p class="text-[11px] text-slate-300 leading-normal">
                                Tracking saldo kuota dan porsi PO berjalan secara real-time.
                            </p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md space-y-1 hover:bg-white/10 transition">
                            <div class="flex items-center gap-2 text-emerald-300 font-bold text-xs">
                                <flux:icon icon="truck" class="size-4 text-emerald-400" />
                                <span>Verifikasi Fisik DO</span>
                            </div>
                            <p class="text-[11px] text-slate-300 leading-normal">
                                Konfirmasi penerimaan makanan dan hitung selisih di lokasi.
                            </p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md space-y-1 hover:bg-white/10 transition">
                            <div class="flex items-center gap-2 text-amber-300 font-bold text-xs">
                                <flux:icon icon="shield-check" class="size-4 text-amber-400" />
                                <span>Validasi 3-Way Match</span>
                            </div>
                            <p class="text-[11px] text-slate-300 leading-normal">
                                Kesesuaian otomatis antara PO, DO fisik, dan invoice rekanan.
                            </p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md space-y-1 hover:bg-white/10 transition">
                            <div class="flex items-center gap-2 text-sky-300 font-bold text-xs">
                                <flux:icon icon="check-badge" class="size-4 text-sky-400" />
                                <span>SAP Posting Integration</span>
                            </div>
                            <p class="text-[11px] text-slate-300 leading-normal">
                                Catat bukti bayar kasir dan nomor dokumen akuntansi resmi.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer Left -->
                <div class="relative z-10 flex items-center justify-between text-xs text-slate-400 border-t border-slate-800/80 pt-4">
                    <span>&copy; {{ date('Y') }} PT Training Center Indonesia</span>
                    <a href="https://github.com/prihandiva/tc-procure" target="_blank" rel="noopener noreferrer" class="hover:text-white transition flex items-center gap-1.5">
                        <flux:icon icon="folder-git-2" class="size-3.5" />
                        <span>GitHub Repository</span>
                    </a>
                </div>
            </div>

            <!-- Right Pane: Authentication Form Card -->
            <div class="flex-1 lg:col-span-6 xl:col-span-5 flex flex-col justify-center items-center px-4 py-8 sm:p-10 lg:p-12 relative min-h-screen">
                <!-- Mobile Top Brand (< lg) -->
                <div class="lg:hidden flex items-center gap-2.5 mb-6">
                    <div class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white shadow-md">
                        <x-app-logo-icon class="size-5 text-white" />
                    </div>
                    <div>
                        <span class="font-black text-lg tracking-tight text-slate-900 dark:text-white">TC-Procure</span>
                        <span class="text-[10px] text-slate-400 block -mt-0.5">Sistem Pengadaan Training Center</span>
                    </div>
                </div>

                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
            </div>
        </div>

        <!-- SweetAlert Style Toast Notification -->
        <x-sweet-alert />

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
