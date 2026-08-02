<x-layouts.app :seo="$seo">
    <section class="relative overflow-hidden bg-neutral-50">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-300/5 via-white to-primary-100/10"></div>
        <div class="relative max-w-5xl mx-auto px-4 md:px-8 py-16 md:py-24">
            <div class="inline-flex items-center rounded-full border border-primary-300/20 bg-white px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-primary-300">
                Legal
            </div>
            <h1 class="mt-5 text-4xl md:text-6xl font-display font-bold tracking-tight text-primary-300 leading-tight">
                Privacy Policy
            </h1>
            <p class="mt-4 text-neutral-500 text-lg max-w-3xl leading-relaxed">
                WestHub Healthcare is committed to protecting your privacy. This policy explains what information we collect, how we use it, and the choices you have.
            </p>
            <p class="mt-3 text-sm text-neutral-400">
                Effective date: April 17, 2026
            </p>
        </div>
    </section>

    <section class="py-14 md:py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4 md:px-8">
            <div class="space-y-10">
                <article class="rounded-3xl border border-neutral-100 bg-neutral-50/60 p-6 md:p-8">
                    <h2 class="text-2xl md:text-3xl font-display font-bold text-primary-300">Information We Collect</h2>
                    <p class="mt-4 text-neutral-500 leading-relaxed">
                        We may collect information you provide directly to us, including your name, contact details, and message content when you submit an enquiry or communicate with our team.
                    </p>
                </article>

                <article class="rounded-3xl border border-neutral-100 bg-neutral-50/60 p-6 md:p-8">
                    <h2 class="text-2xl md:text-3xl font-display font-bold text-primary-300">How We Use Information</h2>
                    <p class="mt-4 text-neutral-500 leading-relaxed">
                        We use your information to respond to requests, coordinate care-related communication, improve our services, and provide updates you have asked to receive.
                    </p>
                </article>

                <article class="rounded-3xl border border-neutral-100 bg-neutral-50/60 p-6 md:p-8">
                    <h2 class="text-2xl md:text-3xl font-display font-bold text-primary-300">Sharing and Disclosure</h2>
                    <p class="mt-4 text-neutral-500 leading-relaxed">
                        We do not sell personal information. We only share information with trusted service providers or when required by law, and only as necessary for business and care operations.
                    </p>
                </article>

                <article class="rounded-3xl border border-neutral-100 bg-neutral-50/60 p-6 md:p-8">
                    <h2 class="text-2xl md:text-3xl font-display font-bold text-primary-300">Data Protection</h2>
                    <p class="mt-4 text-neutral-500 leading-relaxed">
                        We maintain administrative, technical, and physical safeguards designed to protect personal information from unauthorized access, disclosure, or misuse.
                    </p>
                </article>

                <article class="rounded-3xl border border-neutral-100 bg-neutral-50/60 p-6 md:p-8">
                    <h2 class="text-2xl md:text-3xl font-display font-bold text-primary-300">Your Choices</h2>
                    <p class="mt-4 text-neutral-500 leading-relaxed">
                        You may request access to, correction of, or deletion of your personal information where applicable. You can also opt out of non-essential communications at any time.
                    </p>
                </article>

                <article class="rounded-3xl border border-neutral-100 bg-neutral-50/60 p-6 md:p-8">
                    <h2 class="text-2xl md:text-3xl font-display font-bold text-primary-300">Contact Us</h2>
                    <p class="mt-4 text-neutral-500 leading-relaxed">
                        If you have privacy-related questions, please contact us through our
                        <a href="{{ route('enquiries') }}" class="font-semibold text-primary-100 underline underline-offset-2 hover:text-primary-200 transition-colors">enquiries page</a>.
                    </p>
                </article>
            </div>
        </div>
    </section>

    <x-newsletter />

</x-layouts.app>
