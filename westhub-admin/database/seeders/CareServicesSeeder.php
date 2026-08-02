<?php

namespace Database\Seeders;

use App\Models\CareServiceGroup;
use App\Models\CareServiceItem;
use App\Models\Service;
use Illuminate\Database\Seeder;

class CareServicesSeeder extends Seeder
{
    public function run(): void
    {
        $services = collect([
            [
                'name' => 'Personal Care',
                'slug' => 'personal-care',
                'tagline' => 'Daily assistance delivered with dignity.',
                'description' => 'Hands-on support for bathing, dressing, hygiene, mobility, and meal routines.',
            ],
            [
                'name' => 'Companionship',
                'slug' => 'companionship',
                'tagline' => 'Reliable social support at home.',
                'description' => 'Conversation, routine support, social engagement, and wellbeing check-ins.',
            ],
            [
                'name' => 'Post-Hospital Support',
                'slug' => 'post-hospital-support',
                'tagline' => 'Structured recovery support after discharge.',
                'description' => 'Recovery-focused care, appointment reminders, and home routine stabilization.',
            ],
            [
                'name' => 'Dementia Care',
                'slug' => 'dementia-care',
                'tagline' => 'Calm and consistent memory care routines.',
                'description' => 'Personalized routines and supervision tailored to cognitive support needs.',
            ],
            [
                'name' => 'Respite Care',
                'slug' => 'respite-care',
                'tagline' => 'Short-term coverage for family caregivers.',
                'description' => 'Flexible relief shifts for families needing trusted temporary care coverage.',
            ],
        ])->mapWithKeys(function (array $service) {
            $model = Service::query()->firstOrCreate(
                ['slug' => $service['slug']],
                [
                    'name' => $service['name'],
                    'tagline' => $service['tagline'],
                    'description' => $service['description'],
                    'status' => 'published',
                    'is_active' => true,
                    'sort_order' => 0,
                    'published_at' => now(),
                ]
            );

            return [$service['slug'] => $model->id];
        });

        $groups = collect([
            [
                'name' => 'In-Home Daily Support',
                'slug' => 'in-home-daily-support',
                'description' => 'Essential daily routines and supervision for independent home living.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Clinical & Recovery Care',
                'slug' => 'clinical-recovery-care',
                'description' => 'Structured post-acute and condition-based support programs.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Family & Relief Services',
                'slug' => 'family-relief-services',
                'description' => 'Flexible care designed to support families and reduce caregiver burnout.',
                'sort_order' => 3,
            ],
        ])->mapWithKeys(function (array $group) {
            $model = CareServiceGroup::query()->firstOrCreate(
                ['slug' => $group['slug']],
                [
                    'name' => $group['name'],
                    'description' => $group['description'],
                    'status' => 'published',
                    'is_active' => true,
                    'sort_order' => $group['sort_order'],
                    'published_at' => now(),
                ]
            );

            return [$group['slug'] => $model->id];
        });

        $items = [
            [
                'group_slug' => 'in-home-daily-support',
                'service_slug' => 'personal-care',
                'title' => 'Bathing and Grooming Assistance',
                'slug' => 'bathing-and-grooming-assistance',
                'subtitle' => 'Comfortable, respectful daily hygiene support',
                'description' => 'Caregivers assist with bathing, grooming, dressing, and personal routines while preserving independence and dignity.',
                'icon' => 'sparkles',
                'sort_order' => 1,
            ],
            [
                'group_slug' => 'in-home-daily-support',
                'service_slug' => 'companionship',
                'title' => 'Companionship Visits',
                'slug' => 'companionship-visits',
                'subtitle' => 'Meaningful social connection at home',
                'description' => 'Friendly visits focused on conversation, engagement, and emotional wellbeing to reduce isolation and promote confidence.',
                'icon' => 'users',
                'sort_order' => 2,
            ],
            [
                'group_slug' => 'clinical-recovery-care',
                'service_slug' => 'post-hospital-support',
                'title' => 'Post-Discharge Recovery Plan',
                'slug' => 'post-discharge-recovery-plan',
                'subtitle' => 'Safe transition from hospital to home',
                'description' => 'Personalized follow-up support after discharge including routine planning, appointment support, and daily recovery check-ins.',
                'icon' => 'clipboard-check',
                'sort_order' => 1,
            ],
            [
                'group_slug' => 'clinical-recovery-care',
                'service_slug' => 'dementia-care',
                'title' => 'Memory Care Supervision',
                'slug' => 'memory-care-supervision',
                'subtitle' => 'Specialized routines for cognitive support',
                'description' => 'Consistency-based care plans to support memory, reduce stress triggers, and improve daily comfort for clients and families.',
                'icon' => 'brain',
                'sort_order' => 2,
            ],
            [
                'group_slug' => 'family-relief-services',
                'service_slug' => 'respite-care',
                'title' => 'Weekend Respite Coverage',
                'slug' => 'weekend-respite-coverage',
                'subtitle' => 'Planned relief for primary caregivers',
                'description' => 'Flexible short-term coverage giving family caregivers time to rest while maintaining continuity of care at home.',
                'icon' => 'calendar-heart',
                'sort_order' => 1,
            ],
            [
                'group_slug' => 'family-relief-services',
                'service_slug' => 'personal-care',
                'title' => 'Overnight Safety Monitoring',
                'slug' => 'overnight-safety-monitoring',
                'subtitle' => 'Night-time reassurance and supervision',
                'description' => 'Overnight support for mobility, medication reminders, and safety checks to provide peace of mind for households.',
                'icon' => 'moon-stars',
                'sort_order' => 2,
            ],
        ];

        foreach ($items as $item) {
            CareServiceItem::query()->firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'care_service_group_id' => $groups[$item['group_slug']],
                    'service_id' => $services[$item['service_slug']] ?? null,
                    'title' => $item['title'],
                    'subtitle' => $item['subtitle'],
                    'description' => $item['description'],
                    'icon' => $item['icon'],
                    'status' => 'published',
                    'is_active' => true,
                    'sort_order' => $item['sort_order'],
                    'published_at' => now(),
                ]
            );
        }
    }
}
