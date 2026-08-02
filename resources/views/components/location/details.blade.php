@props(['content', 'township'])

<section class="py-16 bg-white">
    <div class="container mx-auto max-w-6xl px-8">
        {{-- Primary Details --}}
        <div class="mb-8">
            <h2 class="text-xl md:text-2xl font-display font-bold text-primary-300 mb-4">
                {{ $content['title'] }}
            </h2>
            <p class="text-neutral-500 font-semibold mb-4 md:text-justify">
                {{ $content['subtitle'] }}
            </p>
            <div class="text-neutral-500 leading-relaxed mb-4 md:text-justify">
                {{ $content['mission'] }}
            </div>
            <a href="https://illinois.hometownlocator.com/il/cook/chicago.cfm" class="text-neutral-500 font-semibold underline hover:text-primary-100 transition-colors">
                {{ $township }} Profile: Facts & Data
            </a>
        </div>

        {{-- Secondary Details --}}
        <div class="grid md:grid-cols-1 gap-8">
            <div class="">
                <h3 class="text-lg md:text-xl font-display font-bold text-primary-300 mb-2">
                    {{ $content['secondary_title'] }}
                </h3>
                <p class="text-neutral-500 mb-4 leading-relaxed md:text-justify">
                    {{ $content['secondary_description'] }}
                </p>
                <p class="text-neutral-500 leading-relaxed md:text-justify">
                    {{ $content['promise'] }}
                </p>
            </div>
        </div>
    </div>
</section>
