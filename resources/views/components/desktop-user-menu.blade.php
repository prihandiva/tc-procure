<flux:dropdown position="bottom" align="start" class="w-full">
    <div class="flex items-center gap-3 p-2 rounded-xl bg-slate-100/60 dark:bg-zinc-800/50 hover:bg-slate-200/60 dark:hover:bg-zinc-800 transition cursor-pointer border border-slate-200/60 dark:border-zinc-700/60" data-test="sidebar-menu-button">
        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-tr from-indigo-600 to-indigo-500 font-semibold text-white text-xs shadow-xs">
            {{ auth()->user()->initials() }}
        </div>
        <div class="grid flex-1 min-w-0 text-start leading-tight">
            <span class="truncate text-xs font-semibold text-slate-800 dark:text-zinc-100">{{ auth()->user()->name }}</span>
            <span class="inline-flex items-center gap-1 mt-0.5">
                <span class="inline-block size-1.5 rounded-full {{ auth()->user()->isVendor() ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                <span class="text-[10px] font-medium text-slate-500 dark:text-zinc-400 truncate">{{ auth()->user()->role?->label() ?? 'Pengguna' }}</span>
            </span>
        </div>
        <flux:icon icon="chevrons-up-down" class="size-4 text-slate-400 shrink-0" />
    </div>

    <flux:menu class="w-64">
        <div class="p-2 border-b border-slate-100 dark:border-zinc-800">
            <div class="font-semibold text-xs text-slate-800 dark:text-zinc-100">{{ auth()->user()->name }}</div>
            <div class="text-[11px] text-slate-500 dark:text-zinc-400 truncate">{{ auth()->user()->email }}</div>
            <div class="mt-1.5">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60">
                    {{ auth()->user()->role?->label() }}
                </span>
            </div>
        </div>

        <flux:menu.radio.group>
            <flux:menu.item :href="route('help')" icon="question-mark-circle" wire:navigate>
                {{ __('Bantuan & Panduan') }}
            </flux:menu.item>
            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                {{ __('Settings') }}
            </flux:menu.item>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item
                    as="button"
                    type="submit"
                    icon="arrow-right-start-on-rectangle"
                    class="w-full cursor-pointer text-rose-600 dark:text-rose-400 hover:!bg-rose-50 dark:hover:!bg-rose-950/30"
                    data-test="logout-button"
                >
                    {{ __('Log out') }}
                </flux:menu.item>
            </form>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
