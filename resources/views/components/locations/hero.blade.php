@props(['name', 'subtitle'])

<section class="relative bg-primary-300 overflow-hidden py-20 lg:py-12 lg:max-h-[500px]">
    <!-- Brand Gradient Overlay -->
    <div class="absolute inset-0 bg-gradient-brand opacity-60"></div>
    
    <!-- Background Pattern/Circles (Matching Hero Design) -->
    <div class="absolute -bottom-10 -right-10 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
    <div class="absolute top-10 left-10 w-48 h-48 bg-primary-100/10 rounded-full blur-2xl"></div>

    <div class="max-w-7xl mx-auto px-4 md:px-8 relative z-10 h-full">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Left Content -->
            <div class="text-white">
                <h1 class="font-display text-4xl md:text-6xl lg:text-7xl font-semibold mb-6 tracking-tight">
                    {{ $name }}
                </h1>
                <p class="text-lg md:text-xl lg:text-2xl font-medium text-primary-50/90 mb-10 max-w-lg leading-relaxed">
                    {{ $subtitle }}
                </p>

                <div class="flex flex-wrap gap-4 max-w-md">
                    <!-- Book Appointment Button -->
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-appointment'))" class="inline-flex flex-grow items-center justify-center gap-3 px-8 py-4 bg-white text-primary-300 font-bold rounded-full hover:bg-neutral-50 transition-all shadow-lg hover:shadow-xl active:scale-95">
                        <x-icon-calendar class="w-5 h-5" />
                        Book Appointment
                    </button>

                    <!-- Email Us Button -->
                    
                    <a href="{{ \App\Support\SiteSettings::contactMailto() }}"
                       class="inline-flex flex-grow items-center justify-center gap-1 h-[54px] px-6 rounded-full bg-[linear-gradient(180deg,rgba(171,217,239,0.72)_0%,rgba(138,197,228,0.62)_100%)] border border-[#BFDCF0] text-[#EAF6FF] text-[17px] font-medium leading-none whitespace-nowrap shadow-[inset_0_1px_0_rgba(255,255,255,0.35)] transition-colors hover:bg-[linear-gradient(180deg,rgba(175,221,243,0.78)_0%,rgba(144,203,233,0.68)_100%)]">
                        <span class="inline-flex items-center justify-center w-5 h-5">
                            <img src="{{ asset('assets/icons/Property 1=Email Us.svg') }}" alt="" class="w-full h-full brightness-0 invert">
                    </span>
                        Email Us
                    </a>
                </div>
            </div>

            <!-- Right Image -->
            <div class="relative group">
                <!-- Decorative element (Matching the white logo-like shape in the corner of design) -->
                <div class="absolute bottom-4 right-4 z-20">
                    <img src="{{ asset('assets/icons/Logo white.svg') }}" alt="WestHub" class="w-16 h-16 lg:w-20 lg:h-20">
                </div>
                
                <div class="relative z-10 transition-transform duration-700 group-hover:scale-[1.02]">
                    <img src="{{ asset('assets/images/Location image.webp') }}" 
                         alt="{{ $name }} Location" 
                         class="w-[70%] h-auto drop-shadow-[0_20px_50px_rgba(0,0,0,0.3)]">
                </div>
            </div>
        </div>
    </div>
</section>
