<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Masuk ke Akun')" :description="__('Masukkan username atau email dan kata sandi Anda')" />

        <!-- Quick Demo Credentials Hint -->
        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-xs text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-300">
            <div class="font-bold text-zinc-900 dark:text-white mb-1.5 flex items-center justify-between">
                <span>Akun Default:</span>
                <span class="text-[11px] font-normal text-zinc-500">Password: <strong class="text-zinc-800 dark:text-zinc-200">password</strong></span>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center text-[11px]">
                <div class="rounded bg-white p-1.5 shadow-xs dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                    <span class="font-bold text-indigo-600 dark:text-indigo-400 block">superadmin</span>
                    <span class="text-[10px] text-zinc-500">Superadmin</span>
                </div>
                <div class="rounded bg-white p-1.5 shadow-xs dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                    <span class="font-bold text-emerald-600 dark:text-emerald-400 block">tc</span>
                    <span class="text-[10px] text-zinc-500">SPV Training Center</span>
                </div>
                <div class="rounded bg-white p-1.5 shadow-xs dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                    <span class="font-bold text-amber-600 dark:text-amber-400 block">vendor</span>
                    <span class="text-[10px] text-zinc-500">CV Yulia Catering</span>
                </div>
            </div>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Username or Email Address -->
            <flux:input
                name="email"
                label="Username atau Email"
                :value="old('email')"
                type="text"
                required
                autofocus
                autocomplete="username"
                placeholder="superadmin / tc / vendor"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Don\'t have an account?') }}</span>
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
