<section class="bg-primary-50 relative w-full min-h-[420px] md:min-h-[520px] lg:h-[600px] flex items-end overflow-hidden py-16 pb-8 lg:py-20">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0 z-0">
        <img 
            src="{{ asset('assets/images/Scaled down - side-view-smiley-nurse-talking-patient 1.webp') }}" 
            alt="Professional Care" 
            class="object-cover object-top w-full h-full"
        >
        <div class="absolute inset-0 bg-neutral-600/50" style="background: linear-gradient(180deg, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.89) 100%);
"></div>
    </div>

    <!-- Content Area -->
    <div class="container relative z-10 px-4 mx-auto md:px-6 h-full flex flex-wrap flex-col gap-6 justify-end lg:flex-row lg:justify-between lg:items-end">
        <div class="max-w-3xl">
            <!-- Badge -->
            <div class="inline-block px-3 py-1 mb-4 text-xs font-semibold tracking-wider uppercase bg-neutral-50 text-neutral-600 rounded-sm">
                Today
            </div>

            <!-- Title -->
            <h1 class="mb-4 text-3xl sm:text-3xl font-bold leading-tight text-neutral-50 md:text-5xl lg:text-6xl font-display">
                Find the Professional Care Your Family Deserves.
            </h1>

            <!-- Subtext -->
            <p class="text-lg md:text-xl lg:text-2xl text-neutral-50 font-sans">
                Comprehensive Care Solutions tailored to Your Needs
            </p>

        </div>

            <!-- Read More Link (Design shows it on the right side of the hero text area or as a button) -->
            <div class="flex items-center">
                <a href="#" class="flex items-center gap-3 px-6 py-3 transition-all border rounded-full text-neutral-50 border-neutral-50/50 hover:bg-neutral-50/10 backdrop-blur-sm">
                    <span class="text-sm font-medium">Read more</span>
                    <x-icon-arrow-right class="w-4 h-4 fill-current transition-transform group-hover:translate-x-1" />
                </a>
            </div>
    </div>
</section>
