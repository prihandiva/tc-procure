<x-layouts::auth :title="__('Masuk ke Sistem')">
    <div x-data="{
        fillAccount(username, pass = 'password') {
            const userInput = document.querySelector('input[name=email]');
            const passInput = document.querySelector('input[name=password]');
            if (userInput) {
                userInput.value = username;
                userInput.dispatchEvent(new Event('input', { bubbles: true }));
                userInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (passInput) {
                passInput.value = pass;
                passInput.dispatchEvent(new Event('input', { bubbles: true }));
                passInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (window.sweetToast) {
                window.sweetToast({
                    type: 'info',
                    title: 'Akun Terisi Otomatis',
                    message: 'Username `' + username + '` dan kata sandi telah dimasukkan ke formulir.',
                    duration: 2500
                });
            }
        }
    }" class="w-full space-y-6">

        <!-- Card Container -->
        <div class="rounded-3xl bg-white/95 dark:bg-zinc-900/95 border border-slate-200/80 dark:border-zinc-800 p-6 sm:p-8 shadow-xl shadow-slate-200/40 dark:shadow-none space-y-6 backdrop-blur-xl">

            <!-- Title & Branding -->
            <div class="text-center space-y-2">
                <div class="inline-flex size-12 items-center justify-center rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white shadow-md shadow-indigo-600/30 mb-1">
                    <x-app-logo-icon class="size-6 text-white" />
                </div>
                <h2 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                    Masuk ke TC-Procure
                </h2>
                <p class="text-xs text-slate-500 dark:text-zinc-400 max-w-sm mx-auto leading-relaxed">
                    Sistem Kontrak PO, Penerimaan Fisik Surat Jalan (DO), dan Verifikasi Tagihan Training Center.
                </p>
            </div>

            <!-- 1-Click Demo Accounts Quick Selector -->
            <div class="rounded-2xl border border-slate-200/80 bg-slate-50/70 p-3.5 text-xs dark:border-zinc-800 dark:bg-zinc-800/40 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-700 dark:text-zinc-300 text-[11px] uppercase tracking-wider flex items-center gap-1.5">
                        <flux:icon icon="bolt" class="size-3.5 text-amber-500" />
                        Pilih Akun Demo (1-Klik)
                    </span>
                    <span class="text-[10px] text-slate-400">Password: <strong class="text-slate-700 dark:text-zinc-300 font-mono">password</strong></span>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <button
                        type="button"
                        @click="fillAccount('superadmin')"
                        class="group flex flex-col items-center justify-center p-2 rounded-xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-700/80 hover:border-indigo-500 dark:hover:border-indigo-500 shadow-2xs hover:shadow-xs transition text-center cursor-pointer"
                    >
                        <span class="font-black text-xs text-indigo-600 dark:text-indigo-400 group-hover:scale-105 transition transform">superadmin</span>
                        <span class="text-[9px] text-slate-400 font-medium">Super Admin</span>
                    </button>

                    <button
                        type="button"
                        @click="fillAccount('tc')"
                        class="group flex flex-col items-center justify-center p-2 rounded-xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-700/80 hover:border-emerald-500 dark:hover:border-emerald-500 shadow-2xs hover:shadow-xs transition text-center cursor-pointer"
                    >
                        <span class="font-black text-xs text-emerald-600 dark:text-emerald-400 group-hover:scale-105 transition transform">tc</span>
                        <span class="text-[9px] text-slate-400 font-medium">SPV TC</span>
                    </button>

                    <button
                        type="button"
                        @click="fillAccount('vendor')"
                        class="group flex flex-col items-center justify-center p-2 rounded-xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-700/80 hover:border-amber-500 dark:hover:border-amber-500 shadow-2xs hover:shadow-xs transition text-center cursor-pointer"
                    >
                        <span class="font-black text-xs text-amber-600 dark:text-amber-400 group-hover:scale-105 transition transform">vendor</span>
                        <span class="text-[9px] text-slate-400 font-medium">Rekanan</span>
                    </button>
                </div>
            </div>

            <!-- Session Status / Notification -->
            <x-auth-session-status class="text-center" :status="session('status')" />

            <x-passkey-verify />

            <!-- Login Form -->
            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf

                <!-- Username or Email Address -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
                        Username atau Email
                    </label>
                    <div class="relative">
                        <flux:input
                            name="email"
                            :value="old('email')"
                            type="text"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="superadmin / tc / vendor"
                            class="w-full"
                        />
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">
                            Kata Sandi (Password)
                        </label>
                        @if (Route::has('password.request'))
                            <flux:link class="text-xs text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 font-medium" :href="route('password.request')" wire:navigate>
                                Lupa kata sandi?
                            </flux:link>
                        @endif
                    </div>
                    <flux:input
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        viewable
                        class="w-full"
                    />
                </div>

                <!-- Remember Me -->
                <div class="pt-1">
                    <flux:checkbox name="remember" label="Ingat saya di perangkat ini" :checked="old('remember')" />
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        data-test="login-button"
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/30 transition transform active:scale-95 cursor-pointer border border-indigo-400/30"
                    >
                        <span>Masuk ke TC-Procure</span>
                        <flux:icon icon="arrow-right" class="size-4" />
                    </button>
                </div>
            </form>

            <div class="pt-2 text-center text-xs text-slate-500 dark:text-zinc-400 border-t border-slate-100 dark:border-zinc-800">
                <span>Belum memiliki akun rekanan? </span>
                <flux:link :href="route('register')" wire:navigate class="font-bold text-indigo-600 hover:underline dark:text-indigo-400">Daftar Akun Baru</flux:link>
            </div>
        </div>

        <!-- Security Badge Notice -->
        <div class="text-center">
            <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-zinc-500">
                <flux:icon icon="shield-check" class="size-3.5 text-emerald-500" />
                <span>Koneksi aman terenkripsi &bull; Training Center Enterprise System</span>
            </span>
        </div>
    </div>
</x-layouts::auth>
