<section class="relative min-h-[500px] md:min-h-[540px] flex items-center pt-24 pb-16 overflow-hidden bg-primary-300">
    <!-- Background Image -->
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('assets/images/A_high-quality,_cinematic_202603201740 1.webp') }}" 
             alt="Skilled Nursing Service" 
             class="object-cover w-full h-full object-center">
        <!-- Mobile: full overlay. Desktop/Tablet: layered gradients -->
        <div class="absolute inset-0 bg-primary-300/92 md:bg-primary-300/45"></div>
        <div class="hidden md:block absolute inset-0 bg-gradient-to-r from-primary-300/95 via-primary-200/55 to-transparent"></div>
        <div class="hidden md:block absolute inset-y-0 left-0 w-full md:w-[44%] bg-primary-300/35"></div>
    </div>

    <!-- Content Area -->
    <div class="container relative z-10 px-6 mx-auto lg:px-12">
        <div class="max-w-2xl lg:pt-3">
            <h1 class="text-[30px] sm:text-[34px] md:text-[52px] lg:text-[64px] font-bold font-display text-white leading-[1.08] md:leading-[1.05] mb-4 md:mb-5 tracking-tight">
                Skilled & Unskilled <br>
                Nursing Service
            </h1>
            <p class="text-lg sm:text-xl md:text-[28px] lg:text-[34px] text-white/95 font-sans mb-7 md:mb-10 font-medium leading-tight md:leading-none">
                At your doorstep...
            </p>

            <div class="flex flex-col sm:flex-row items-center gap-3.5">
                <!-- Book Appointment Button (Using Shared Component) -->
                <x-button-primary type="button" onclick="window.dispatchEvent(new CustomEvent('open-appointment'))" variant="white" class="w-full sm:w-auto !h-14 !px-6 !py-0 !text-base !font-semibold gap-2.5 !shadow-none">
                    <span class="inline-flex items-center justify-center w-8 h-8">
                        <img src="{{ asset('assets/icons/Property 1=Calendar.svg') }}" alt="" class="w-[18px] h-[18px]" style="filter: invert(19%) sepia(21%) saturate(5429%) hue-rotate(200deg) brightness(97%) contrast(93%);">
                    </span>
                    <span>Book Appointment</span>
                </x-button-primary>

                <!-- Email Us Button (Using Shared Component) -->
                <x-button-secondary href="{{ \App\Support\SiteSettings::contactMailto() }}" variant="hero" class="w-full sm:w-auto !h-14 !px-6 !py-0 !text-base gap-2.5  bg-[linear-gradient(180deg,rgba(171,217,239,0.72)_0%,rgba(138,197,228,0.62)_100%)] border border-[#BFDCF0] text-[#EAF6FF] text-[17px] font-medium leading-none whitespace-nowrap shadow-[inset_0_1px_0_rgba(255,255,255,0.35)] transition-colors hover:bg-[linear-gradient(180deg,rgba(175,221,243,0.78)_0%,rgba(144,203,233,0.68)_100%)] !font-semibold">
                    <span class="inline-flex items-center justify-center w-8 h-8">
                        <img src="{{ asset('assets/icons/Property 1=Email Us.svg') }}" alt="" class="w-[18px] h-[18px] brightness-0 invert">
                    </span>
                    <span>Email Us</span>
                </x-button-secondary>
            </div>

        </div>
    </div>

    <!-- Brand Decal (Bottom Right) -->
    <div class="absolute bottom-6 right-8 z-10 opacity-80">
        <img src="{{ asset('assets/icons/Logo white.svg') }}" alt="" class="w-14 h-auto">
    </div>
</section>
