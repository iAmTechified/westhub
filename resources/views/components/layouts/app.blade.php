@props(['seo' => []])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $seo['title'] ?? config('app.name', 'WestHub Healthcare') }}</title>
        <meta name="description" content="{{ $seo['description'] ?? 'Compassionate Home Care & Nursing Services in Illinois.' }}">
        <meta property="og:title" content="{{ $seo['title'] ?? config('app.name', 'WestHub Healthcare') }}">
        <meta property="og:description" content="{{ $seo['description'] ?? '' }}">
        <meta property="og:image" content="{{ $seo['og_image'] ?? asset('/assets/images/Slide Image 1.webp') }}">
        <meta name="twitter:card" content="summary_large_image">

        <!-- GEO / Local SEO Meta Tags -->
        <meta name="geo.region" content="US-IL" />
        <meta name="geo.placename" content="Chicago" />
        <meta name="geo.position" content="41.8781;-87.6298" />
        <meta name="ICBM" content="41.8781, -87.6298" />

        <!-- Google Analytics (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('GA_MEASUREMENT_ID', 'G-XXXXXXXXXX') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ env('GA_MEASUREMENT_ID', 'G-XXXXXXXXXX') }}');
        </script>

        <!-- Schema.org JSON-LD for LocalBusiness -->
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "LocalBusiness",
          "name": "WestHub Healthcare",
          "image": "{{ asset('/assets/images/Slide Image 1.webp') }}",
          "@id": "{{ url('/') }}",
          "url": "{{ url('/') }}",
          "telephone": "+1-800-000-0000",
          "address": {
            "@type": "PostalAddress",
            "streetAddress": "123 Healthcare Ave",
            "addressLocality": "Chicago",
            "addressRegion": "IL",
            "postalCode": "60601",
            "addressCountry": "US"
          },
          "geo": {
            "@type": "GeoCoordinates",
            "latitude": 41.8781,
            "longitude": -87.6298
          },
          "openingHoursSpecification": {
            "@type": "OpeningHoursSpecification",
            "dayOfWeek": [
              "Monday",
              "Tuesday",
              "Wednesday",
              "Thursday",
              "Friday",
              "Saturday",
              "Sunday"
            ],
            "opens": "00:00",
            "closes": "23:59"
          }
        }
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link href="https://fonts.googleapis.com/css2?family=Funnel+Display:wght@300..800&display=swap" rel="stylesheet">
        <link href="https://assets.calendly.com/assets/external/widget.css" rel="stylesheet">

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        @stack('styles')
    </head>
    <body class="font-sans antialiased text-neutral-500 bg-neutral-50">
        <div class="min-h-screen">
            <!-- Header (Future Component) -->
            <x-navigation />

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            <!-- Footer (Future Component) -->
            <x-footer />
        </div>

        @livewireScripts
        <script src="https://assets.calendly.com/assets/external/widget.js" async></script>
        <div x-data="{ openAppointmentModal: false }" 
             @open-appointment.window="openAppointmentModal = true"
             @close-appointment.window="openAppointmentModal = false">
            <div x-show="openAppointmentModal" x-cloak x-transition.opacity class="fixed inset-0 z-[100] bg-neutral-900/60 backdrop-blur-sm"></div>
            <div x-show="openAppointmentModal" x-cloak 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6 pointer-events-none">
                <div @click.away="openAppointmentModal = false" class="w-full max-w-5xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl shadow-2xl pointer-events-auto">
                     <livewire:book-appointment />
                </div>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
