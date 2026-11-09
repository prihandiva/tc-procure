@props([
    'model' => 'document_file',
    'file' => null,
    'label' => 'Unggah Foto / Berkas Dokumen',
    'help' => 'Ambil foto langsung dengan kamera HP atau pilih file dari galeri/PDF. Maksimal 5 MB.',
    'accept' => 'image/*,application/pdf',
    'id' => 'upload_' . uniqid(),
])

@php
    $isImage = false;
    $previewUrl = null;
    $fileName = null;
    $fileSize = null;

    if ($file) {
        try {
            $fileName = method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : 'Dokumen';
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
            if ($isImage && method_exists($file, 'temporaryUrl')) {
                $previewUrl = $file->temporaryUrl();
            }
            if (method_exists($file, 'getSize')) {
                $bytes = $file->getSize();
                $fileSize = $bytes >= 1048576 ? number_format($bytes / 1048576, 2) . ' MB' : number_format($bytes / 1024, 1) . ' KB';
            }
        } catch (\Throwable $e) {
            $isImage = false;
        }
    }
@endphp

<div x-data="{
    lightboxOpen: false,
    lightboxImage: '',
    openLightbox(src) {
        this.lightboxImage = src;
        this.lightboxOpen = true;
    }
}" class="space-y-2">
    <div class="flex items-center justify-between">
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">
            {{ $label }}
        </label>
        <span class="text-[10px] text-slate-400">PDF, JPG, PNG &bull; Maks 5MB</span>
    </div>

    <!-- Hidden Native Inputs -->
    <!-- Input 1: Direct Camera (capture=environment for phone camera) -->
    <input
        type="file"
        id="{{ $id }}_camera"
        wire:model="{{ $model }}"
        accept="image/*"
        capture="environment"
        class="hidden"
    >

    <!-- Input 2: Gallery / File Picker (supports PDF & images) -->
    <input
        type="file"
        id="{{ $id }}_file"
        wire:model="{{ $model }}"
        accept="{{ $accept }}"
        class="hidden"
    >

    <!-- Upload Area State -->
    @if ($file)
        <!-- File Selected Preview Card -->
        <div class="rounded-2xl border border-slate-200/90 dark:border-zinc-700/80 bg-white dark:bg-zinc-900 p-4 shadow-xs space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-2.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                    Berkas Terpilih (Siap Diunggah)
                </span>

                <button
                    type="button"
                    wire:click="$set('{{ $model }}', null)"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 cursor-pointer"
                >
                    <flux:icon icon="trash" class="size-3.5" />
                    <span>Hapus / Ganti</span>
                </button>
            </div>

            <!-- Image Thumbnail Preview or PDF Document Card -->
            @if ($isImage && $previewUrl)
                <div class="relative group rounded-xl overflow-hidden border border-slate-200 dark:border-zinc-800 bg-slate-950/5 dark:bg-zinc-950/50 flex flex-col sm:flex-row items-center gap-4 p-3">
                    <div class="relative size-28 sm:size-32 shrink-0 rounded-xl overflow-hidden shadow-xs cursor-pointer border border-slate-200 dark:border-zinc-700" @click="openLightbox('{{ $previewUrl }}')">
                        <img src="{{ $previewUrl }}" alt="Preview Berkas" class="size-full object-cover group-hover:scale-105 transition duration-300">
                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white">
                            <flux:icon icon="magnifying-glass-plus" class="size-6 drop-shadow" />
                        </div>
                    </div>

                    <div class="space-y-1.5 flex-1 min-w-0 text-center sm:text-left">
                        <div class="font-bold text-sm text-slate-900 dark:text-white truncate">
                            {{ $fileName }}
                        </div>
                        <div class="text-xs text-slate-500 dark:text-zinc-400 flex flex-wrap items-center justify-center sm:justify-start gap-2">
                            <span>Format: <strong class="uppercase text-slate-700 dark:text-zinc-300">{{ $ext }}</strong></span>
                            @if ($fileSize)
                                <span class="text-slate-300 dark:text-zinc-700">&bull;</span>
                                <span>Ukuran: <strong class="text-slate-700 dark:text-zinc-300">{{ $fileSize }}</strong></span>
                            @endif
                        </div>
                        <div class="pt-1 flex flex-wrap items-center justify-center sm:justify-start gap-2">
                            <button
                                type="button"
                                @click="openLightbox('{{ $previewUrl }}')"
                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60 hover:bg-indigo-100 transition"
                            >
                                <flux:icon icon="eye" class="size-3.5" />
                                <span>Pratinjau Foto</span>
                            </button>
                            <label
                                for="{{ $id }}_camera"
                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300 hover:bg-slate-200 transition cursor-pointer"
                            >
                                <flux:icon icon="camera" class="size-3.5" />
                                <span>Foto Ulang</span>
                            </label>
                        </div>
                    </div>
                </div>
            @else
                <!-- PDF / Generic File Card -->
                <div class="p-4 rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/40 flex items-center justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/60 font-black text-xs">
                            PDF
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-sm text-slate-900 dark:text-white truncate">
                                {{ $fileName }}
                            </div>
                            <div class="text-xs text-slate-400">
                                {{ $fileSize ?? 'Dokumen PDF' }} &bull; Siap dilampirkan
                            </div>
                        </div>
                    </div>
                    <label
                        for="{{ $id }}_file"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white text-slate-700 dark:bg-zinc-800 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700 hover:bg-slate-100 transition cursor-pointer shrink-0"
                    >
                        Ganti File
                    </label>
                </div>
            @endif
        </div>
    @else
        <!-- No File Selected Dropzone with Camera Menu & File Picker -->
        <div class="rounded-2xl border-2 border-dashed border-slate-300 dark:border-zinc-700 hover:border-indigo-400 dark:hover:border-indigo-500 bg-slate-50/50 dark:bg-zinc-900/50 p-5 sm:p-6 transition text-center space-y-4">
            
            <!-- Uploading Spinner Animation -->
            <div wire:loading wire:target="{{ $model }}" class="py-4 space-y-2">
                <div class="flex items-center justify-center gap-2 text-indigo-600 dark:text-indigo-400 font-bold text-sm">
                    <svg class="size-5 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Mengunggah berkas... Mohon tunggu</span>
                </div>
                <p class="text-xs text-slate-400">Memproses dan membaca file dokumen ke server</p>
            </div>

            <!-- Normal Upload Buttons -->
            <div wire:loading.remove wire:target="{{ $model }}" class="space-y-4">
                <div class="flex items-center justify-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60">
                        <flux:icon icon="camera" class="size-5.5" />
                    </div>
                    <div class="flex size-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700">
                        <flux:icon icon="document-arrow-up" class="size-5.5" />
                    </div>
                </div>

                <div class="space-y-1">
                    <div class="font-bold text-sm text-slate-900 dark:text-white">
                        Ambil Foto Langsung atau Unggah Berkas
                    </div>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 max-w-sm mx-auto">
                        {{ $help }}
                    </p>
                </div>

                <!-- Action Button Group -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-1">
                    <!-- Button 1: Camera -->
                    <label
                        for="{{ $id }}_camera"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-600 text-white font-bold text-xs shadow-md shadow-indigo-600/25 transition transform active:scale-95 cursor-pointer border border-indigo-400/30"
                    >
                        <flux:icon icon="camera" class="size-4" />
                        <span>Buka Kamera (Foto Fisik)</span>
                    </label>

                    <!-- Button 2: File Browser -->
                    <label
                        for="{{ $id }}_file"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700 font-bold text-xs border border-slate-300 dark:border-zinc-700 shadow-2xs transition cursor-pointer"
                    >
                        <flux:icon icon="folder-open" class="size-4 text-slate-400" />
                        <span>Pilih dari Galeri / PDF</span>
                    </label>
                </div>
            </div>
        </div>
    @endif

    @error($model)
        <span class="text-xs text-rose-600 dark:text-rose-400 font-medium block mt-1">
            {{ $message }}
        </span>
    @enderror

    <!-- Lightbox Fullscreen Modal -->
    <template x-teleport="body">
        <div
            x-show="lightboxOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
            @keydown.escape.window="lightboxOpen = false"
        >
            <div class="relative max-w-4xl max-h-[90vh] flex flex-col items-center">
                <button
                    type="button"
                    @click="lightboxOpen = false"
                    class="absolute -top-12 right-0 flex size-10 items-center justify-center rounded-full bg-white/20 hover:bg-white/30 text-white backdrop-blur-md transition cursor-pointer"
                    aria-label="Tutup"
                >
                    <flux:icon icon="x-mark" class="size-5" />
                </button>
                <img
                    :src="lightboxImage"
                    alt="Tampilan Penuh"
                    class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl border border-white/10"
                >
            </div>
        </div>
    </template>
</div>
