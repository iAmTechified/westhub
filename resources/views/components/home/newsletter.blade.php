<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 md:px-8">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-12 border-t border-neutral-100 pt-24">
            
            <!-- Content -->
            <div class="lg:w-1/2">
                <h2 class="font-display text-4xl md:text-5xl font-bold text-primary-300 mb-4">Stay in Touch with us</h2>
                <p class="text-xl text-neutral-500">Join our mailing list and get latest news from us.</p>
            </div>

            <!-- Form -->
            <div class="lg:w-1/2 w-full">
                <form method="POST" action="{{ route('newsletter.store') }}" class="relative flex items-center max-w-2xl lg:ml-auto" data-newsletter-form novalidate>
                    @csrf
                    <input type="email" 
                           name="email"
                           placeholder="Enter your email address through this link" 
                           class="w-full h-16 pl-8 pr-40 text-lg border-2 border-neutral-100 rounded-full focus:border-primary-100 focus:ring-0 transition-all outline-none"
                           data-newsletter-email
                           required>
                    
                    <div class="absolute right-2 top-2 bottom-2">
                        <x-button-primary type="submit" class="!px-10 h-full !text-base disabled:opacity-70 disabled:cursor-not-allowed" data-newsletter-submit>
                            <span data-newsletter-label>Join us</span>
                            <span data-newsletter-loading class="hidden">Submitting...</span>
                        </x-button-primary>
                    </div>
                </form>
                <p data-newsletter-feedback class="hidden mt-3 rounded-2xl px-4 py-3 text-sm font-semibold" role="status" aria-live="polite"></p>
            </div>

        </div>
    </div>
</section>
