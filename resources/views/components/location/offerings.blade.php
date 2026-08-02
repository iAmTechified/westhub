@props(['items', 'township'])

<section class="relative min-h-[600px] flex items-center overflow-hidden bg-primary-300 py-14 md:py-[4.5rem]"
    x-data="{
        container: null,
        isDragging: false,
        startX: 0,
        startScrollLeft: 0,
        savedScrollBehavior: '',

        init() {
            this.container = this.$refs.slider;
        },

        leadingPadding() {
            if (!this.container) return 0;

            return parseFloat(window.getComputedStyle(this.container).paddingLeft) || 0;
        },

        slideLeft(child) {
            return Math.max(0, child.offsetLeft - this.leadingPadding());
        },

        currentIndex() {
            const container = this.container;
            const children = Array.from(container.children);
            if (!children.length) return 0;

            const scrollLeft = container.scrollLeft;

            return children.reduce((closestIndex, child, index) => {
                const closestLeft = this.slideLeft(children[closestIndex]);
                const childLeft = this.slideLeft(child);

                return Math.abs(childLeft - scrollLeft) < Math.abs(closestLeft - scrollLeft)
                    ? index
                    : closestIndex;
            }, 0);
        },

        goTo(index) {
            const container = this.container;
            if (!container) return;

            const children = Array.from(container.children);
            if (!children[index]) return;

            container.scrollTo({
                left: this.slideLeft(children[index]),
                behavior: 'smooth'
            });
        },

        next() {
            const children = Array.from(this.container?.children || []);
            if (!children.length) return;

            const current = this.currentIndex();
            this.goTo(current >= children.length - 1 ? 0 : current + 1);
        },

        prev() {
            const children = Array.from(this.container?.children || []);
            if (!children.length) return;

            const current = this.currentIndex();
            this.goTo(current <= 0 ? children.length - 1 : current - 1);
        },

        startDrag(event) {
            if (!this.container || event.button !== 0 || event.pointerType === 'touch') return;

            this.isDragging = true;
            this.startX = event.clientX;
            this.startScrollLeft = this.container.scrollLeft;
            this.savedScrollBehavior = this.container.style.scrollBehavior;
            this.container.style.scrollBehavior = 'auto';
            this.container.classList.add('is-dragging');
            this.container.setPointerCapture?.(event.pointerId);
        },

        drag(event) {
            if (!this.isDragging || !this.container) return;

            event.preventDefault();
            const delta = event.clientX - this.startX;
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
            this.snapToNearest();
        },

        snapToNearest() {
            const container = this.container;
            if (!container) return;

            const children = Array.from(container.children);
            if (!children.length) return;

            this.goTo(this.currentIndex());
        }
    }"
>
    {{-- Background Image --}}
    <div class="absolute inset-0 z-0 opacity-90">
        <img src="{{ asset('assets/images/old-man-standing-gray-backround-with-his-granddaughter 1.webp') }}" 
             alt="Care in {{ $township }}" 
             class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-neutral-600/20"></div> {{-- Subtle overlay --}}
    </div>

    {{-- Content --}}
    <div class="relative z-10 w-full py-4 md:py-6">
        <div
            x-ref="slider"
            class="flex items-stretch gap-4 overflow-x-auto scrollbar-hide snap-x snap-mandatory cursor-grab select-none pb-9 pt-2 [touch-action:pan-y] [&.is-dragging]:cursor-grabbing"
            style="scroll-behavior: smooth; scroll-padding-left: max(1rem, calc((100vw - 1066px) / 2)); padding-left: max(1rem, calc((100vw - 1066px) / 2)); padding-right: 1rem;"
            tabindex="0"
            aria-label="Care offerings in {{ $township }}"
            @pointerdown="startDrag($event)"
            @pointermove="drag($event)"
            @pointerup="endDrag($event)"
            @pointercancel="endDrag($event)"
            @lostpointercapture="endDrag($event)"
            @dragstart.prevent
            @keydown.left.prevent="prev()"
            @keydown.right.prevent="next()"
        >
            @foreach($items as $index => $item)
                <div class="snap-start shrink-0 w-[min(78vw,19rem)] sm:w-[19rem] lg:w-[304px]">
                    <div class="h-full min-h-[410px] lg:min-h-[470px] bg-white/80 backdrop-blur-md p-6 md:p-8 pb-5 rounded-xl shadow-xl flex flex-col border border-white/20">
                        <h3 class="text-[1.7rem] md:text-3xl font-display font-normal text-primary-300 mb-5 md:mb-6 leading-tight">
                            {{ $item['title'] }}
                        </h3>
                        <p class="text-neutral-600 text-[15px] md:text-base leading-relaxed flex-grow">
                            {{ $item['description'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="padding-left: max(1rem, calc((100vw - 1066px) / 2)); padding-right: 1rem;">
            <x-button-primary type="button" onclick="window.dispatchEvent(new CustomEvent('open-appointment'))" class="justify-start">
                <x-icon-calendar class="w-4 h-4 mr-2" />
                Book Appointment
            </x-button-primary>
        </div>
    </div>
</section>
