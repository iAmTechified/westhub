<section class="py-24 bg-[#F2FBFF]">
    <div class="container mx-auto px-4 md:px-8 max-w-7xl">
        <div class="mb-16">
            <h2 class="text-4xl md:text-5xl font-display font-bold text-primary-300 mb-6">Join Us Today</h2>
            <p class="text-neutral-500 text-lg md:text-xl leading-relaxed max-w-5xl">
                At Westhub Healthcare, we believe that exceptional care starts with an exceptional team. Whether you are a skilled medical professional dedicated to clinical excellence or a compassionate non-medical professional eager to support seniors in their daily lives, there is a place for you here.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 md:gap-8">
            {{-- Skilled Professional Card --}}
            <a href="{{ route('application-form', ['type' => 'skilled']) }}" class="group relative bg-[#1C3F78] rounded-2xl p-7 md:p-8 lg:p-12 transition-all duration-500 hover:bg-primary-200 hover:-translate-y-2 overflow-hidden flex sm:flex-row sm:items-center gap-5 md:gap-6 lg:gap-8 text-left">
                <div class="z-10 w-[18px] h-[18px] md:w-24 md:h-24">
                    <img src="{{ asset('assets/icons/Property 1=Skilled.svg') }}" alt="Skilled" class="w-full h-full brightness-0 invert" style="filter: invert(95%) sepia(12%) saturate(735%) hue-rotate(180deg) brightness(105%) contrast(101%);">
                </div>
                <h3 class="z-10 text-md md:text-2xl lg:text-3xl font-display font-bold text-white">Skilled Professional</h3>
            </a>

            {{-- Non-skilled Professional Card --}}
            <a href="{{ route('application-form', ['type' => 'non-skilled']) }}" class="group relative bg-[#1C3F78] rounded-2xl p-7 md:p-8 lg:p-12 transition-all duration-500 hover:bg-primary-200 hover:-translate-y-2 overflow-hidden flex sm:flex-row sm:items-center gap-5 md:gap-6 lg:gap-8 text-left">
                <div class="z-10 w-[18px] h-[18px] md:w-24 md:h-24">
                    <img src="{{ asset('assets/icons/Property 1=Non Skilled.svg') }}" alt="Non-skilled" class="w-full h-full brightness-0 invert" style="filter: invert(95%) sepia(12%) saturate(735%) hue-rotate(180deg) brightness(105%) contrast(101%);">
                </div>
                <h3 class="relative z-10 text-md md:text-2xl lg:text-3xl font-display font-bold text-white">Non-skilled Professional</h3>
            </a>
        </div>
    </div>


</section>
