<x-layouts.app :seo="$seo">
    {{-- Section 1: Hero Section --}}
    <x-location.hero :content="$location->content['hero']" :township="$location->township_name" />

    {{-- Section 2: Township Details --}}
    <x-location.details :content="$location->content['details']" :township="$location->township_name" />

    {{-- Section 3 & 4: Offerings --}}
    <div class="py-18 bg-white border-t-2 border-t-neutral-100">
        <div class="max-w-8xl mx-auto text-center mb-6 pt-6 px-2 md:px-6 lg:px-none">
            <h2 class="text-2xl md:text-[2.7rem] font-display font-bold text-primary-300">
                {{ $location->content['offerings']['title'] }}
            </h2>
        </div>
        <x-location.offerings :items="$location->content['offerings']['items']" :township="$location->township_name" />
    </div>

    {{-- Section 5: County Locations --}}
    <x-location.county-directory
        :county="$location->county_name"
        :county-slug="$location->county_slug"
        :townships="$location->townships"
        :current-township="$location->township_name"
    />

    {{-- Section 6: Healthcare Brands (Slider) --}}
    <x-shared.healthcare-brands />

    {{-- Section 7: Gallery --}}
    <x-home.gallery />

    {{-- Section 8: Testimonials --}}
    <x-shared.testimonials />

    {{-- Section 9: Get in touch --}}
    <x-location.contact-cta 
        :title="$location->content['footer_cta']['title']" 
        :description="$location->content['footer_cta']['description']" 
    />
</x-layouts.app>
