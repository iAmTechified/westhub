<section class="relative min-h-[640px] lg:min-h-[760px] bg-[#EAFAFF] overflow-hidden">
    {{-- Background Image Positioned to the Right --}}
    <div class="absolute inset-y-0 right-0 w-full lg:w-[58%] z-0">
        <img 
            src="{{ asset('assets/images/old man & grand daughter.webp') }}" 
            alt="Care and compassion" 
            class="w-full h-full object-cover object-right pointer-events-none"
        />
        {{-- Gradient Overlay fading from solid background color to transparent --}}
        <div class="absolute inset-0 bg-gradient-to-r from-[#EAFAFF] via-[#EAFAFF]/82 to-[#EAFAFF]/12 z-10"></div>
    </div>

    <div class="container mx-auto px-4 md:px-10 xl:px-12 max-w-7xl relative z-20 py-16 md:py-20 lg:py-24">
        <div class="flex flex-col lg:flex-row items-start w-full relative">
            {{-- Left Side: Vision & Mission Content --}}
            <div class="w-full lg:w-[46%]">
                <div class="max-w-[560px] space-y-12 md:space-y-14 lg:space-y-12">
                    {{-- Our Vision --}}
                    <div class="flex flex-col gap-5">
                        <div class="flex items-center gap-3">
                            <div class="text-[#14ABD5]">
                                <x-icon-vision class="w-8 h-8 md:w-9 md:h-9 fill-current" />
                            </div>
                            <h3 class="text-[34px] sm:text-[40px] md:text-[46px] lg:text-[56px] leading-[1.02] font-display font-bold text-primary-300">Our Vision</h3>
                        </div>
                        <p class="text-neutral-600 text-[17px] md:text-[18px] leading-[1.5] font-normal max-w-[610px]">
                            To be the most trusted partner in home healthcare, setting the standard for excellence where every individual can lead a dignified, healthy, and independent life in the comfort of their own home.
                        </p>
                    </div>

                    {{-- Our Mission --}}
                    <div class="flex flex-col gap-5">
                        <div class="flex items-center gap-3">
                            <div class="text-[#14ABD5]">
                                <x-icon-mission class="w-8 h-8 md:w-9 md:h-9 fill-current" />
                            </div>
                            <h3 class="text-[34px] sm:text-[40px] md:text-[46px] lg:text-[56px] leading-[1.02] font-display font-bold text-primary-300">Our Mission</h3>
                        </div>
                        <p class="text-neutral-600 text-[17px] md:text-[18px] leading-[1.5] font-normal max-w-[610px]">
                            To enhance the quality of life for our patients by providing professional, compassionate home healthcare that prioritizes physical health, emotional well-being, and genuine human connection.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Right Side: Floating Core Values Card (Glass) --}}
            <div class="w-full lg:w-[54%] relative mt-10 md:mt-12 lg:mt-0 flex justify-center lg:block lg:min-h-[560px]">
                <div class="relative lg:absolute lg:left-[26%] xl:left-[24%] lg:bottom-10">
                    {{-- glass card --}}
                    <div class="bg-white/62 backdrop-blur-[10px] rounded-[18px] p-6 md:p-7 shadow-[0_16px_40px_-14px_rgba(28,63,120,0.35)] border border-white/55 w-full max-w-[330px] lg:max-w-[305px]">
                        <h4 class="text-[34px] md:text-[38px] lg:text-[44px] leading-[0.95] font-display font-bold text-primary-300 mb-5">Our Core<br>Values</h4>
                        <ul class="space-y-4">
                            <li class="flex items-center gap-3 group">
                                <div class="text-[#14ABD5] transition-transform group-hover:scale-110">
                                    <x-icon-home class="w-5 h-5 fill-current" />
                                </div>
                                <span class="text-[#0B1626] font-medium text-[18px] leading-tight">Compassion First</span>
                            </li>
                            <li class="flex items-center gap-3 group">
                                <div class="text-[#14ABD5] transition-transform group-hover:scale-110">
                                    <x-icon-therapy class="w-5 h-5 fill-current" />
                                </div>
                                <span class="text-[#0B1626] font-medium text-[18px] leading-tight">Unwavering Integrity</span>
                            </li>
                            <li class="flex items-center gap-3 group">
                                <div class="text-[#14ABD5] transition-transform group-hover:scale-110">
                                    <x-icon-adult-home-care class="w-5 h-5 fill-current" />
                                </div>
                                <span class="text-[#0B1626] font-medium text-[18px] leading-tight">Empowered Independence</span>
                            </li>
                        </ul>
                    </div>

                    {{-- Bottom Right Watermark on image --}}
                    <div class="absolute -bottom-14 -right-10 lg:-right-28 w-14 opacity-40 pointer-events-none">
                        <img src="{{ asset('assets/icons/Logo white.svg') }}" alt="" class="w-full">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
