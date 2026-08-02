@props(['title', 'description'])

<div class="flex gap-6 group text-left">
    <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-[#14ABD5] flex items-center justify-center text-white shadow-lg transition-transform group-hover:scale-110">
        {{ $icon }}
    </div>
    <div>
        <h3 class="text-3xl font-display font-bold text-[#1C3F78] mb-3 text-left leading-tight tracking-tight">{{ $title }}</h3>
        <p class="text-neutral-500 text-lg leading-relaxed text-left">
            {{ $description }}
        </p>
    </div>
</div>
