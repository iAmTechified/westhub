<x-layouts.app :seo="$seo">
    <div class="bg-neutral-50 pt-16 md:pt-24 pb-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-6xl font-bold font-display text-primary-300 tracking-tight">
                Our Gallery
            </h1>
        </div>
    </div>

    <!-- Gallery Grid Section -->
    <x-gallery.grid :images="$images" />

    <!-- Testimonials Section -->
    <x-shared.testimonials />

    <x-newsletter />

</x-layouts.app>
