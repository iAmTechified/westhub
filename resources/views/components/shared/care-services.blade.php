@php
    $servicesDir = public_path('assets/images/services');
    if (!file_exists($servicesDir)) {
        @mkdir($servicesDir, 0777, true);
    }
    if (!file_exists($servicesDir . '/home-care.jpg')) {
        @copy('c:\\Users\\USER\\Desktop\\Westhub\\UI images\\Home care.jpg', $servicesDir . '/home-care.jpg');
    }
    if (!file_exists($servicesDir . '/cna.jpg')) {
        @copy('c:\\Users\\USER\\Desktop\\Westhub\\UI images\\CNA.jpg', $servicesDir . '/cna.jpg');
    }
    if (!file_exists($servicesDir . '/therapy.jpg')) {
        @copy('c:\\Users\\USER\\Desktop\\Westhub\\UI images\\therapy.jpg', $servicesDir . '/therapy.jpg');
    }

    $services = [
        [
            'number' => '01',
            'title' => 'Home Care',
            'subtitle' => 'Non-Nursing Service',
            'back_title' => 'Home Care (Non-Medical)',
            'description' => 'Quality is our priority. Our supervisors stay connected with every family through personal phone calls and face-to-face visits to ensure all needs are being met.',
            'icon' => asset('assets/icons/Property 1=Home.svg'),
            'image' => asset('assets/images/services/home-care.webp'),
            'features' => [
                'Hourly Homemaker services',
                'Home Health Aide Services',
                'Post-Hospital Care Services',
                'Live-in Homemaker services',
                'Companionship and Sitter Services'
            ],
            'link'=> '/care-services/home-care',
        ],
        [
            'number' => '02',
            'title' => 'CNA, LPN & RN',
            'subtitle' => 'Nursing Service',
            'back_title' => 'CNA, LPN & RN',
            'description' => 'Here, our standard is providing the best Certified Nursing Assistants (CNA), Licensed Practical Nurses (LPN), and Registered Nurses.',
            'icon' => asset('assets/icons/Property 1=Medical.svg'),
            'image' => asset('assets/images/services/cna.webp'),
            'features' => [
                'Certified Nursing Assistants (CNA)',
                'Licensed Practical Nurses (LPN)',
                'Registered Nurses (RN)',
                'Vital signs Monitoring',
                'Trach Care',
                'Companionship and Sitter Services'
            ],
            'link'=> '/care-services/nursing-care',
        ],
        [
            'number' => '03',
            'title' => 'Therapy Services',
            'subtitle' => '',
            'back_title' => 'Therapy Services',
            'description' => "Experience nursing care that feels like home. We provide compassionate, one-on-one support tailored to your journey; whether you're recovering from surgery or managing your long-term health.",
            'icon' => asset('assets/icons/Property 1=Therapy.svg'),
            'image' => asset('assets/images/services/therapy.webp'),
            'features' => [
                'Physical Therapy',
                'Speech Therapy',
                'Occupational Therapy',
                'Medical Social Worker Services'
            ],
            'link'=> '/care-services/therapeutic-services',
        ]
    ];
@endphp

<section class="py-24 bg-primary-50 overflow-hidden">
    <div class="max-w-[1200px] mx-auto px-5 md:px-8">
        <!-- Section Header -->
        <div class="mb-16 md:mb-20">
            <h2 class="font-display text-[32px] leading-[1.08] md:text-[48px] lg:text-[48px] md:leading-[1.05] font-semibold text-primary-200 mb-2">Care Services</h2>
            <p class="text-[16px] md:text-[18px] lg:text-[18px] leading-normal text-primary-200/60 font-regular">Comprehensive Care Solutions tailored to Your Needs</p>
        </div>

        <!-- Services Grid -->
        <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-3 gap-6 md:gap-7 lg:gap-8">
            @foreach($services as $service)
                <x-sub.service-flip-card 
                    :number="$service['number']"
                    :title="$service['title']"
                    :subtitle="$service['subtitle']"
                    :back-title="$service['back_title'] ?? $service['title']"
                    :description="$service['description']"
                    :icon="$service['icon']"
                    :image="$service['image']"
                    :features="$service['features']"
                    :link="$service['link']"
                />
            @endforeach
        </div>
    </div>
</section>

<style>
    .backface-hidden {
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
    }

    .service-card-icon {
        filter: brightness(0) saturate(100%) invert(59%) sepia(88%) saturate(1675%) hue-rotate(157deg) brightness(94%) contrast(88%);
    }
</style>
