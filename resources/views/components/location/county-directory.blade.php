@props([
    'county',
    'countySlug',
    'townships' => [],
    'currentTownship' => null,
])

@php
    $townshipItems = \App\Support\LocationData::normalizeTownshipList($townships ?? []);
    $currentTownshipSlug = $currentTownship ? str($currentTownship)->slug()->toString() : null;
@endphp

<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-display font-bold text-primary-300 mb-10">
            {{ $county }} Home Care Services:
        </h2>

        <div class="flex flex-wrap gap-4">
            @foreach($townshipItems as $township)
                @php
                    $isCurrent = $township['slug'] === $currentTownshipSlug;
                @endphp

                <a
                    href="{{ route('locations.township', ['county' => $countySlug, 'township' => $township['slug']]) }}"
                    @class([
                        'px-6 py-3 rounded-full font-medium transition-all shadow-sm border border-transparent',
                        'bg-primary-100 text-white cursor-default' => $isCurrent,
                        'bg-neutral-200/50 text-neutral-600 hover:bg-primary-100 hover:text-white hover:shadow-md hover:border-primary-100/20 active:scale-95' => ! $isCurrent,
                    ])
                    @if($isCurrent) aria-current="page" @endif
                >
                    {{ $township['name'] }}
                </a>
            @endforeach
        </div>
    </div>
</section>
