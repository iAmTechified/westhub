@props(['name', 'href' => null])

@if ($href)
    <a href="{{ $href }}" class="flex items-start justify-start gap-1 group cursor-pointer">
        <x-icon-location class="min-w-[30px] h-[30px] text-neutral-600 group-hover:scale-110 group-hover:text-primary-100 transition-transform duration-300" />
        <span class="text-neutral-600 text-base font-medium group-hover:text-primary-100 group-hover:scale-105 transition-colors leading-relaxed] mt-0">
            {{ $name }}
        </span>
    </a>
@else
    <div class="flex items-start justify-start gap-1 group cursor-default">
        <x-icon-location class="min-w-[30px] h-[30px] text-neutral-600 group-hover:scale-110 group-hover:text-primary-100 transition-transform duration-300" />
        <span class="text-neutral-600 text-base font-medium group-hover:text-primary-100 group-hover:scale-105 transition-colors leading-relaxed mt-0">
            {{ $name }}
        </span>
    </div>
@endif
