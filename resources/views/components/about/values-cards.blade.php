<section class="bg-white py-24">
    <div class="mx-auto max-w-[1160px] px-6 md:px-8">
        <h2 class="mb-16 text-left font-display text-[44px] font-bold leading-[1.1] text-primary-300 md:text-[56px]">Our Core Values</h2>
        
        <div class="mb-16 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 place-items-center justify-items-center">
            {{-- Compassion First --}}
            <div class="flex min-h-[380px] md:min-h-[420px] flex-col items-center rounded-[34px] border border-[#E5E7EB] bg-white px-8 md:px-10 pb-10 md:pb-12 pt-8 md:pt-10 text-center">
                <div class="mb-8 flex h-[84px] w-[84px] items-center justify-center rounded-full bg-[#E6F6FF]">
                    <img src="{{ asset('assets/icons/Property 1=Home.svg') }}" alt="Compassion" class="h-9 w-9" style="filter: brightness(0) saturate(100%) invert(56%) sepia(91%) saturate(1833%) hue-rotate(159deg) brightness(98%) contrast(94%);">
                </div>
                <h3 class="mb-6 font-display text-2xl font-bold leading-tight text-[#111111]">Compassion First</h3>
                <p class="max-w-[300px] text-lg leading-relaxed text-[#2F2F2F]">
                    We approach every patient with empathy and kindness, treating them like a member of our own family to ensure they feel seen, heard, and valued.
                </p>
            </div>

            {{-- Unwavering Integrity --}}
            <div class="flex min-h-[380px] md:min-h-[420px] flex-col items-center rounded-[34px] border border-[#E5E7EB] bg-white px-8 md:px-10 pb-10 md:pb-12 pt-8 md:pt-10 text-center">
                <div class="mb-8 flex h-[84px] w-[84px] items-center justify-center rounded-full bg-[#E6F6FF]">
                    <img src="{{ asset('assets/icons/Property 1=Adult Home Care.svg') }}" alt="Integrity" class="h-9 w-9" style="filter: brightness(0) saturate(100%) invert(56%) sepia(91%) saturate(1833%) hue-rotate(159deg) brightness(98%) contrast(94%);">
                </div>
                <h3 class="mb-6 font-display text-2xl font-bold leading-tight text-[#111111]">Unwavering Integrity</h3>
                <p class="max-w-[300px] text-lg leading-relaxed text-[#2F2F2F]">
                    We hold ourselves to the highest ethical standards, ensuring transparency, reliability, and clinical excellence in every interaction and treatment plan.
                </p>
            </div>

            {{-- Empowered Independence --}}
            <div class="flex min-h-[380px] md:min-h-[420px] flex-col justify-self-center items-center rounded-[34px] border border-[#E5E7EB] bg-white px-8 md:px-10 pb-10 md:pb-12 pt-8 md:pt-10 text-center">
                <div class="mb-8 flex h-[84px] w-[84px] items-center justify-center rounded-full bg-[#E6F6FF]">
                    <img src="{{ asset('assets/icons/Property 1=Therapy.svg') }}" alt="Independence" class="h-9 w-9" style="filter: brightness(0) saturate(100%) invert(56%) sepia(91%) saturate(1833%) hue-rotate(159deg) brightness(98%) contrast(94%);">
                </div>
                <h3 class="mb-6 font-display text-2xl font-bold leading-tight text-[#111111]">Empowered<br>Independence</h3>
                <p class="max-w-[300px] text-lg leading-relaxed text-[#2F2F2F]">
                    We focus on supporting our patients' autonomy, providing the necessary care and tools that allow them to maintain their lifestyle and dignity at home.
                </p>
            </div>
        </div>

        {{-- Button aligned to left with WestHub Logo --}}
        <div class="flex justify-center lg:justify-start">
            <a href="#" class="group inline-flex items-center justify-center gap-3 rounded-full bg-primary-100 px-8 py-[14px] font-display text-xl font-semibold leading-none text-[#D7F4FF] transition-all hover:brightness-105">
                <svg viewBox="0 0 59 30" class="h-auto w-6 fill-current">
                    <path d="M21.012 18.2084L27.1921 24.1392C27.755 24.6794 27.7931 25.5723 27.27 26.1506C27.2012 26.2267 27.1304 26.3029 27.0588 26.3772C25.0719 28.4475 22.4152 29.4899 19.7557 29.4899C17.2349 29.4899 14.7114 28.5544 12.7489 26.6709L3.11261 17.4234C-0.919222 13.5529 -1.05156 7.14623 2.81892 3.1135C6.6894 -0.920146 13.0961 -1.05158 17.1288 2.8189L18.2338 3.87943C18.6788 4.30637 18.8003 4.96535 18.5483 5.52824C16.6656 9.72776 17.5059 14.8428 21.012 18.2084Z" />
                    <path d="M54.9761 17.4234L52.0692 20.2134C51.1518 21.0936 49.6381 20.4518 49.6163 19.181C49.6163 19.1665 49.6163 19.1529 49.6163 19.1384C49.5547 16.1453 48.331 13.3553 46.171 11.2823L40.2465 5.59713C39.6519 5.02698 39.6519 4.07613 40.2465 3.50508L40.9617 2.8189C44.9944 -1.05158 51.4011 -0.920146 55.2716 3.1135C59.1421 7.14623 59.0097 13.5529 54.9771 17.4234H54.9761Z" />
                    <path d="M38.4074 29.49C35.8866 29.49 33.3631 28.5545 31.4006 26.671L21.7643 17.4226C17.7316 13.5521 17.5992 7.14541 21.4697 3.11268C25.3402 -0.920059 31.7469 -1.0524 35.7796 2.81808L45.4159 12.0665C49.4487 15.9369 49.581 22.3436 45.7105 26.3764C43.7236 28.4467 41.0669 29.4891 38.4074 29.4886V29.49Z" />
                </svg>
                Join Us Today
            </a>
        </div>
    </div>
</section>
