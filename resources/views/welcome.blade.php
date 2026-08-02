<x-layouts.app>
    <div class="relative min-h-[500px] flex items-center justify-center overflow-hidden bg-neutral-600">
        <!-- Hero Background Gradient (Gradient/50) -->
        <div class="absolute inset-0 bg-gradient-brand opacity-90"></div>

        <!-- Content -->
        <div class="relative z-10 max-w-4xl mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-6xl font-bold font-display text-neutral-50 mb-6 drop-shadow-lg leading-tight">
                Compassionate Care, <br>
                <span class="text-primary-100 italic">Delivered at Home.</span>
            </h1>
            <p class="text-xl md:text-2xl text-neutral-50 mb-10 opacity-90 max-w-2xl mx-auto leading-relaxed">
                Illinois Licensed and CHAP Certified Home Healthcare Agency dedicated to promoting health and wellbeing for all ages.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-appointment'))" class="px-8 py-4 bg-primary-100 text-primary-300 font-bold rounded-lg shadow-xl hover:bg-neutral-50 hover:shadow-2xl transition-all duration-300 scale-100 hover:scale-105">
                    Book Appointment
                </button>
                <a href="#" class="px-8 py-4 bg-transparent border-2 border-neutral-50 text-neutral-50 font-bold rounded-lg hover:bg-neutral-50 hover:text-primary-300 transition-all duration-300 scale-100 hover:scale-105">
                    Our Services
                </a>
            </div>
        </div>
    </div>

    <!-- Initialization Success Section -->
    <section class="py-20 bg-neutral-50">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="inline-block p-2 px-4 bg-primary-50 text-primary-200 text-xs font-bold uppercase tracking-widest rounded-full mb-4 border border-primary-100">
                System Initialized
            </div>
            <h2 class="text-3xl font-bold font-display text-primary-300 mb-6">Master Plan Ingested</h2>
            <div class="w-24 h-1 bg-gradient-brand mx-auto mb-10 rounded-full"></div>
            <p class="text-neutral-500 max-w-2xl mx-auto leading-relaxed">
                The WestHub Healthcare foundation is now live. All design tokens, core packages, and architectural constraints are locked in. I am ready for the first Handoff Template.
            </p>
        </div>
    </section>
</x-layouts.app>
