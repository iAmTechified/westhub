@props(['number', 'title', 'subtitle', 'backTitle', 'description', 'icon', 'image', 'features', 'link' => []])

<div x-data="{ flipped: false }"
     @mouseenter="flipped = true"
     @mouseleave="flipped = false"
     @click="flipped = !flipped"
     class="group min-h-[480px] md:min-h-[400px] lg:min-h-[480px] [perspective:2000px] cursor-pointer">
    <div class="relative h-full w-full transition-all duration-700 [transform-style:preserve-3d]"
         :class="flipped ? '[transform:rotateY(180deg)]' : ''">

        <div class="absolute inset-0 backface-hidden bg-white rounded-[18px] shadow-[0_26px_55px_-35px_rgba(20,55,106,0.35)] flex flex-col overflow-hidden">
            <!-- Top Image Section with Fade -->
            <div class="relative h-[180px] md:h-[200px] w-full flex-shrink-0">
                <img src="{{ $image }}" alt="{{ $title }}" class="absolute inset-0 h-full w-full object-cover object-top" />
                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-white/30 to-white"></div>
                
                <!-- Number positioned at the bottom left of the image area -->
                <div class="absolute bottom-[10px] left-6">
                    <span class="font-display text-[34px] leading-none font-normal text-primary-300 opacity-20">{{ $number }}</span>
                </div>
            </div>

            <!-- Content Section -->
            <div class="flex flex-col flex-grow px-6 pb-8">
                <h3 class="font-display text-[24px] md:text-[28px] leading-[1.1] font-normal tracking-[-0.01em] text-primary-300 {{ $subtitle ? 'mb-1' : 'mb-4' }}">{{ $title }}</h3>
                @if($subtitle)
                    <p class="text-[14px] md:text-[15px] leading-none text-[#8A9EB3] font-normal mb-4">{{ $subtitle }}</p>
                @endif

                <p class="text-[14px] md:text-[15px] leading-[1.6] text-primary-300 font-regular mb-6">
                    {{ $description }}
                </p>

                <!-- Bottom Logo/Icon -->
                <div class="mt-auto flex justify-end">
                    <img src="{{ asset('assets/icons/Logo=Cut out.svg') }}" alt="WestHub Mark" class="h-[24px] w-auto" />
                </div>
            </div>
        </div>

        <div class="absolute inset-0 backface-hidden [transform:rotateY(180deg)] bg-[#005FA8] rounded-[18px] p-4 md:p-8 lg:p-9 text-white flex flex-col justify-between align-between overflow-hidden">
        <div>    
        <div class="flex items-center gap-3 mb-4">
                <img src="{{ $icon }}" alt="{{ $title }} Icon" class="h-[28px] w-[28px] brightness-0 invert flex-shrink-0" />
                <h3 class="font-display text-md md:text-lg text-primary-50 leading-tight font-regular">{{ $backTitle }}</h3>
            </div>

            <p class="text-xs md:text-sm leading-[1.6] text-white/95 mb-5">
                {{ $description }}
            </p>
            </div>

            <ul class="space-y-3.5 mb-4">
                @foreach($features as $feature)
                    <li class="flex items-center gap-3 text-xs md:text-sm leading-normal">
                        <div class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-md bg-white">
                            <svg class="h-3.5 w-3.5 text-[#005FA8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span>{{ $feature }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="mt-auto flex items-center justify-between">
                <img src="{{ asset('assets/icons/Logo white.svg') }}" alt="WestHub Mark" class="h-[24px] w-auto" />

                <a href="{{ $link }}" @click.stop class="inline-flex items-center gap-2 text-sm md:text-md font-medium text-white hover:opacity-90">
                    <span>Explore</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5l5 5-5 5" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
