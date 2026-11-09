<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-50/70 text-slate-900 dark:bg-zinc-950 dark:text-zinc-100 antialiased selection:bg-indigo-500 selection:text-white font-sans">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-slate-200/80 bg-white dark:border-zinc-800 dark:bg-zinc-900 shadow-xs">
            <flux:sidebar.header class="pb-2">
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <div class="px-3 pb-3">
                <div class="flex items-center justify-between rounded-lg bg-indigo-50/80 px-2.5 py-1.5 text-xs dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50">
                    <span class="font-medium text-indigo-900 dark:text-indigo-200 text-[11px]">Training Center</span>
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-indigo-700 dark:text-indigo-300">
                        <span class="size-1.5 rounded-full bg-emerald-500"></span> Live
                    </span>
                </div>
            </div>

            <flux:sidebar.nav>
                <flux:sidebar.group heading="Operasional & Transaksi" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        Dashboard
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="clipboard-document-list" :href="route('purchase-orders.index')" :current="request()->routeIs('purchase-orders.*')" wire:navigate>
                        Purchase Order (PO)
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="truck" :href="route('deliveries.index')" :current="request()->routeIs('deliveries.*')" wire:navigate>
                        Pengiriman (DO)
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="document-text" :href="route('invoices.index')" :current="request()->routeIs('invoices.*')" wire:navigate>
                        Tagihan (Invoice)
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="banknotes" :href="route('payment-requests.index')" :current="request()->routeIs('payment-requests.*')" wire:navigate>
                        Payment Request (PR)
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="check-badge" :href="route('postings.index')" :current="request()->routeIs('postings.*')" wire:navigate>
                        Dokumen Posting SAP
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if (auth()->user()->isSuperAdmin() || auth()->user()->isSpv())
                    <flux:sidebar.group heading="Master Data & Konfigurasi" class="grid mt-2">
                        <flux:sidebar.item icon="building-storefront" :href="route('vendors.index')" :current="request()->routeIs('vendors.*')" wire:navigate>
                            Master Vendor
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="cube" :href="route('items.index')" :current="request()->routeIs('items.*')" wire:navigate>
                            Master Item Snack
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="queue-list" :href="route('payment-stages.index')" :current="request()->routeIs('payment-stages.*')" wire:navigate>
                            Tahapan Pembayaran
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <div class="px-2 pt-2">
                <a href="https://github.com/prihandiva/tc-procure" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-600 dark:text-zinc-400 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition">
                    <span class="flex items-center gap-2">
                        <flux:icon icon="folder-git-2" class="size-4 text-slate-500" />
                        GitHub Repo
                    </span>
                    <span class="text-[10px] text-slate-400">v1.0</span>
                </a>
            </div>

            <div class="p-2">
                <x-desktop-user-menu :name="auth()->user()->name" />
            </div>
        </flux:sidebar>

        <!-- Mobile Header (Sticky & responsive) -->
        <flux:header class="lg:hidden border-b border-slate-200/80 bg-white/95 dark:border-zinc-800 dark:bg-zinc-900/95 backdrop-blur-md sticky top-0 z-40 px-3 sm:px-4 py-2 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2">
                    <div class="flex size-7 items-center justify-center rounded-lg bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white shadow-xs">
                        <x-app-logo-icon class="size-3.5 text-white" />
                    </div>
                    <span class="font-extrabold text-sm tracking-tight text-slate-900 dark:text-white">TC-Procure</span>
                </a>
            </div>

            <flux:dropdown position="top" align="end">
                <div class="flex items-center gap-1.5 cursor-pointer p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition">
                    <div class="flex size-7 items-center justify-center rounded-lg bg-indigo-600 font-bold text-white text-xs">
                        {{ auth()->user()->initials() }}
                    </div>
                    <flux:icon icon="chevron-down" class="size-3 text-slate-400" />
                </div>

                <flux:menu class="w-56">
                    <div class="p-2 border-b border-slate-100 dark:border-zinc-800">
                        <div class="font-bold text-xs text-slate-900 dark:text-white">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</div>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                                {{ auth()->user()->role?->label() }}
                            </span>
                        </div>
                    </div>

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer text-rose-600 hover:!bg-rose-50 dark:hover:!bg-rose-950/30"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

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
