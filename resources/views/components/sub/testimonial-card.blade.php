@props(['avatar', 'name', 'role', 'text', 'stars' => 5])

<div class="bg-white w-full rounded-2xl md:rounded-3xl px-8 pt-6 pb-12 border border-neutral-100 shadow-[0_20px_38px_-36px_rgba(0,0,0,0.09)] flex min-h-[280px] min-w-[200px] h-full flex-col transition-[border-color,box-shadow] duration-300 hover:border-t-warning-100 hover:border-l-warning-100 hover:shadow-[0_20px_40px_-30px_rgba(0,0,0,0.38)]">
    <div class="flex items-center gap-2.5 mb-[13px]">
        <div class="w-[60px] h-[60px] md:w-[80px] md:h-[80px] rounded-full overflow-hidden shrink-0">
            <img src="{{ $avatar }}" alt="{{ $name }}" class="w-full h-full object-cover pointer-events-none">
        </div>
        <div class="min-w-0">
            <h4 class="font-display font-bold text-md md:text-lg text-neutral-600 truncate">{{ $name }}</h4>
            <p class="text-xs md:text-sm text-neutral-300 font-regular mt-0 truncate">{{ $role }}</p>
        </div>
    </div>

    <p class="text-neutral-600 leading-[25px] text-sm md:text-base font-display font-regular">
        {{ $text }}
    </p>

    <div class="mt-4 flex gap-[5px]">
        @for($i = 0; $i < 5; $i++)
            <img src="/assets/icons/Property 1=Star.svg" alt=""
                 class="w-[25px] h-[25px] pointer-events-none {{ $i < $stars ? 'opacity-100' : 'opacity-[0.12]' }}"
                 style="{{ $i < $stars ? 'filter: invert(74%) sepia(87%) saturate(1074%) hue-rotate(352deg) brightness(102%) contrast(101%);' : '' }}">
        @endfor
    </div>
</div>
