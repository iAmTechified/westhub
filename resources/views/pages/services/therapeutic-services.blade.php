@php
    $therapeuticServices = [
        [
            'title' => 'Physical Therapy',
            'answer' => 'We offer physical therapy, which involves exercise and manual therapy to help clients regain strength, movement, and function in their bodies right in the comfort of their homes.',
        ],
        [
            'title' => 'Speech-Language Pathology',
            'answer' => 'Our speech therapy services help clients improve their speech, language, and swallowing abilities. We also offer cognitive therapy to help clients with memory, attention, and problem-solving skills.',
        ],
        [
            'title' => 'Occupational Therapy',
            'answer' => 'Our occupational therapy focuses on helping clients improve their ability to perform daily activities, such as dressing, cooking, and cleaning, and to become more independent.',
        ],
        [
            'title' => 'Medical Social Work',
            'answer' => 'Our medical social worker services assist in connecting patients and families with community resources, providing emotional support and counseling, and assisting in care planning.',
        ],
    ];
@endphp

<x-layouts.app :seo="$seo">

        {{-- Hero Section --}}
    <x-services.hero 
        title="Therapeutic Services"
        subtitle="Physical/Occupational Therapy"
    />

    <!-- Accordion Section -->
    <x-services.accordion
        :services="$therapeuticServices"
        :show-index="false"
        :show-divider="false"
        section-class="py-20 bg-white"
        container-class="max-w-[1088px] mx-auto px-4 md:px-8"
    />

    <!-- Healing Journey Text Section -->
    <section class="py-16 bg-white border-t border-neutral-200">
        <div class="max-w-5xl mx-auto px-4 text-center">
            <div class="space-y-8 text-[#555555] text-base md:text-lg leading-relaxed font-medium max-w-4xl mx-auto">
                <p>
                    WestHub Healthcare is committed to your healing journey. We offer a diverse range of supportive therapies designed to restore your wellbeing and help you thrive after surgery, illness, or injury. We believe that the best place to heal is at home, and our services are built to make that possible.
                </p>
                <p>
                    Our expert therapists work hand-in-hand with you and your family to develop a care strategy that honors your unique goals. Utilizing modern technology and a heart-centered approach, we deliver high-quality therapeutic care right to your doorstep. At WestHub, our mission is simple: providing the expert care you need to enjoy a better, healthier life.
                </p>
            </div>
        </div>
    </section>

    <!-- Care Services Section -->
    <x-shared.care-services />

    <!-- Healthcare Brands Section -->
    <x-shared.healthcare-brands />

    <!-- Service Locations Section -->
    <x-shared.locations />

    <!-- Testimonials Section -->
    <x-shared.testimonials />

    <x-newsletter />


</x-layouts.app>
