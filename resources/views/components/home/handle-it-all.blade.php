<section class="py-24 bg-primary-300 relative overflow-hidden" x-data="{
    active: 0,
    services: [
        { 
            title: 'Flexible Homemaker Services', 
            desc: 'Supporting seniors with daily essentials and light housekeeping to keep the home bright and safe.',
            icon: 'Property 1=Home Service.svg'
        },
        { 
            title: 'Compassionate Health Aides', 
            desc: 'Heartfelt assistance focused on improving health outcomes and preserving independence.',
            icon: 'Property 1=Health Aides.svg'
        },
        { 
            title: 'Transition to Home Services', 
            desc: 'Personalized care following a hospital stay to guide you comfortably back to wellness.',
            icon: 'Property 1=Home Service.svg'
        },
        { 
            title: 'Full-Time Live-in Companionship', 
            desc: 'Ensuring peace of mind with 24-hour presence and dedicated support for your loved ones.',
            icon: 'Property 1=Home Companionship.svg'
        }
    ],
    next() { this.active = (this.active + 1) % this.services.length },
    prev() { this.active = (this.active - 1 + this.services.length) % this.services.length }
}">
    <!-- Section Header (Refactored for High Fidelity) -->
    <div class="max-w-6xl mx-auto px-6 md:px-12 lg:px-16 text-left text-white mb-16">
        <h2 class="font-display text-3xl md:text-5xl font-semibold mb-6">We handle it all</h2>
        <p class="text-sm md:text-base text-primary-50 max-w-4xl opacity-90">
            Nutritional Support | Daily Errands & Skilled Nursing | Light Household Tasks | Personal Hygiene Assistance | Continence Care | Memory & Respite Support | Medication Management.
        </p>
    </div>

    <!-- Central White Card Slider -->
    <div class="max-w-6xl mx-auto px-4 relative">
        <div class="bg-white rounded-[2rem] p-10 sm:p-12 pb-4 lg:p-16 relative flex flex-col justify-center items-center md:items-end lg:items-center lg:flex-row items-center min-h-[300px] sm:min-h-[300px] md:min-h-[100px] lg:min-h-[460px] xl:min-h-[500px]">
             
            <!-- Desktop Navigation Arrows -->
            <button @click="prev()" class="hidden lg:block absolute left-2 sm:left-4 md:left-8 z-20 p-2 hover:scale-110 transition-transform">
                <img src="/assets/icons/Property 1=Back.svg" alt="Back" class="w-9 h-9 sm:w-11 sm:h-11 md:w-12 md:h-12">
            </button>

            <!-- Slider Content -->
            <div class="flex-grow lg:max-w-4xl mx-auto">
                <template x-for="(service, index) in services" :key="index">
                    <div x-show="active === index" 
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0 translate-x-8"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         class="w-full flex flex-col md:flex-row items-center justify-center gap-6 md:gap-8 lg:gap-16 text-center md:text-left sm:px-2 lg:px-12 min-h-[200px] sm:min-h-[200px] md:min-h-[250px] lg:min-h-[300px]">
                         
                         <!-- Circular Icon Area -->
                         <div class="shrink-0">
                             <div class="w-28 h-28 md:w-48 md:h-48 bg-primary-200 rounded-full flex items-center justify-center shadow-lg">
                                 <img :src="'/assets/icons/' + service.icon" alt="Service Icon" class="w-12 h-12 md:w-28 md:h-28 brightness-0 invert">
                             </div>
                         </div>

                         <!-- Text Area -->
                        <div class="flex-grow w-full min-h-[150px]">
                             <h3 class="font-display text-xl md:text-4xl lg:text-5xl font-bold text-primary-300 mb-2 leading-tight" x-text="service.title"></h3>
                             <p class="text-sm md:text-xl text-primary-300 leading-relaxed max-w-xl opacity-80 font-medium" x-text="service.desc"></p>
                         </div>
                    </div>
                </template>
            </div>

            <button @click="next()" class="hidden lg:block absolute right-2 sm:right-4 md:right-8 z-20 p-2 hover:scale-110 transition-transform">
                <img src="/assets/icons/Property 1=Next.svg" alt="Next" class="w-9 h-9 sm:w-11 sm:h-11 md:w-12 md:h-12">
            </button>

        <!-- Tablet/Mobile Controls: stacked below content -->
        <div class="lg:hidden mt-2 flex items-center justify-center gap-4">
            <button @click="prev()" class="p-2 hover:scale-110 transition-transform">
                <img src="/assets/icons/Property 1=Back.svg" alt="Back" class="w-10 h-10">
            </button>
            <button @click="next()" class="p-2 hover:scale-110 transition-transform">
                <img src="/assets/icons/Property 1=Next.svg" alt="Next" class="w-10 h-10">
            </button>
        </div>
        </div>

    </div>
</section>
