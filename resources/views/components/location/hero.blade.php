@props(['content', 'township'])

<section class="relative py-10 bg-primary-50/30">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-3xl md:text-5xl font-display font-bold text-primary-300 mb-4 max-w-4xl mx-auto leading-tight">
            {{ $content['title'] }}
        </h1>
        
        <x-button-primary type="button" onclick="window.dispatchEvent(new CustomEvent('open-appointment'))" class="px-8 py-4">
            <x-icon-calendar class="w-5 h-5 mr-2" />
            {{ $content['cta_text'] }}
        </x-button-primary>
    </div>
</section>
