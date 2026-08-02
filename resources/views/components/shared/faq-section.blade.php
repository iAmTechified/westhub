@php
    $countyNames = \App\Support\LocationData::countyNamesForSentence();

    $faqs = [
        [
            'question' => 'How do I refer a patient to WestHub Healthcare for services?',
            'answer' => 'Referring a patient is a seamless process. You can reach our Referral Supervisors directly at 872-249-9191, fax clinical documentation to 872-268-8877, or send a secure email. For your convenience, we provide specialized Provider and Non-Provider referral forms on our website. Our team typically reviews and processes all requests within 24 business hours.'
        ],
        [
            'question' => 'What specific regions does WestHub Healthcare serve?',
            'answer' => "We serve a wide range of counties including {$countyNames}. Our network of caregivers is strategically located to ensure timely response and local expertise."
        ],
        [
            'question' => 'Who is eligible for WestHub’s in-home care services?',
            'answer' => 'Eligibility typically depends on the patient’s medical needs and insurance coverage. We provide services to individuals requiring skilled nursing, therapeutic support, or non-medical home care. Contact our clinical coordinators for a free assessment.'
        ],
        [
            'question' => 'Is WestHub Healthcare a licensed and insured agency?',
            'answer' => 'Yes, WestHub Healthcare is fully licensed by the State, CHAP accredited, and comprehensively insured to provide both skilled and non-skilled home healthcare services.'
        ],
        [
            'question' => 'How does WestHub select its caregiving team?',
            'answer' => 'Our selection process is rigorous, including comprehensive background checks, clinical competency testing, and multiple rounds of behavioral interviews to ensure our staff aligns with our compassion-first mission.'
        ]
    ];
@endphp

<section class="py-24 bg-white">
    <div class="max-w-5xl mx-auto px-4 md:px-8">
        <div class="text-left mb-16">
            <h2 class="font-display text-4xl font-bold text-primary-300 mb-6 uppercase tracking-widest">General FAQ</h2>
            <div class="w-16 h-1.5 bg-primary-100 rounded-full"></div>
        </div>

        <div class="space-y-6" x-data="{ openAccordionItem: null }">
            @foreach($faqs as $index => $faq)
                <x-sub.accordion-item 
                    :index="str_pad($index + 1, 2, '0', STR_PAD_LEFT)"
                    :question="$faq['question']"
                    :answer="$faq['answer']"
                    :exclusive="true"
                    :item-key="'shared-faq-' . ($index + 1)"
                />
            @endforeach
        </div>
    </div>
</section>
