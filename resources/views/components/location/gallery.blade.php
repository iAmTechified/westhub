<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl md:text-5xl font-display font-bold text-primary-300 mb-12">
            Our Gallery
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            {{-- Mock Gallery Images - using placeholders that look like home healthcare --}}
            @for($i = 1; $i <= 4; $i++)
                <div class="aspect-[3/4] rounded-3xl overflow-hidden shadow-lg">
                    <img src="https://images.unsplash.com/photo-1581056771107-24ca5f033842?q=80&w=600&auto=format&fit=crop" 
                         alt="Gallery Image {{ $i }}" 
                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                </div>
            @endfor
        </div>

        <x-button-secondary href="/gallery" class="px-8 py-3">
            View more
            <x-icon-arrow-right class="w-4 h-4 ml-2" />
        </x-button-secondary>
    </div>
</section>
