<section class="relative min-h-[520px] lg:min-h-[580px] bg-primary-300 overflow-hidden flex items-center pt-24 pb-20">
    <!-- Person Image Background -->
    <div class="absolute inset-0 z-0 flex items-center justify-center opacity-30 lg:opacity-100">
        <img src="/assets/images/3b433c0af15ca9c886037e29cb5c3e66edb201f9.webp" 
             alt="Enquiry Hero" 
             class="h-full w-auto object-contain object-center scale-125 lg:scale-100 translate-y-10 lg:-translate-x-20">
    </div>

    <!-- Decorative Blurs -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary-100/10 blur-3xl rounded-full -translate-y-1/2 translate-x-1/2"></div>
    <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-primary-100/10 blur-3xl rounded-full translate-y-1/2 -translate-x-1/2"></div>

    <div class="max-w-7xl mx-auto px-5 md:px-10 lg:px-16 w-full relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-12 items-center">
            
            <!-- Left Side: Text -->
            <div class="text-left space-y-2 max-w-4xl">
                <span class="text-white text-sm font-medium tracking-wide">Never miss an update</span>
                <h1 class="font-display text-3xl sm:text-4xl md:text-5xl lg:text-5xl font-medium text-white">
                    Get in touch for
                    the Best Senior
                    Care Services
                    around you today.
                </h1>
            </div>

            <!-- Right Side: Enquiry Form -->
            <div class="flex justify-center lg:justify-end">
                <div class="w-full max-w-2xl">
                    <livewire:enquiry-form />
                </div>
            </div>
        </div>
    </div>
</section>
