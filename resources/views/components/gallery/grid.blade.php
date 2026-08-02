@props(['images' => []])

@php
    $galleryImages = collect($images)->values();
    $lightboxImages = $galleryImages
        ->map(fn (array $image): array => [
            'url' => $image['url'] ?? '',
            'alt' => $image['alt'] ?? $image['title'] ?? 'Gallery image',
        ])
        ->filter(fn (array $image): bool => $image['url'] !== '')
        ->values();
    $galleryGroups = $galleryImages->chunk(6)->values();
    $completeTilePatterns = [
        [
            'col-span-2 row-span-2 lg:col-span-2 lg:row-span-3',
            'col-span-1 row-span-2 lg:col-span-1 lg:row-span-3',
            'col-span-1 row-span-2 lg:col-span-1 lg:row-span-3',
            'col-span-2 row-span-2 lg:col-span-2 lg:row-span-2',
            'col-span-1 row-span-2 lg:col-span-1 lg:row-span-2',
            'col-span-1 row-span-2 lg:col-span-1 lg:row-span-2',
        ],
        [
            'col-span-1 row-span-2 lg:col-span-1 lg:row-span-3',
            'col-span-1 row-span-2 lg:col-span-1 lg:row-span-3',
            'col-span-2 row-span-2 lg:col-span-2 lg:row-span-3',
            'col-span-1 row-span-2 lg:col-span-1 lg:row-span-2',
            'col-span-1 row-span-2 lg:col-span-1 lg:row-span-2',
            'col-span-2 row-span-2 lg:col-span-2 lg:row-span-2',
        ],
    ];
    $closingTilePatterns = [
        1 => [
            'col-span-2 row-span-2 lg:col-span-4 lg:row-span-3',
        ],
        2 => [
            'col-span-1 row-span-2 lg:col-span-2 lg:row-span-3',
            'col-span-1 row-span-2 lg:col-span-2 lg:row-span-3',
        ],
        3 => [
            'col-span-2 row-span-2 lg:col-span-2 lg:row-span-3',
            'col-span-1 row-span-2 lg:col-span-1 lg:row-span-3',
            'col-span-1 row-span-2 lg:col-span-1 lg:row-span-3',
        ],
        4 => [
            'col-span-2 row-span-2 lg:col-span-2 lg:row-span-3',
            'col-span-1 row-span-2 lg:col-span-1 lg:row-span-3',
            'col-span-1 row-span-2 lg:col-span-1 lg:row-span-3',
            'col-span-2 row-span-2 lg:col-span-4 lg:row-span-2',
        ],
        5 => [
            'col-span-2 row-span-2 lg:col-span-2 lg:row-span-3',
            'col-span-1 row-span-2 lg:col-span-1 lg:row-span-3',
            'col-span-1 row-span-2 lg:col-span-1 lg:row-span-3',
            'col-span-1 row-span-2 lg:col-span-2 lg:row-span-2',
            'col-span-1 row-span-2 lg:col-span-2 lg:row-span-2',
        ],
    ];
@endphp

<section
    class="py-10 md:py-16 bg-neutral-50 px-4 overflow-hidden"
    x-data="galleryLightbox(@js($lightboxImages->all()))"
>
    <div class="max-w-7xl mx-auto">

        @if($galleryImages->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-neutral-400">
                <svg class="w-16 h-16 mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 0 1 2.828 0L16 16m-2-2 1.586-1.586a2 2 0 0 1 2.828 0L20 14m-6-6h.01M6 20h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/>
                </svg>
                <p class="text-lg font-semibold font-display">No gallery images yet.</p>
            </div>
        @else
            <div class="space-y-2.5 xs:space-y-3 md:space-y-4 lg:space-y-5">
                @foreach($galleryGroups as $groupIndex => $group)
                    @php
                        $groupCount = $group->count();
                        $patterns = $groupCount === 6
                            ? $completeTilePatterns[$groupIndex % count($completeTilePatterns)]
                            : $closingTilePatterns[$groupCount];
                    @endphp

                    <div class="grid grid-cols-2 lg:grid-cols-4 auto-rows-[6.75rem] xxs:auto-rows-[7.5rem] xs:auto-rows-[8.5rem] sm:auto-rows-[10rem] md:auto-rows-[11rem] lg:auto-rows-[9rem] xl:auto-rows-[10rem] gap-2.5 xs:gap-3 md:gap-4 lg:gap-5">
                        @foreach($group as $image)
                            @php
                                $imageIndex = ($groupIndex * 6) + $loop->index;
                            @endphp

                            <div class="{{ $patterns[$loop->index] }} min-h-0">
                                <button
                                    type="button"
                                    class="group relative block h-full w-full cursor-pointer text-left focus:outline-none focus:ring-4 focus:ring-primary-100/30 focus:ring-offset-2"
                                    @click="openLightbox({{ $imageIndex }})"
                                    aria-label="Open {{ $image['alt'] ?? $image['title'] ?? 'gallery image' }} in gallery"
                                >
                                    <span class="relative block h-full overflow-hidden rounded-[1.25rem] shadow-sm transition-all duration-500 group-hover:shadow-xl group-hover:shadow-primary-300/10">
                                        <img
                                            src="{{ $image['url'] }}"
                                            alt="{{ $image['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.05]"
                                        >

                                        <span class="absolute inset-0 flex items-end bg-gradient-to-t from-primary-300/70 via-primary-300/10 to-transparent p-3 opacity-0 transition-opacity duration-500 group-hover:opacity-100 xs:p-4 md:p-5">
                                            <span class="hidden transform translate-y-4 transition-transform duration-500 group-hover:translate-y-0 xs:block">
                                                <span class="block text-white text-sm font-semibold font-display tracking-wide">{{ $image['alt'] }}</span>
                                                <span class="block text-primary-50/80 text-xs mt-0.5">WestHub Healthcare</span>
                                            </span>

                                            <span class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full border border-white/30 bg-white/20 opacity-0 backdrop-blur-sm transition-opacity delay-100 duration-300 group-hover:opacity-100 md:right-4 md:top-4 md:h-9 md:w-9">
                                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-6-6m2-5a7 7 0 1 1-14 0 7 7 0 0 1 14 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                                </svg>
                                            </span>
                                        </span>
                                    </span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-16 flex justify-center">
            <button
                onclick="window.dispatchEvent(new CustomEvent('open-appointment'))"
                class="inline-flex items-center gap-3 px-10 py-4 bg-primary-100 text-white font-bold rounded-full shadow-xl shadow-primary-100/30 hover:shadow-2xl hover:shadow-primary-300/40 hover:-translate-y-0.5 transition-all duration-300 group"
            >
                <x-icon-calendar class="w-5 h-5 group-hover:rotate-12 transition-transform duration-300" />
                <span class="text-base tracking-tight font-display">Book Appointment</span>
            </button>
        </div>
    </div>

    <x-gallery.lightbox />
</section>
