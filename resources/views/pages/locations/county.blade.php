<x-layouts.app :seo="$seo">
    <!-- Hero Section -->
    <x-locations.hero 
        :name="$countyData['name']" 
        :subtitle="$countyData['subtitle']" 
    />

    <!-- Townships Section -->
    <x-locations.townships 
        :name="$countyData['name']" 
        :townships="$countyData['townships']" 
        :county-slug="$countySlug"
    />

    <!-- Map Section -->
    <x-locations.map 
        :coords="$countyData['coords']" 
        :name="$countyData['name']" 
    />

    <x-newsletter />

</x-layouts.app>
