@props(['name', 'countySlug', 'slug' => null])

@php
    $townshipSlug = $slug ?: str($name)->slug();
@endphp

<a href="{{ route('locations.township', ['county' => $countySlug, 'township' => $townshipSlug]) }}" 
   class="inline-flex items-center px-6 md:px-7 py-2.5 md:py-3 bg-[#F1F1F1] hover:bg-primary-100 hover:text-white transition-all duration-300 rounded-full cursor-pointer group shadow-sm hover:shadow-md border border-transparent hover:border-primary-100/20 active:scale-95">
    <span class="font-display font-medium text-neutral-500 group-hover:text-white transition-colors duration-300 whitespace-nowrap">
        {{ $name }}
    </span>
</a>
