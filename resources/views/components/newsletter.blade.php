<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 md:px-8">
        <div class="flex flex-col md:flex-row justify-between md:items-start lg:items-center gap-8 md:gap-10 lg:gap-12">
            <div class="max-w-md">
                <h2 class="text-2xl md:text-4xl font-bold text-primary-300 mb-4 font-display">Stay in Touch with us</h2>
                <p class="text-neutral-500 text-base md:text-lg">Subscribe to our weekly newsletter to stay ahead</p>
            </div>

            <div class="w-full md:max-w-xl lg:max-w-2xl">
                <form
                    method="POST"
                    action="{{ route('newsletter.store') }}"
                    class="flex flex-col lg:flex-row gap-4"
                    data-newsletter-form
                    novalidate
                >
                    @csrf

                    <div class="relative flex-grow">
                        <input
                            type="email"
                            name="email"
                            placeholder="Enter your email"
                            class="w-full px-6 md:px-8 py-3.5 md:py-4 rounded-full border border-neutral-200 focus:outline-none focus:ring-2 focus:ring-primary-100/20 focus:border-primary-100 transition-all text-base md:text-lg"
                            data-newsletter-email
                            required
                        >
                    </div>
                    <x-button-primary type="submit" class="!px-8 md:!px-10 !py-3.5 md:!py-4 shadow-lg shadow-primary-100/20 disabled:opacity-70 disabled:cursor-not-allowed" data-newsletter-submit>
                        <span data-newsletter-label>Stay in Touch</span>
                        <span data-newsletter-loading class="hidden">Submitting...</span>
                    </x-button-primary>
                </form>

                <p data-newsletter-feedback class="hidden mt-3 rounded-2xl px-4 py-3 text-sm font-semibold" role="status" aria-live="polite"></p>

                <p class="text-sm text-neutral-400 pl-4 mt-4">
                    By subscribing, you agree to our <a href="{{ route('privacy.policy') }}" class="text-primary-100 underline hover:text-primary-200 transition-colors">Privacy Policy</a>
                </p>
            </div>
        </div>
    </div>
</section>
