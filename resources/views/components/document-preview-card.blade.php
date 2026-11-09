@props([
    'documents' => [],
    'title' => 'Berkas Dokumen Lampiran',
])

<div x-data="{
    lightboxOpen: false,
    lightboxImage: '',
    lightboxTitle: '',
    openLightbox(src, title) {
        this.lightboxImage = src;
        this.lightboxTitle = title;
        this.lightboxOpen = true;
    }
}" class="space-y-3">
    @forelse ($documents as $doc)
        @php
            $isImg = $doc->isImage();
            $fileUrl = route('documents.show', $doc);
            $downloadUrl = route('documents.show', ['document' => $doc, 'download' => 1]);
        @endphp

        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-zinc-800/50 border border-slate-200/80 dark:border-zinc-700/60 space-y-3">
            @if ($isImg)
                <!-- Image Document with Real Thumbnail Preview -->
                <div class="relative group rounded-xl overflow-hidden border border-slate-200 dark:border-zinc-700 bg-slate-900/5 dark:bg-zinc-950">
                    <img
                        src="{{ $fileUrl }}"
                        alt="{{ $doc->original_name }}"
                        class="w-full h-40 sm:h-48 object-cover group-hover:scale-105 transition duration-300"
                        loading="lazy"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent flex items-end justify-between p-3">
                        <span class="text-[11px] font-bold text-white truncate max-w-[200px] drop-shadow">
                            {{ $doc->original_name }}
                        </span>
                        <span class="text-[10px] text-white/80 font-mono">
                            {{ $doc->formatted_size }}
                        </span>
                    </div>

                    <!-- Overlay Action Buttons on Hover -->
                    <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2 backdrop-blur-xs">
                        <button
                            type="button"
                            @click="openLightbox('{{ $fileUrl }}', '{{ addslashes($doc->original_name) }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/90 hover:bg-white text-slate-900 text-xs font-bold shadow-md transition transform active:scale-95 cursor-pointer"
                        >
                            <flux:icon icon="magnifying-glass-plus" class="size-3.5" />
                            <span>Perbesar Foto</span>
                        </button>
                        <a
                            href="{{ $downloadUrl }}"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-900/80 hover:bg-slate-900 text-white text-xs font-semibold backdrop-blur-md transition shadow-md"
                        >
                            <flux:icon icon="arrow-down-tray" class="size-3.5" />
                            <span>Unduh</span>
                        </a>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-0.5">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                            FOTO / GAMBAR
                        </span>
                        <span class="text-[11px] text-slate-400">
                            {{ $doc->created_at?->translatedFormat('d M Y H:i') }}
                        </span>
                    </div>

                    <button
                        type="button"
                        @click="openLightbox('{{ $fileUrl }}', '{{ addslashes($doc->original_name) }}')"
                        class="text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 hover:underline flex items-center gap-1 cursor-pointer"
                    >
                        <flux:icon icon="eye" class="size-3" />
                        <span>Lihat Penuh</span>
                    </button>
                </div>
            @else
                <!-- PDF / Other File Card -->
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/60 font-black text-xs">
                            PDF
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-xs text-slate-900 dark:text-white truncate">
                                {{ $doc->original_name }}
                            </div>
                            <div class="text-[11px] text-slate-400 flex items-center gap-1.5">
                                <span>{{ $doc->formatted_size }}</span>
                                <span>&bull;</span>
                                <span>{{ $doc->created_at?->translatedFormat('d M Y H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                        <a
                            href="{{ $fileUrl }}"
                            target="_blank"
                            class="px-2.5 py-1.5 text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 rounded-lg transition"
                        >
                            Buka
                        </a>
                        <a
                            href="{{ $downloadUrl }}"
                            class="px-2.5 py-1.5 text-xs font-semibold text-slate-600 bg-white hover:bg-slate-100 dark:bg-zinc-800 dark:text-zinc-300 rounded-lg border border-slate-200 dark:border-zinc-700 transition"
                        >
                            Unduh
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @empty
        <div class="text-xs text-slate-400 text-center py-6 border border-dashed border-slate-200 dark:border-zinc-800 rounded-xl">
            Tidak ada dokumen fisik dilampirkan.
        </div>
    @endforelse

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
            class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md"
            @keydown.escape.window="lightboxOpen = false"
        >
            <div class="relative max-w-5xl max-h-[92vh] flex flex-col items-center">
                <div class="w-full flex items-center justify-between pb-3 text-white">
                    <span class="font-bold text-sm truncate pr-4" x-text="lightboxTitle"></span>
                    <button
                        type="button"
                        @click="lightboxOpen = false"
                        class="flex size-9 items-center justify-center rounded-xl bg-white/20 hover:bg-white/30 text-white backdrop-blur-md transition cursor-pointer"
                        aria-label="Tutup"
                    >
                        <flux:icon icon="x-mark" class="size-5" />
                    </button>
                </div>
                <img
                    :src="lightboxImage"
                    :alt="lightboxTitle"
                    class="max-w-full max-h-[82vh] object-contain rounded-2xl shadow-2xl border border-white/10"
                >
            </div>
        </div>
    </template>
</div>
