@php
    $locations = \App\Support\LocationData::serviceLocationLinks();
@endphp

<section class="py-20 bg-primary-50 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 md:px-8">
        <h2 class="font-display text-3xl font-bold text-primary-300 mb-12">Service Locations</h2>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-y-6 gap-x-3 mb-16">
            @foreach($locations as $location)
                <x-sub.location-item
                    :name="$location['name']"
                    :href="$location['href']"
                />
            @endforeach
        </div>

        <div class="flex justify-start">
            <x-button-primary type="button" onclick="window.dispatchEvent(new CustomEvent('open-appointment'))" class="!bg-[#14ABD5] hover:!bg-[#1194B8] !rounded-full !py-3.5 !px-8 flex items-center gap-2 group transition-all duration-300 shadow-lg shadow-[#14ABD5]/20">
                <x-icon-calendar class="w-5 h-5 text-white" />
                <span class="text-white font-medium">Book Appointment</span>
            </x-button-primary>
        </div>
    </div>
</section>
