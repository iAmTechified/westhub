<section x-data="{ 
    activeSlide: 0, 
    slides: [
        { 
            image: '/assets/images/Slide Image 1.webp', 
            label: 'Give Your Loved Ones',
            labelClass: 'text-2xl md:text-4xl lg:text-5xl font-light',
            heading: 'Compassionate Care for an Exceptional Result.',
            subheading: 'At West Hub Healthcare, we believe that healthcare is more than just medicine; it\'s about genuine human connection. Our team of dedicated professionals is committed to providing compassionate care that addresses not only your physical needs but also your emotional and mental well-being.',
            position: 'right center'
        },
        { 
            image: '/assets/images/Slide Image 2.webp', 
            label: 'WestHub Healthcare',
            labelClass: 'text-base md:text-xl font-medium',
            heading: 'Empowering Independence Through Personalized Care.',
            subheading: 'Our dedicated team provides the support you need to maintain your lifestyle and dignity. Quality care tailored to your unique rhythm of life.',
            position: '75% center'
        },
        { 
            image: '/assets/images/Slide Image 3.webp', 
            label: 'WestHub Healthcare',
            labelClass: 'text-base md:text-xl font-medium',
            heading: 'Healthcare Built on Genuine Human Connection.',
            subheading: 'We believe healing starts with a smile. Beyond medical needs, we focus on the emotional and mental well-being of every patient we serve.',
            position: '0% center'
        }
    ],
    next() { this.activeSlide = (this.activeSlide + 1) % this.slides.length },
    prev() { this.activeSlide = (this.activeSlide - 1 + this.slides.length) % this.slides.length },
    imagePosition(slide) { return window.innerWidth < 768 ? 'center center' : slide.position },
    init() { setInterval(() => this.next(), 6000) }
}" class="relative min-h-[640px] md:min-h-[700px] lg:h-[650px] overflow-hidden bg-primary-300">
    
    <!-- Slides -->
    <template x-for="(slide, index) in slides" :key="index">
        <div x-show="activeSlide === index" 
             class="absolute inset-0">
            
            <!-- Background Image -->
            <img :src="slide.image" 
                 :alt="slide.heading" 
                 :style="{ objectPosition: imagePosition(slide) }"
                 class="absolute inset-0 object-cover w-full h-full z-0">
            
            <!-- Mobile should be fully overlaid, desktop keeps gradient fade -->
            <div class="absolute inset-0 bg-primary-300/10 bg-gradient-teal z-10"></div>

            <!-- Content Area -->
            <div class="relative z-20 flex flex-col items-start justify-center h-full max-w-7xl mx-auto px-5 md:px-12 py-24 md:py-32 lg:py-24">
                <div class="max-w-md lg:max-w-3xl w-full text-white">
                    <span :class="slide.labelClass" 
                          class="inline-block mb-2 tracking-tight text-white drop-shadow-sm" 
                          x-text="slide.label"></span>
                    
                    <h1 class="font-display text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold leading-[1.06] mb-4 md:mb-6 drop-shadow-md" x-text="slide.heading"></h1>
                    
                    <!-- Frosted Glass (Charcoal Text) -->
                    <div class="inline-block w-full max-w-2xl p-3 sm:p-5 lg:p-8 bg-white/30 backdrop-blur-md rounded-[1.25rem] sm:rounded-[2rem] border border-white/40 mb-5 md:mb-8 shadow-xl">
                        <p class="text-neutral-600 text-xs sm:text-sm md:text-base lg:text-lg font-regular leading-relaxed" x-text="slide.subheading"></p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 sm:gap-5">
                        <button type="button"
                                onclick="window.dispatchEvent(new CustomEvent('open-appointment'))"
                                class="inline-flex w-full sm:w-auto justify-center items-center gap-2 sm:gap-4 px-4 sm:px-10 py-3 sm:py-4 bg-white text-primary-200 font-bold text-sm sm:text-lg rounded-full shadow-2xl hover:bg-neutral-50 transition-all active:scale-95">
                            <x-icon-calendar class="w-5 h-5 sm:w-6 sm:h-6 text-primary-200"/>
                            Book Appointment
                        </button>
                        
                        <a href="{{ \App\Support\SiteSettings::contactMailto() }}"
                           class="inline-flex w-full sm:w-auto justify-center items-center gap-2 sm:gap-4 px-4 sm:px-10 py-3 sm:py-4 bg-white/20 backdrop-blur-md border-2 border-white/50 text-white font-bold text-sm sm:text-lg rounded-full shadow-2xl hover:bg-white/40 transition-all active:scale-95">
                            <x-icon-mail class="w-5 h-5 sm:w-6 sm:h-6 text-white"/>
                            Email Us
                        </a>
                    </div>

                    <!-- Mobile: logo + indicators stacked with content at bottom-right -->
                    <div class="mt-6 flex md:hidden w-full justify-end">
                        <div class="flex flex-col items-end gap-2">
                            <img src="/assets/icons/Logo white.svg" alt="WestHub Healthcare Mark" class="h-8 opacity-95">
                            <div class="flex items-center gap-2">
                                <template x-for="(slide, index) in slides" :key="index">
                                    <button @click="activeSlide = index"
                                            :class="activeSlide === index ? 'w-8 bg-primary-100' : 'w-2.5 bg-white/50'"
                                            class="h-2 rounded-full transition-all duration-500"></button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Desktop/Tablet indicators -->
    <div class="hidden md:flex absolute bottom-6 left-auto translate-x-0 bottom-20 right-8 z-30 flex-col items-center gap-3">
        <img src="/assets/icons/Logo white.svg" alt="WestHub Healthcare Mark" class="h-10 lg:h-11 opacity-95">
        
        <div class="flex items-center gap-3">
            <template x-for="(slide, index) in slides" :key="index">
                <button @click="activeSlide = index" 
                        :class="activeSlide === index ? 'w-10 bg-primary-100' : 'w-3 bg-white/40'"
                        class="h-2 rounded-full transition-all duration-500"></button>
            </template>
        </div>
    </div>

    <!-- Depth Shadow -->
    <div class="hidden lg:block absolute inset-y-0 left-0 w-1/3 bg-gradient-to-r from-primary-300/40 to-transparent z-10 pointer-events-none"></div>
</section>
