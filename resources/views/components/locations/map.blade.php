@props(['coords', 'name'])

@php
    $lat = (float) ($coords[0] ?? 0);
    $lng = (float) ($coords[1] ?? 0);
    $zoom = 10;
    $embedQuery = urlencode($lat . ',' . $lng);
    $mapEmbedUrl = "https://www.google.com/maps?q={$embedQuery}&z={$zoom}&output=embed";
@endphp

<section class="pb-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 md:px-8">
        <div class="relative w-full aspect-[4/3] md:aspect-[16/9] lg:aspect-[21/9] overflow-hidden rounded-[1.5rem] border border-neutral-200 shadow-lg bg-neutral-100">
            <iframe
                title="{{ $name }} map"
                src="{{ $mapEmbedUrl }}"
                class="h-full w-full"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen>
            </iframe>
        </div>
    </div>
</section>
