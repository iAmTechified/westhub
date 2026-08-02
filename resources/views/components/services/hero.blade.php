@props(['title', 'subtitle'])

<section class="py-20 bg-neutral-50">
    <div class="max-w-7xl mx-auto px-2 md:px-8 text-center flex flex-col items-center">
        <h1 class="font-display text-5xl md:text-5xl lg:text-6xl font-bold text-primary-300 mb-4 tracking-tight">
            {{ $title }}
        </h1>
        
        <p class="font-display text-base md:text-xl font-medium text-neutral-400 tracking-[0.2em] mb-4">
            {{ $subtitle }}
        </p>

        <x-button-primary type="button" onclick="window.dispatchEvent(new CustomEvent('open-appointment'))" class="!px-12 !py-4 shadow-[0_10px_30px_-10px_rgba(20,171,213,0.3)] flex items-center gap-3">
            <x-icon-calendar class="w-5 h-5"/>
            <span class="text-base font-bold">Book Appointment</span>
        </x-button-primary>
    </div>
</section>
