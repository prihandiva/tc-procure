<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="!p-3 sm:!p-6 lg:!p-8 min-w-0 w-full max-w-full overflow-x-clip">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
