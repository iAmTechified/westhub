@props([
    'services' => null,
    'showIndex' => true,
    'showDivider' => true,
    'sectionClass' => 'py-12 bg-white',
    'containerClass' => 'max-w-7xl mx-auto px-4 md:px-8 space-y-4',
    'itemAppearance' => [],
])

@php
    if ($services === null) {
        $defaultServiceTitles = [
            'Post-Surgical Recovery Support',
            'Chronic Condition Management',
            'Professional Medication Administration',
            'IV Therapy, Injections, & Secure Medication Planning',
            'In-Home Chemotherapy Administration',
            'Routine Vital Signs Monitoring',
            'Advanced Wound Care & Wound Vac Management',
            'Tracheostomy Care & Maintenance',
            'Ventilator & Respiratory Support',
            'Gastrostomy (G-tube) Feeding Support',
            'Nasogastric (N-G) Tube Management',
            'Catheter & Ostomy Care Services',
            'Specialized Pediatric Nursing',
            'In-Home Laboratory Diagnostic Services',
            'Remote Home Health Telehealth Services',
        ];

        $services = array_map(
            fn (string $title): array => [
                'title' => $title,
                'answer' => 'WestHub Healthcare provides comprehensive '.strtolower($title).' tailored to each patient\'s unique needs. Our highly trained clinical staff ensures the highest standards of safety, comfort, and professional excellence in the home setting.',
            ],
            $defaultServiceTitles
        );
    }
@endphp

<div class="{{ $sectionClass }}">
    <div class="{{ $containerClass }}">
        @foreach($services as $index => $service)
            @php
                $itemIndex = $service['index'] ?? str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                $itemKey = (string) ($service['key'] ?? $service['id'] ?? $service['title'] ?? $itemIndex);
                $itemShowIndex = $service['show_index'] ?? $service['showIndex'] ?? $showIndex;
                $itemShowDivider = $service['show_divider'] ?? $service['showDivider'] ?? $showDivider;
                $appearance = array_merge(
                    is_array($itemAppearance) ? $itemAppearance : [],
                    is_array($service['appearance'] ?? null) ? $service['appearance'] : []
                );
            @endphp

            <x-sub.accordion-item
                :index="$itemIndex"
                :item-key="$itemKey"
                :question="$service['title']"
                :answer="$service['answer'] ?? null"
                :content="$service['content'] ?? null"
                :appearance="$appearance"
                :show-index="$itemShowIndex"
                :show-divider="$itemShowDivider"
            />
        @endforeach
    </div>
</div>
