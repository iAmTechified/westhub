@php($testimonials = \App\Support\FrontendContent::testimonials())

<section class="py-4 md:py-5 bg-white overflow-hidden"
    x-data="{
        container: null,
        autoPlayInterval: null,
        resizeHandler: null,
        isDragging: false,
        hasMoved: false,
        startX: 0,
        startScrollLeft: 0,
        savedScrollBehavior: '',
        savedScrollSnapType: '',
        autoPlayDelay: 3000,

        init() {
            this.container = this.$refs.container;
            this.setupAutoPlay();
            this.resizeHandler = () => {
                this.snapToNearest('auto');
                this.resetAutoPlay();
            };
            window.addEventListener('resize', this.resizeHandler);
        },

        destroy() {
            this.stopAutoPlay();

            if (this.resizeHandler) {
                window.removeEventListener('resize', this.resizeHandler);
            }
        },

        next() {
            const children = this.slides();
            if (!children.length) return;

            const nextIndex = (this.currentIndex() + 1) % children.length;
            this.scrollToSlide(nextIndex);
        },

        prev() {
            const children = this.slides();
            if (!children.length) return;

            const currentIndex = this.currentIndex();
            const previousIndex = currentIndex <= 0 ? children.length - 1 : currentIndex - 1;
            this.scrollToSlide(previousIndex);
        },

        manualNext() {
            this.stopAutoPlay();
            this.next();
            this.resetAutoPlay();
        },

        manualPrev() {
            this.stopAutoPlay();
            this.prev();
            this.resetAutoPlay();
        },

        startDrag(event) {
            if (!this.container || event.button !== 0 || event.isPrimary === false) return;

            this.stopAutoPlay();
            this.isDragging = true;
            this.hasMoved = false;
            this.startX = event.clientX;
            this.startScrollLeft = this.container.scrollLeft;
            this.savedScrollBehavior = this.container.style.scrollBehavior;
            this.savedScrollSnapType = this.container.style.scrollSnapType;
            this.container.style.scrollBehavior = 'auto';
            this.container.style.scrollSnapType = 'none';
            this.container.classList.add('is-dragging');
            this.container.setPointerCapture?.(event.pointerId);
        },

        drag(event) {
            if (!this.isDragging || !this.container) return;

            event.preventDefault();
            const delta = event.clientX - this.startX;
            if (Math.abs(delta) > 3) {
                this.hasMoved = true;
            }
            this.container.scrollLeft = this.startScrollLeft - delta;
        },

        endDrag(event) {
            if (!this.isDragging || !this.container) return;

            this.isDragging = false;
            this.container.classList.remove('is-dragging');
            if (this.container.hasPointerCapture?.(event.pointerId)) {
                this.container.releasePointerCapture(event.pointerId);
            }
            this.container.style.scrollBehavior = this.savedScrollBehavior || 'smooth';
            this.container.style.scrollSnapType = this.savedScrollSnapType;
            this.snapToNearest();
            this.resetAutoPlay();
        },

        setupAutoPlay() {
            this.stopAutoPlay();

            if (!this.container || !this.slides().length) return;

            this.autoPlayInterval = window.setInterval(() => {
                if (!this.isDragging) {
                    this.next();
                }
            }, this.autoPlayDelay);
        },

        resetAutoPlay() {
            this.stopAutoPlay();
            this.setupAutoPlay();
        },

        stopAutoPlay() {
            if (this.autoPlayInterval) {
                window.clearInterval(this.autoPlayInterval);
                this.autoPlayInterval = null;
            }
        },

        snapToNearest(behavior = 'smooth') {
            const index = this.currentIndex();
            if (index < 0) return;

            this.scrollToSlide(index, behavior);
        },

        slides() {
            return this.container ? Array.from(this.container.children) : [];
        },

        currentIndex() {
            const container = this.container;
            const children = this.slides();
            if (!container || !children.length) return -1;

            const currentLeft = container.scrollLeft;
            const maxScroll = container.scrollWidth - container.clientWidth;

            return children.reduce((closestIndex, child, index) => {
                if (Math.abs(currentLeft - maxScroll) < 2 && index === children.length - 1) {
                    return index;
                }

                const closestDistance = Math.abs(this.slideTarget(children[closestIndex]) - currentLeft);
                const distance = Math.abs(this.slideTarget(child) - currentLeft);

                return distance < closestDistance ? index : closestIndex;
            }, 0);
        },

        slideTarget(slide) {
            if (!this.container || !slide) return 0;

            const centeredOffset = slide.offsetLeft - ((this.container.clientWidth - slide.offsetWidth) / 2);
            const maxScroll = this.container.scrollWidth - this.container.clientWidth;

            return Math.max(0, Math.min(centeredOffset, maxScroll));
        },

        scrollToSlide(index, behavior = 'smooth') {
            const container = this.container;
            const children = this.slides();
            if (!container || !children[index]) return;

            container.scrollTo({
                left: this.slideTarget(children[index]),
                behavior
            });
        }
    }"
>
    <div class="mx-auto">
        <div class="mb-5 md:mb-6 max-w-7xl mx-auto px-8">
            <h2 class="font-display text-2xl md:text-4xl font-bold leading-none text-primary-300">Testimonials</h2>
        </div>

        <div x-ref="container"
             class="grid grid-flow-col auto-cols-[90%] md:auto-cols-[28%] overflow-x-auto snap-x snap-mandatory scrollbar-hide gap-6 pb-7 pt-0.5 pl-6 cursor-grab select-none transition-all [touch-action:pan-y] [&.is-dragging]:cursor-grabbing"
             style="scroll-behavior: smooth;"
             tabindex="0"
             aria-label="Testimonials"
             @pointerdown="startDrag($event)"
             @pointermove="drag($event)"
             @pointerup="endDrag($event)"
             @pointercancel="endDrag($event)"
             @pointerup.window="endDrag($event)"
             @pointercancel.window="endDrag($event)"
             @lostpointercapture="endDrag($event)"
             @dragstart.prevent
             @keydown.left.prevent="manualPrev()"
             @keydown.right.prevent="manualNext()"
        >
            @foreach($testimonials as $testimonial)
                <div class="snap-center shrink-0 min-h-[400px]">
                    <div class="h-full">
                        <x-sub.testimonial-card
                            :avatar="$testimonial['avatar']"
                            :name="$testimonial['name']"
                            :role="$testimonial['role']"
                            :text="$testimonial['text']"
                            :stars="$testimonial['stars']"
                        />
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
