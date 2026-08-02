<section class="bg-white">
    <div class="max-w-7xl mx-auto px-4 md:px-8 py-16 md:py-20 lg:py-24 text-center">
        <!-- Title -->
        <h1 class="font-display text-[2.625rem] md:text-[3.75rem] lg:text-[4rem] font-bold text-primary-300 leading-[1.08] mb-3 tracking-[-0.02em]">
            Home Care Services
        </h1>
        
        <!-- Subtitle -->
        <p class="text-[1.5rem] md:text-[2rem] text-primary-300/65 font-medium mb-7 md:mb-8 tracking-[-0.01em]">
            Non-skilled Professional
        </p>

        <!-- CTA Button -->
        <div class="flex justify-center">
            <x-button-primary 
                type="button"
                onclick="window.dispatchEvent(new CustomEvent('open-appointment'))"
                class="flex items-center gap-2.5 !h-14 !px-8 !py-0 bg-primary-100 text-white shadow-xl shadow-primary-100/30 hover:shadow-primary-100/50 transition-all duration-300 active:translate-y-0.5"
            >
                <x-icon-calendar class="w-5 h-5" />
                <span class="text-lg leading-none">Book Appointment</span>
            </x-button-primary>
        </div>
    </div>
</section>
