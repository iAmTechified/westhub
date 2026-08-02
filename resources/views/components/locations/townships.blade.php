@props(['name', 'townships', 'countySlug'])

@php
    $townshipItems = \App\Support\LocationData::normalizeTownshipList($townships ?? []);
@endphp

<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 md:px-8">
        <h2 class="font-display text-3xl md:text-4xl font-bold text-primary-300 mb-12">
            {{ $name }} Home Care Services:
        </h2>

        <div class="flex flex-wrap gap-4">
            @foreach($townshipItems as $township)
                <x-locations.township-pill
                    :name="$township['name']"
                    :slug="$township['slug']"
                    :county-slug="$countySlug"
                />
            @endforeach
        </div>
    </div>
</section>
