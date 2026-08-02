@props(['icon', 'title', 'description', 'border' => false])

<div class="group bg-white p-12 rounded-[50px] border border-neutral-100 shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 text-center {{ $border ? 'border-t-4 border-t-[#14ABD5]/20' : '' }}">
    <div class="w-20 h-20 rounded-full bg-[#EAFAFF] flex items-center justify-center text-[#14ABD5] mx-auto mb-8 group-hover:bg-[#14ABD5] group-hover:text-white transition-all duration-500 shadow-inner">
        <{{ $icon }} class="w-10 h-10" />
    </div>
    <h3 class="text-2xl font-display font-bold text-[#1C3F78] mb-4">{{ $title }}</h3>
    <p class="text-[#3D3D3D] leading-relaxed text-lg">
        {{ $description }}
    </p>
</div>
