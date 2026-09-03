<section class="py-6 bg-neutral-100 border-b border-neutral-200/60">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
            
            <!-- Contact Info -->
            <div class="w-full flex flex-wrap items-center justify-start md:justify-center lg:justify-start gap-5 md:gap-5">
                <!-- Phone -->
                <a href="tel:{{ \App\Support\SiteSettings::contactPhoneTel() }}" class="flex items-center gap-2 group">
                    <div class="p-2 bg-primary-100 rounded-md flex items-center justify-center text-white flex-shrink-0 group-hover:bg-primary-100/80 transition-all duration-300">
                        <span class="inline-flex items-center justify-center w-[20px] h-[20px]">
                        <img src="{{ asset('assets/icons/Property 1=Phone.svg') }}" alt="" class="w-full brightness-0 invert">
                    </span>
                    </div>
                    <span class="text-neutral-600 font-semibold text-sm md:text-xl">{{ \App\Support\SiteSettings::contactPhone() }}</span>
                </a>

                <!-- Email -->
                <a href="{{ \App\Support\SiteSettings::contactMailto() }}" class="flex items-center gap-2 group">
                    <div class="p-2 bg-primary-100 rounded-md flex items-center justify-center text-white flex-shrink-0 group-hover:bg-primary-100/80 transition-all duration-300">
                        <span class="inline-flex items-center justify-center w-[20px] h-[20px]">
                        <img src="{{ asset('assets/icons/Property 1=Email Us.svg') }}" alt="" class="w-full brightness-0 invert">
                    </span>
                    </div>
                    <span class="text-neutral-600 font-semibold text-sm md:text-xl break-all sm:break-normal">{{ \App\Support\SiteSettings::contactEmail() }}</span>
                </a>
            </div>

            <!-- Social Capsule -->
            <div class="bg-neutral-200 rounded-2xl px-3 md:px-5 py-3 flex items-center gap-2 md:gap-4">
                <!-- X (Twitter) -->
                <a href="#" class="text-primary-300 hover:text-primary-200 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.393 6.231H2.746l7.73-8.835L1.254 2.25H8.08l4.259 5.63zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                </a>
                <!-- LinkedIn -->
                <a href="#" class="text-primary-300 hover:text-primary-200 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                    </svg>
                </a>
                <!-- Instagram -->
                <a href="#" class="text-primary-300 hover:text-primary-200 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
                <span class="text-neutral-600 font-semibold text-md md:text-xl whitespace-nowrap">WestHub Healthcare</span>
            </div>

        </div>
    </div>
</section>
