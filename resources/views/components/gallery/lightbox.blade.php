<div
    x-show="lightboxOpen"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="gallery-lightbox-overlay fixed inset-0 z-[9999] overflow-hidden"
    @click.self="closeLightbox()"
    @keydown.escape.window="closeLightbox()"
    @keydown.arrow-left.window="previousImage()"
    @keydown.arrow-right.window="nextImage()"
    role="dialog"
    aria-modal="true"
    :aria-label="currentImage().alt"
>
    <button
        type="button"
        class="gallery-lightbox-close focus:outline-none focus:ring-2 focus:ring-white/70"
        @click="closeLightbox()"
        aria-label="Close gallery lightbox"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
    </button>

    <div class="gallery-lightbox-stage" @click.self="closeLightbox()">
        <div
            x-ref="lightboxTrack"
            class="gallery-lightbox-track"
            :class="trackClass()"
            :style="trackStyle()"
        >
            <template x-for="(image, index) in images" :key="`${image.url}-${index}`">
                <figure
                    data-lightbox-slide
                    class="gallery-lightbox-slide"
                    :class="{ 'is-active': index === currentIndex }"
                >
                    <img :src="image.url" :alt="image.alt" class="gallery-lightbox-image">
                </figure>
            </template>
        </div>
    </div>

    <div class="gallery-lightbox-controls" aria-label="Gallery controls">
        <button
            x-show="hasPrevious()"
            x-transition.opacity.duration.150ms
            type="button"
            class="gallery-lightbox-arrow focus:outline-none focus:ring-2 focus:ring-white/70"
            @click="previousImage()"
            aria-label="Previous gallery image"
        >
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
        </button>

        <span x-show="!hasPrevious()" aria-hidden="true"></span>

        <button
            x-show="hasNext()"
            x-transition.opacity.duration.150ms
            type="button"
            class="gallery-lightbox-arrow focus:outline-none focus:ring-2 focus:ring-white/70"
            @click="nextImage()"
            aria-label="Next gallery image"
        >
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        </button>
    </div>
</div>
