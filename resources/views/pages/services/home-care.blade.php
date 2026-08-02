@php
    $homeCareServices = [
        [
            'title' => 'Home Health Aide Services',
            'content' => [
                [
                    'type' => 'pills',
                    'items' => [
                        'Personal Care Assistance',
                        'Assisting with Bathing /Shower',
                        'Light House Keeping',
                        'Laundry/Ironing',
                        'Diet Planning & Meal Preparation',
                        'Companionship & Recreation',
                        'Grocery, Shopping & Errands',
                        'Medication Reminder',
                        'Transportation/ Accompanying to Doctor\'s Appointments',
                    ],
                ],
            ],
        ],
        [
            'title' => 'Personalized Health Aide Services',
            'content' => [
                [
                    'type' => 'pills',
                    'items' => [
                        'Personal Care Assistance',
                        'Assisting with Bathing /Shower',
                        'Toileting, Commode & Bed pan Handling',
                        'Transferring & Positioning',
                        'Incontinence Care',
                        'Medical equipment cleaning',
                        'Fall protection standby',
                        'Light House Keeping',
                        'Laundry/Ironing',
                        'Diet Planning & Meal Preparation',
                        'Companionship & Recreation',
                        'Grocery, Shopping & Errands',
                        'Medication Reminder',
                        'Transportation/ Accompanying to Doctor\'s Appointments',
                        'Respite Care',
                    ],
                ],
            ],
        ],
        [
            'title' => 'Recovery & Wellness Support',
            'content' => [
                [
                    'type' => 'pills',
                    'items' => [
                        'Companionship',
                        'Assisting with Bathing /Bed Bath',
                        'Hoyer Lift Assistance',
                        'Light Massage',
                        'Assistance with Walking/Wheelchair',
                        'Transportation/ Accompanying to Doctor\'s Appointments',
                        'Respite Care',
                        'Medical equipment cleaning',
                        'Gait belt Transfers',
                        'Well-being Observation',
                        'Fall protection standby',
                        'Light House Keeping',
                        'Medication Reminder',
                        'Diet Planning & Meal Preparation',
                    ],
                ],
            ],
        ],
        [
            'title' => 'Full-Time In-Home Companionship',
            'content' => [
                [
                    'type' => 'pills',
                    'items' => [
                        'Personal Care Assistance',
                        'Assisting with Bathing /Shower',
                        'Light House Keeping',
                        'Laundry/Ironing',
                        'Diet Planning & Meal Preparation',
                        'Companionship & Recreation',
                        'Grocery, Shopping & Errands',
                        'Medication Reminder',
                        'Transportation/ Accompanying to Doctor\'s Appointments',
                    ],
                ],
            ],
        ],
        [
            'title' => 'Heartfelt Connection & Care Sitting',
            'content' => [
                [
                    'type' => 'pills',
                    'items' => [
                        'Personal Care Assistance',
                        'Companionship & Recreation',
                        'Assisting with Bathing /Shower',
                        'Light House Keeping',
                        'Laundry/Ironing',
                        'Diet Planning & Meal Preparation',
                        'Grocery, Shopping & Errands',
                        'Medication Reminder',
                        'Assistance with Walking/Wheelchair',
                        'Fall protection standby',
                        'Transportation/ Accompanying to Doctor\'s Appointments',
                    ],
                ],
            ],
        ],
    ];

@endphp

<x-layouts.app :seo="$seo">
    
    {{-- Hero Section --}}
    <x-services.hero 
        title="Home Care Services"
        subtitle="Non-skilled Professional"
    />

    {{-- Accordion Section --}}
    <x-services.accordion
        :services="$homeCareServices"
        :show-index="false"
    />
    
    <div class="w-full h-px bg-neutral-200/50"></div>
    
    <x-care-services.commitment />

    <x-shared.care-services />
    
    <x-shared.healthcare-brands />
    
    <x-shared.locations />
    
    <x-shared.testimonials />

    <x-newsletter />

</x-layouts.app>
