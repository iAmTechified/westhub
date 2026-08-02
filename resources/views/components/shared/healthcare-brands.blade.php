@php
    $brandDir = public_path('assets/images/healthcare brands');
    $brandFiles = \Illuminate\Support\Facades\File::exists($brandDir)
        ? \Illuminate\Support\Facades\File::files($brandDir)
        : [];

    usort($brandFiles, function ($a, $b) {
        return strnatcasecmp($a->getFilename(), $b->getFilename());
    });

    $brands = collect($brandFiles)->map(function ($file, $index) {
        return [
            'name' => 'Healthcare Brand ' . ($index + 1),
            'src' => asset('assets/images/healthcare brands/' . $file->getFilename()),
        ];
    })->values();
@endphp

<section class="py-16 md:py-20 bg-gradient-brand relative overflow-hidden"
    x-data="{
        brands: @js($brands->all()),
        activeIndex: 0,
        cardsPerView: 4,
        maxIndex: 0,
        autoSlideTimer: null,
        autoSlideMs: 3400,
        resizeHandler: null,
        init() {
            this.setCardsPerView();
            this.startAutoSlide();
            this.resizeHandler = () => {
                this.setCardsPerView();
                this.startAutoSlide();
            };
            window.addEventListener('resize', this.resizeHandler);
        },
        destroy() {
            this.stopAutoSlide();
            window.removeEventListener('resize', this.resizeHandler);
        },
        setCardsPerView() {
            if (window.innerWidth < 640) {
                this.cardsPerView = 1;
            } else if (window.innerWidth < 1024) {
                this.cardsPerView = 2;
            } else {
                this.cardsPerView = 4;
            }

            this.maxIndex = Math.max(0, this.brands.length - this.cardsPerView);
            this.activeIndex = Math.min(this.activeIndex, this.maxIndex);
        },
        startAutoSlide() {
            this.stopAutoSlide();
            if (!this.brands.length || this.maxIndex === 0) return;

            this.autoSlideTimer = window.setInterval(() => this.next(false), this.autoSlideMs);
        },
        stopAutoSlide() {
            if (!this.autoSlideTimer) return;

            window.clearInterval(this.autoSlideTimer);
            this.autoSlideTimer = null;
        },
        next(shouldResetTimer = true) {
            if (!this.brands.length || this.maxIndex === 0) return;

            this.activeIndex = this.activeIndex >= this.maxIndex ? 0 : this.activeIndex + 1;
            if (shouldResetTimer) this.startAutoSlide();
        },
        prev(shouldResetTimer = true) {
            if (!this.brands.length || this.maxIndex === 0) return;

            this.activeIndex = this.activeIndex <= 0 ? this.maxIndex : this.activeIndex - 1;
            if (shouldResetTimer) this.startAutoSlide();
        },
        trackStyle() {
            if (!this.$refs.track) return '';
            const firstCard = this.$refs.track.children[0];
            if (!firstCard) return '';

            const cardWidth = firstCard.getBoundingClientRect().width;
            const trackStyles = window.getComputedStyle(this.$refs.track);
            const gap = parseFloat(trackStyles.columnGap || trackStyles.gap || 0);
            const offset = this.activeIndex * (cardWidth + gap);

            return `transform: translateX(-${offset}px);`;
        }
    }"
>
    <div class="max-w-7xl mx-auto px-4 md:px-8 relative z-10 text-center">
        <h2 class="text-3xl md:text-4xl font-bold font-display text-white mb-3 tracking-tight">
            We Accept Clients From:
        </h2>
        <p class="text-white/90 mb-10 md:mb-12 text-sm md:text-lg font-medium max-w-5xl mx-auto">
            Major Health Insurance companies, Government (State & Federal) Health Agencies and Private Pay
        </p>

        <div class="relative px-2 md:px-12">
            <button
                x-show="activeIndex > 0"
                x-transition:enter="transition-opacity duration-300 ease-out"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-200 ease-in"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="prev()"
                class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-2 z-20 p-2 text-white/60 hover:text-white transition-colors duration-200 brightness-0 invert"
                aria-label="Previous healthcare brands"
            >
                <img src="{{ asset('assets/icons/Property 1=Back.svg') }}" alt="Back" class="w-5 md:w-10 h-5 md:h-10 opacity-70 hover:opacity-100 transition-opacity duration-200">
            </button>

            <div class="overflow-hidden">
                <div
                    x-ref="track"
                    class="flex items-center gap-2 md:gap-5 transition-transform duration-700 ease-[cubic-bezier(0.25,1.35,0.35,1)] will-change-transform"
                    :style="trackStyle()"
                >
                @foreach($brands as $brand)
                    <div class="shrink-0 w-[calc(26%-10px)] md:w-[calc(24%-10px)] lg:w-[calc(25%-15px)]">
                        <x-sub.brand-logo :src="$brand['src']" :alt="$brand['name']" />
                    </div>
                @endforeach
                </div>
            </div>

            <button
                x-show="maxIndex > 0"
                x-transition:enter="transition-opacity duration-300 ease-out"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-200 ease-in"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="next()"
                class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-2 z-20 p-2 text-white/60 hover:text-white transition-colors duration-200 brightness-0 invert"
                aria-label="Next healthcare brands"
            >
                <img src="{{ asset('assets/icons/Property 1=Next.svg') }}" alt="Next" class="w-5 md:w-10 h-5 md:h-10 opacity-70 hover:opacity-100 transition-opacity duration-200">
            </button>
        </div>
    </div>
    
    <!-- Design Sheen -->
    <div class="absolute inset-0 bg-black/5 pointer-events-none"></div>
</section>
