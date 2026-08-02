<section class="relative overflow-hidden bg-gradient-brand">
    <div class="mx-auto max-w-7xl px-4 xs:px-6 md:px-8 xl:px-10">
        <div class="relative flex flex-col md:flex-row items-center
        py-8 xs:py-10 md:py-12 lg:py-10
        max-h-[600px] md:max-h-[400px] lg:max-h-[500px]">
            {{-- Text --}}
            <div class="relative z-20 w-full lg:max-w-[35%] text-center md:text-left">
                <h1 class="font-display font-medium max-w-md text-white leading-[1.05] text-[clamp(2rem,5vw,3rem)]">
                    Home Healthcare Service you can Trust
                </h1>

                <div class="mx-6 md:mx-auto mt-7 flex flex-wrap items-center lg:items-start justify-center lg:justify-start gap-3 xs:gap-4">
                    <button type="button"
                       onclick="window.dispatchEvent(new CustomEvent('open-appointment'))"
                       class="inline-flex flex-grow items-center justify-center gap-3 h-[54px] px-6 rounded-full bg-[#EAF6FF] border border-[#CFE7F6] text-[#075EA6] text-[17px] font-semibold leading-none whitespace-nowrap shadow-[inset_0_1px_0_rgba(255,255,255,0.85)] transition-colors hover:bg-[#F4FAFF]">
                        <span class="inline-flex items-center justify-center w-8 h-8">
                        <img src="{{ asset('assets/icons/Property 1=Calendar.svg') }}" alt="" class="w-[18px] h-[18px]" style="filter: invert(19%) sepia(21%) saturate(5429%) hue-rotate(200deg) brightness(97%) contrast(93%);">
                    </span>
                        Book Appointment
                    </button>

                    <a href="{{ \App\Support\SiteSettings::contactMailto() }}"
                       class="inline-flex flex-grow items-center justify-center gap-3 h-[54px] px-6 rounded-full bg-[linear-gradient(180deg,rgba(171,217,239,0.72)_0%,rgba(138,197,228,0.62)_100%)] border border-[#BFDCF0] text-[#EAF6FF] text-[17px] font-medium leading-none whitespace-nowrap shadow-[inset_0_1px_0_rgba(255,255,255,0.35)] transition-colors hover:bg-[linear-gradient(180deg,rgba(175,221,243,0.78)_0%,rgba(144,203,233,0.68)_100%)]">
                        <span class="inline-flex items-center justify-center w-8 h-8">
                        <img src="{{ asset('assets/icons/Property 1=Email Us.svg') }}" alt="" class="w-[18px] h-[18px] brightness-0 invert">
                    </span>
                        Email Us
                    </a>
                </div>
            </div>

            {{-- Character --}}
            <div class="flex-grow pointer-events-none z-10 w-full md:w-[56%]">
                <img
                    src="{{ asset('assets/images/lady thumbs up.webp') }}"
                    alt="Professional caregiver giving thumbs up"
                    class="scale-110 md:scale-100 md:w-full md:h-auto lg:w-auto md:max-w-none object-contain md:translate-y-12 lg:translate-y-[200px] xl:translate-y-[200px]"
                />
            </div>

            {{-- Brand Mark --}}
            <div class="absolute bottom-4 right-4 sm:bottom-6 sm:right-6 lg:bottom-8 lg:right-8 text-white/90 w-10 sm:w-12 lg:w-14 pointer-events-none select-none z-20">
                <svg viewBox="0 0 59 30" class="fill-current w-full h-auto">
                    <path d="M21.012 18.2084L27.1921 24.1392C27.755 24.6794 27.7931 25.5723 27.27 26.1506C27.2012 26.2267 27.1304 26.3029 27.0588 26.3772C25.0719 28.4475 22.4152 29.4899 19.7557 29.4899C17.2349 29.4899 14.7114 28.5544 12.7489 26.6709L3.11261 17.4234C-0.919222 13.5529 -1.05156 7.14623 2.81892 3.1135C6.6894 -0.920146 13.0961 -1.05158 17.1288 2.8189L18.2338 3.87943C18.6788 4.30637 18.8003 4.96535 18.5483 5.52824C16.6656 9.72776 17.5059 14.8428 21.012 18.2084Z" />
                    <path d="M54.9761 17.4234L52.0692 20.2134C51.1518 21.0936 49.6381 20.4518 49.6163 19.181C49.6163 19.1665 49.6163 19.1529 49.6163 19.1384C49.5547 16.1453 48.331 13.3553 46.171 11.2823L40.2465 5.59713C39.6519 5.02698 39.6519 4.07613 40.2465 3.50508L40.9617 2.8189C44.9944 -1.05158 51.4011 -0.920146 55.2716 3.1135C59.1421 7.14623 59.0097 13.5529 54.9771 17.4234H54.9761Z" />
                    <path d="M38.4074 29.49C35.8866 29.49 33.3631 28.5545 31.4006 26.671L21.7643 17.4226C17.7316 13.5521 17.5992 7.14541 21.4697 3.11268C25.3402 -0.920059 31.7469 -1.0524 35.7796 2.81808L45.4159 12.0665C49.4487 15.9369 49.581 22.3436 45.7105 26.3764C43.7236 28.4467 41.0669 29.4891 38.4074 29.4891V29.49Z" />
                </svg>
            </div>
        </div>
    </div>
</section>
