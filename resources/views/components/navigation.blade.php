@php
    $locations = \App\Support\LocationData::countyLinks();
    $locationsHref = $locations !== []
        ? route('locations.county', ['county' => $locations[0]['slug']])
        : route('locations.county', ['county' => 'cook-county']);
@endphp

<nav x-data="{ 
    mobileMenuOpen: false, 
    careDropdown: false, 
    locationDropdown: false,
    scrolled: false
}" 
     x-init="scrolled = window.scrollY > 8"
     @scroll.window="scrolled = window.scrollY > 8"
     class="sticky top-0 z-50 border-b transition-all duration-300"
     :class="scrolled 
        ? 'bg-white/75 backdrop-blur-xl border-white/45 shadow-lg shadow-primary-300/10' 
        : 'bg-white backdrop-blur-xl border-neutral-100/50'">
    <div class="max-w-7xl mx-auto px-4 md:px-8">
        <div class="flex justify-between items-center h-20">
            
            <!-- Logo: Blobs + Wordmark -->
            <a href="/" class="flex gap-3 items-center">
                <img src="/assets/logo.svg" alt="WestHub Mark" class="h-6">
                <img src="/assets/icons/Logo wordmark.svg" alt="WestHub Healthcare" class="h-9">
            </a>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center gap-5">
                <a href="/" class="{{ request()->routeIs('home') ? 'bg-primary-300 text-white px-4 py-1.5 rounded-full shadow-lg shadow-primary-300/30' : 'text-neutral-500' }} text-sm font-bold hover:text-primary-100 transition-all duration-300 tracking-tight">Home</a>
                <a href="/about" class="{{ request()->is('about*') ? 'bg-primary-300 text-white px-4 py-1.5 rounded-full shadow-lg shadow-primary-300/30' : 'text-neutral-500' }} text-sm font-bold hover:text-primary-100 transition-all duration-300 tracking-tight whitespace-nowrap">About Us</a>
                
                <!-- Care Services Dropdown -->
                <div @mouseenter="careDropdown = true" @mouseleave="careDropdown = false" class="relative">
                    <a href="/services" class="flex items-center gap-1 font-bold {{ request()->is('services*') ? 'bg-primary-300 text-white px-4 py-1.5 rounded-full shadow-lg shadow-primary-300/30' : 'text-neutral-500 hover:text-primary-100' }} text-sm transition-all duration-300 tracking-tight py-1.5 group whitespace-nowrap">
                        Care Services
                        <x-icon-chevron-down class="w-[8px] h-[8px] {{ request()->is('services*') ? 'text-white' : 'text-primary-300 opacity-80' }} text-xs group-hover:opacity-100 transition-all" />
                    </a>
                    <div x-show="careDropdown" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-cloak
                         class="absolute left-0 w-60 pt-2 z-50">
                        <div class="bg-white/65 backdrop-blur-xl rounded-xl shadow-[0_20px_40px_-20px_rgba(11,77,130,0.45)] border border-white/60 ring-1 ring-primary-100/20 py-2 overflow-hidden">
                            <a href="/services/home-care" class="block px-5 py-2.5 text-sm text-neutral-500 hover:text-primary-100 transition-all font-medium">Home Care</a>
                            <a href="/services/nursing-care" class="block px-5 py-2.5 text-sm text-neutral-500 hover:text-primary-100 transition-all font-medium">Nursing Care</a>
                            <a href="/services/therapeutic-services" class="block px-5 py-2.5 text-sm text-neutral-500 hover:text-primary-100 transition-all font-medium">Therapeutic Services</a>
                        </div>
                    </div>
                </div>

                <!-- Locations Dropdown -->
                <div @mouseenter="locationDropdown = true" @mouseleave="locationDropdown = false" class="relative">
                    <a href="{{ $locationsHref }}" class="flex items-center gap-1 font-bold {{ request()->is('locations*') ? 'bg-primary-300 text-white px-4 py-1.5 rounded-full shadow-lg shadow-primary-300/30' : 'text-neutral-500 hover:text-primary-100' }} text-sm transition-all duration-300 tracking-tight py-1.5 group whitespace-nowrap">
                        Locations
                        <x-icon-chevron-down class="w-[8px] h-[8px] {{ request()->is('locations*') ? 'text-white' : 'text-primary-300 opacity-80' }} group-hover:opacity-100 transition-all" />
                    </a>
                    <div x-show="locationDropdown" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-cloak
                         class="absolute left-0 w-60 pt-2 z-50">
                        <div class="bg-white/65 backdrop-blur-xl rounded-xl shadow-[0_20px_40px_-20px_rgba(11,77,130,0.45)] border border-white/60 ring-1 ring-primary-100/20 py-2 overflow-hidden">
                            @foreach($locations as $location)
                                <a href="{{ route('locations.county', ['county' => $location['slug']]) }}" class="block px-5 py-2 text-sm text-neutral-500 hover:text-primary-100 transition-all font-medium">{{ $location['name'] }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <a href="/enquiries" class="{{ request()->is('enquiries*') ? 'bg-primary-300 text-white px-4 py-1.5 rounded-full shadow-lg shadow-primary-300/30' : 'text-neutral-500' }} text-sm font-bold hover:text-primary-100 transition-all duration-300 tracking-tight whitespace-nowrap">Enquiries</a>
                <a href="/articles" class="{{ request()->is('articles*') ? 'bg-primary-300 text-white px-4 py-1.5 rounded-full shadow-lg shadow-primary-300/30' : 'text-neutral-500' }} text-sm font-bold hover:text-primary-100 transition-all duration-300 tracking-tight whitespace-nowrap">Articles</a>
            </div>

            <!-- Actions -->
            <div class="hidden lg:flex items-center gap-3">
                <x-button-primary type="button" onclick="window.dispatchEvent(new CustomEvent('open-appointment'))" class="flex items-center gap-2 !py-3 !px-6 text-sm active:translate-y-0.5 transition-all">
                    <x-icon-calendar class="w-5 h-5"/>
                    Book Appointment
                </x-button-primary>
                <x-button-secondary href="tel:{{ \App\Support\SiteSettings::contactPhoneTel() }}" variant="outline" class="flex items-center gap-2 !py-3 !px-6 text-sm border-primary-100 text-primary-300 active:translate-y-0.5 transition-all">
                    <x-icon-phone class="w-5 h-5 text-primary-100"/>
                    {{ \App\Support\SiteSettings::contactPhone() }}
                </x-button-secondary>
            </div>

            <!-- Mobile Toggle -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 text-primary-300">
                <svg x-show="!mobileMenuOpen" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileMenuOpen" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu Backdrop -->
    <div x-show="mobileMenuOpen" 
         class="fixed inset-0 z-30 bg-primary-300/30 backdrop-blur-sm lg:hidden"
         @click="mobileMenuOpen = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak></div>

    <!-- Mobile Menu Drawer -->
    <div x-show="mobileMenuOpen" 
         @click.away="mobileMenuOpen = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         x-cloak
         class="lg:hidden bg-white/95 backdrop-blur-2xl border-b border-white/60 shadow-2xl overflow-hidden absolute w-full top-full left-0 z-40 max-h-[calc(100vh-5rem)] overflow-y-auto">
        <div class="px-5 py-5 md:px-6 md:py-6 space-y-2 bg-transparent">
            @php
                $mobileNavBase = 'inline-flex w-full items-center rounded-full px-4 py-2.5 text-sm font-bold tracking-tight transition-all duration-300';
                $mobileNavActive = 'bg-primary-300 text-white shadow-lg shadow-primary-300/30';
                $mobileNavInactive = 'text-neutral-500 hover:text-primary-100';
                $mobileSubBase = 'block w-full rounded-full px-4 py-2 text-sm font-medium transition-all duration-300';
                $mobileSubActive = 'text-primary-300 hover:text-primary-100';
                // $mobileSubActive = 'bg-primary-300 text-white shadow-md shadow-primary-300/25';
                $mobileSubInactive = 'text-neutral-500 hover:text-primary-100';
            @endphp

            <a href="/"
               class="{{ $mobileNavBase }} {{ request()->routeIs('home') ? $mobileNavActive : $mobileNavInactive }}">
                Home
            </a>

            <a href="/about"
               class="{{ $mobileNavBase }} {{ request()->is('about*') ? $mobileNavActive : $mobileNavInactive }}">
                About Us
            </a>

            <div x-data="{ open: {{ request()->is('services*') ? 'true' : 'false' }} }" class="space-y-1.5 p-2" :class="open ? ' rounded-2xl border border-white/25 bg-white/15 backdrop-blur-lg' : ''">
                <div class="flex items-center justify-between w-full gap-2 {{ $mobileNavBase }} {{ request()->is('services*') ? $mobileNavActive : $mobileNavInactive }}">
                    <a href="/services"
                       class="">
                        Care Services
                    </a>
                    <button @click="open = !open"
                            class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full transition-colors duration-300 {{ request()->is('services*') ? 'text-white' : 'text-neutral-500 hover:text-primary-100' }}">
                        <x-icon-chevron-down class="w-4 h-4 transition-transform duration-300" ::class="open ? 'rotate-180' : ''" />
                    </button>
                </div>
                <div x-show="open" x-cloak class="ml-3 border-l border-neutral-50 pl-3 space-y-1.5">
                    <a href="/services/home-care" class="{{ $mobileSubBase }} {{ request()->is('services/home-care') ? $mobileSubActive : $mobileSubInactive }}">Home Care</a>
                    <a href="/services/nursing-care" class="{{ $mobileSubBase }} {{ request()->is('services/nursing-care') ? $mobileSubActive : $mobileSubInactive }}">Nursing Care</a>
                    <a href="/services/therapeutic-services" class="{{ $mobileSubBase }} {{ request()->is('services/therapeutic-services') ? $mobileSubActive : $mobileSubInactive }}">Therapeutic Services</a>
                </div>
            </div>

            <div x-data="{ open: {{ request()->is('locations*') ? 'true' : 'false' }} }" class="space-y-1.5 p-2" :class="open ? ' rounded-2xl border border-white/25 bg-white/15 backdrop-blur-lg' : ''">
                <div class="flex items-center justify-between w-full gap-2 {{ $mobileNavBase }} {{ request()->is('locations*') ? $mobileNavActive : $mobileNavInactive }}">
                    <a href="{{ $locationsHref }}"
                       class="">
                        Locations
                    </a>
                    <button @click="open = !open"
                            class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full transition-colors duration-300 {{ request()->is('locations*') ? 'text-white' : 'text-neutral-500 hover:text-primary-100' }}">
                        <x-icon-chevron-down class="w-4 h-4 transition-transform duration-300" ::class="open ? 'rotate-180' : ''" />
                    </button>
                </div>
                <div x-show="open" x-cloak class="ml-3 border-l border-neutral-50 pl-3 space-y-1.5">
                    @foreach($locations as $location)
                        <a href="{{ route('locations.county', ['county' => $location['slug']]) }}" class="{{ $mobileSubBase }} {{ request()->is('locations/'.$location['slug']) ? $mobileSubActive : $mobileSubInactive }}">{{ $location['name'] }}</a>
                    @endforeach
                </div>
            </div>

            <a href="/enquiries"
               class="{{ $mobileNavBase }} {{ request()->is('enquiries*') ? $mobileNavActive : $mobileNavInactive }}">
                Enquiries
            </a>

            <a href="/articles"
               class="{{ $mobileNavBase }} {{ request()->is('articles*') ? $mobileNavActive : $mobileNavInactive }}">
                Articles
            </a>

            <div class="pt-3 space-y-3">
                <x-button-primary type="button" onclick="window.dispatchEvent(new CustomEvent('open-appointment'))" class="w-full !py-3 flex items-center justify-center gap-2 text-sm active:translate-y-0.5 transition-all">
                    <x-icon-calendar class="w-5 h-5"/>
                    Book Appointment
                </x-button-primary>
                <x-button-secondary href="tel:{{ \App\Support\SiteSettings::contactPhoneTel() }}" variant="outline" class="w-full !py-3 flex items-center justify-center gap-2 text-sm border-primary-100 text-primary-300 hover:bg-primary-100/5 active:translate-y-0.5 transition-all">
                    <x-icon-phone class="w-5 h-5 text-primary-100"/>
                    {{ \App\Support\SiteSettings::contactPhone() }}
                </x-button-secondary>
            </div>
        </div>
    </div>
</nav>
