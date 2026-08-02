@props(['number', 'title', 'subtitle', 'description', 'icon'])

<div class="relative p-10 transition-all duration-500 bg-white border border-neutral-200 group hover:shadow-2xl hover:-translate-y-2 rounded-[2.5rem] overflow-hidden flex flex-col h-full">
    <div class="flex justify-between items-start mb-8">
        <span class="text-5xl font-bold opacity-10 text-neutral-300 font-display transition-opacity group-hover:opacity-20">{{ $number }}</span>
        <div class="p-4 rounded-2xl bg-primary-100/10 text-primary-100 transform group-hover:scale-110 transition-transform duration-500">
            <{{ $icon }} class="w-8 h-8" />
        </div>
    </div>
    <h3 class="text-3xl font-bold text-primary-300 mb-2 font-display leading-tight">{{ $title }}</h3>
    <p class="text-sm font-semibold text-primary-100 mb-6 uppercase tracking-wider">{{ $subtitle }}</p>
    <p class="text-neutral-500 leading-relaxed mb-auto">
        {{ $description }}
    </p>
    
    <!-- Decorative Shapes -->
    <div class="mt-12 flex -space-x-4 opacity-80 group-hover:opacity-100 transition-opacity">
        <div class="w-10 h-10 rounded-full bg-[#1C3F78]"></div>
        <div class="w-10 h-10 rounded-full bg-[#14ABD5]"></div>
        <div class="w-10 h-10 rounded-full bg-[#EAFAFF]"></div>
    </div>
</div>
