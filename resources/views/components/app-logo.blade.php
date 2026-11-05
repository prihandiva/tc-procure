@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="TC-Procure" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white shadow-sm shadow-indigo-500/25">
            <x-app-logo-icon class="size-4.5 text-white" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="TC-Procure" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white shadow-sm shadow-indigo-500/25">
            <x-app-logo-icon class="size-4.5 text-white" />
        </x-slot>
    </flux:brand>
@endif
