@php
    $images = \App\Support\FrontendContent::homeGalleryImages()->take(10)->values();
    $lightboxImages = $images
        ->map(fn (array $image): array => [
            'url' => $image['url'] ?? '',
            'alt' => $image['alt'] ?? $image['title'] ?? 'Gallery image',
        ])
        ->filter(fn (array $image): bool => $image['url'] !== '')
        ->values();
@endphp

<section
    class="py-20 md:py-24 bg-white overflow-hidden"
    x-data="homeGallery(@js($lightboxImages->all()))"
>
    <div class="max-w-7xl mx-auto px-4 md:px-8 mb-16">
        <h2 class="font-display text-5xl md:text-7xl font-bold text-primary-300 leading-tight">Our Gallery</h2>
    </div>

    <div class="relative w-full mb-16 overflow-hidden">
        <div
            x-ref="slider"
            class="flex gap-5 md:gap-8 overflow-x-auto md:overflow-x-hidden whitespace-nowrap py-8 md:py-10 no-scrollbar"
            @mouseenter="pauseScroll()"
            @mouseleave="resumeScroll()"
            @touchstart.passive="pauseScroll()"
            @touchend="resumeScroll()"
            @touchcancel="resumeScroll()"
        >
            @for($copy = 0; $copy < 2; $copy++)
                @foreach($images as $image)
                    <button
                        type="button"
                        class="inline-block w-[78vw] sm:w-[22rem] md:w-96 h-[360px] sm:h-[420px] md:h-[500px] shrink-0 rounded-[2rem] overflow-hidden transition-all duration-500 hover:shadow-sm hover:-translate-y-2 focus:outline-none focus:ring-4 focus:ring-primary-100/25"
                        @click="openLightbox({{ $loop->index }})"
                        aria-label="Open {{ $image['alt'] }} in gallery"
                    >
                        <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" class="w-full h-full object-cover">
                    </button>
                @endforeach
            @endfor
        </div>

        <div class="hidden md:block absolute inset-y-0 left-0 w-32 bg-gradient-to-r from-white to-transparent pointer-events-none"></div>
        <div class="hidden md:block absolute inset-y-0 right-0 w-32 bg-gradient-to-l from-white to-transparent pointer-events-none"></div>
    </div>

    <div class="flex justify-center md:justify-start container md:px-12">
        <x-button-primary href="/gallery" class="!px-16 !py-5 shadow-xl group">
            <span class="flex items-center gap-2">
                View more
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 transition-transform group-hover:translate-x-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </span>
        </x-button-primary>
    </div>

    <x-gallery.lightbox />
</section>

<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
